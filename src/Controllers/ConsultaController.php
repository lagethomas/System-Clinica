<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;

class ConsultaController extends Controller {

    public function index(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        $search = $_GET['search'] ?? '';
        $where = "WHERE c.company_id = :cid";
        $params = ['cid' => $company_id];

        if (!empty($search)) {
            $where .= " AND (p.nome LIKE :s1 OR t.nome LIKE :s2)";
            $params['s1'] = "%$search%";
            $params['s2'] = "%$search%";
        }

        $sql = "SELECT c.*, p.nome as pet_nome, t.nome as tutor_nome 
                FROM cp_consultas c 
                JOIN cp_pets p ON c.pet_id = p.id 
                JOIN cp_tutores t ON p.tutor_id = t.id 
                $where 
                ORDER BY c.data_consulta DESC";

        $consultas = Database::fetchAll($sql, $params);
        $pets = Database::fetchAll("SELECT p.id, p.nome, t.nome as tutor_nome FROM cp_pets p JOIN cp_tutores t ON p.tutor_id = t.id WHERE p.company_id = :cid ORDER BY p.nome ASC", ['cid' => $company_id]);

        $this->render('app/consultas', [
            'title' => 'Agenda de Consultas',
            'consultas' => $consultas,
            'pets' => $pets,
            'search' => $search,
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
            'pet_id' => (int)$_POST['pet_id'],
            'data_consulta' => $_POST['data_consulta'] ?? date('Y-m-d H:i:s'),
            'motivo' => $_POST['motivo'] ?? null,
            'diagnostico' => $_POST['diagnostico'] ?? null,
            'prescricao' => $_POST['prescricao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'valor' => !empty($_POST['valor']) ? (float)str_replace(',', '.', $_POST['valor']) : 0.00,
            'status' => $_POST['status'] ?? 'agendada'
        ];

        if (empty($data['pet_id']) || empty($data['data_consulta'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Pet e Data são obrigatórios'], 400);
            return;
        }

        if ($id) {
            Database::update('cp_consultas', $data, "id = :id AND company_id = :cid", ['id' => $id, 'cid' => $company_id]);
            Logger::log('consulta_update', "Atualizou consulta ID #$id para o pet " . $data['pet_id']);
        } else {
            Database::insert('cp_consultas', $data);
            Logger::log('consulta_create', "Novo agendamento para o pet " . $data['pet_id']);
            
            // Notification for company
            \App\Helpers\Notification::forCompany($company_id, 'Novo Agendamento', "Nova consulta para o pet " . $data['pet_id'], "/app/consultas", 'info', (int)($_SESSION['user_id'] ?? 0));
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
