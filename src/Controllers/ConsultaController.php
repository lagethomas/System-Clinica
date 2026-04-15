<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Pagination;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;
use PDO;

class ConsultaController extends Controller {

    public function index(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        $search = $_GET['search'] ?? '';
        $where = "WHERE c.company_id = :cid";
        $params = ['cid' => $company_id];

        if (!empty($search)) {
            $where .= " AND (t.nome LIKE :s1 OR t.cpf LIKE :s2)";
            $params['s1'] = "%$search%";
            $params['s2'] = "%$search%";
        }

        // Count total for pagination
        $countSql = "SELECT COUNT(*) as total 
                     FROM cp_consultas c 
                     JOIN cp_tutores t ON c.tutor_id = t.id 
                     $where";
        $totalItems = (int)Database::fetch($countSql, $params)['total'];
        $pagination = Pagination::getParams($totalItems, 25);

        $sql = "SELECT c.*, t.nome as tutor_nome, p.nome as pet_nome 
                FROM cp_consultas c 
                JOIN cp_tutores t ON c.tutor_id = t.id 
                LEFT JOIN cp_pets p ON c.pet_id = p.id 
                $where 
                ORDER BY c.data_consulta DESC
                LIMIT :limit OFFSET :offset";

        $stmt = Database::getInstance()->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->execute();
        $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Map columns for compatibility (handle different versions of schema)
        foreach ($consultas as &$c) {
            $c['motivo'] = $c['servico'] ?? $c['motivo'] ?? 'Consulta';
            $c['valor'] = (!empty($c['valor_cobrado']) && (float)$c['valor_cobrado'] > 0) ? $c['valor_cobrado'] : ($c['valor'] ?? 0);
        }

        // Fetch attachments for these consultations
        if (!empty($consultas)) {
            $consultaIds = array_column($consultas, 'id');
            $placeholders = implode(',', array_fill(0, count($consultaIds), '?'));
            $attachments = Database::fetchAll("SELECT * FROM cp_consulta_anexos WHERE consulta_id IN ($placeholders)", $consultaIds);
            
            $groupedAnexos = [];
            foreach ($attachments as $an) {
                $groupedAnexos[$an['consulta_id']][] = $an;
            }

            foreach ($consultas as &$c) {
                $c['anexos'] = $groupedAnexos[$c['id']] ?? [];
            }
        }

        $tutores = Database::fetchAll("SELECT id, nome, cpf FROM cp_tutores WHERE company_id = :cid ORDER BY nome ASC", ['cid' => $company_id]);
        $pets = Database::fetchAll("SELECT id, tutor_id, nome FROM cp_pets WHERE company_id = :cid ORDER BY nome ASC", ['cid' => $company_id]);
        
        // Prepare search string for autocomplete
        foreach ($tutores as &$t) {
            $t['search_string'] = $t['nome'] . ' ' . ($t['cpf'] ? preg_replace('/\D/', '', $t['cpf']) : '');
        }

        $this->render('app/consultas', [
            'title' => 'Agenda de Atendimentos',
            'consultas' => $consultas,
            'tutores' => $tutores,
            'pets' => $pets,
            'search' => $search,
            'pagination' => $pagination,
            'nonce_save' => \Nonce::create('consulta_save'),
            'nonce_delete' => \Nonce::create('consulta_delete')
        ]);
    }

    public function save(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        if (!\Nonce::verify($_POST['nonce'] ?? '', 'consulta_save')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        $data = [
            'company_id' => $company_id,
            'tutor_id' => (int)$_POST['tutor_id'],
            'pet_id' => !empty($_POST['pet_id']) ? (int)$_POST['pet_id'] : null,
            'data_consulta' => $_POST['data_consulta'] ?? date('Y-m-d H:i:s'),
            'motivo' => $_POST['motivo'] ?? 'Consulta',
            'servico' => $_POST['motivo'] ?? 'Consulta',
            'valor' => !empty($_POST['valor']) ? (float)str_replace(',', '.', $_POST['valor']) : 0.00,
            'valor_cobrado' => !empty($_POST['valor']) ? (float)str_replace(',', '.', $_POST['valor']) : 0.00,
            'diagnostico' => $_POST['diagnostico'] ?? null,
            'prescricao' => $_POST['prescricao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'status' => $_POST['status'] ?? 'agendada'
        ];

        if (empty($data['tutor_id']) || empty($data['data_consulta'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Tutor e Data são obrigatórios'], 400);
            return;
        }

        if ($id) {
            Database::update('cp_consultas', $data, "id = :id AND company_id = :cid", ['id' => $id, 'cid' => $company_id]);
            $consulta_id = (int)$id;
            Logger::log('consulta_update', "Atualizou consulta ID #$id para o tutor " . $data['tutor_id']);
        } else {
            $consulta_id = (int)Database::insert('cp_consultas', $data);
            Logger::log('consulta_create', "Novo agendamento para o tutor " . $data['tutor_id']);
            
            // Notification for company
            \App\Helpers\Notification::forCompany($company_id, 'Novo Agendamento', "Nova consulta para o tutor " . $data['tutor_id'], "/app/consultas", 'info', (int)($_SESSION['user_id'] ?? 0));
        }

        // Handle File Uploads (Repeater)
        if (isset($_FILES['anexo_arquivo'])) {
            $names = $_POST['anexo_nome'] ?? [];
            $files = $_FILES['anexo_arquivo'];
            $upload_dir = __DIR__ . '/../../public/uploads/consultas/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            foreach ($files['name'] as $i => $filename) {
                if (isset($files['error'][$i]) && $files['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $newFilename = uniqid('consulta_' . $consulta_id . '_') . '.' . $ext;
                    
                    if (move_uploaded_file($files['tmp_name'][$i], $upload_dir . $newFilename)) {
                        Database::insert('cp_consulta_anexos', [
                            'consulta_id' => $consulta_id,
                            'nome' => !empty($names[$i]) ? $names[$i] : $filename,
                            'arquivo_url' => '/uploads/consultas/' . $newFilename
                        ]);
                    }
                }
            }
        }
        
        // Single file upload fallback (from old form if still used)
        if (isset($_FILES['anexo']) && $_FILES['anexo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../public/uploads/consultas/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext = pathinfo($_FILES['anexo']['name'], PATHINFO_EXTENSION);
            $newFilename = uniqid('anexo_') . '.' . $ext;
            if (move_uploaded_file($_FILES['anexo']['tmp_name'], $upload_dir . $newFilename)) {
                Database::insert('cp_consulta_anexos', [
                    'consulta_id' => $consulta_id,
                    'nome' => $_FILES['anexo']['name'],
                    'arquivo_url' => '/uploads/consultas/' . $newFilename
                ]);
            }
        }

        $this->jsonResponse(['success' => true]);
    }

    public function delete(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        if (!\Nonce::verify($_POST['nonce'] ?? '', 'consulta_delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            Database::query("DELETE FROM cp_consultas WHERE id = :id AND company_id = :cid", ['id' => $id, 'cid' => $company_id]);
            Logger::log('consulta_delete', "Removeu consulta ID #$id");
            $this->jsonResponse(['success' => true]);
        }
    }
}
