<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;

class TutorController extends Controller {

    public function index(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        $search = $_GET['search'] ?? '';
        $where = "WHERE company_id = :cid";
        $params = ['cid' => $company_id];

        if (!empty($search)) {
            $where .= " AND (nome LIKE :s1 OR cpf LIKE :s2 OR email LIKE :s3)";
            $params['s1'] = "%$search%";
            $params['s2'] = "%$search%";
            $params['s3'] = "%$search%";
        }

        $tutores = Database::fetchAll("SELECT * FROM cp_tutores $where ORDER BY nome ASC", $params);

        $this->render('app/tutores', [
            'title' => 'Clientes (Tutores)',
            'tutores' => $tutores,
            'search' => $search,
            'nonce_save' => \Nonce::create('tutor_save'),
            'nonce_delete' => \Nonce::create('tutor_delete')
        ]);
    }

    public function save(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        if (!\Nonce::verify($_POST['nonce'] ?? '', 'tutor_save')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        $data = [
            'company_id' => $company_id,
            'nome' => $_POST['nome'] ?? '',
            'cpf' => $_POST['cpf'] ?? null,
            'email' => $_POST['email'] ?? null,
            'telefone' => $_POST['telefone'] ?? null,
            'zip_code' => $_POST['zip_code'] ?? null,
            'street' => $_POST['street'] ?? null,
            'neighborhood' => $_POST['neighborhood'] ?? null,
            'address_number' => $_POST['address_number'] ?? null,
            'city' => $_POST['city'] ?? null,
            'state' => $_POST['state'] ?? null
        ];

        if (empty($data['nome'])) {
            $this->jsonResponse(['success' => false, 'message' => 'O nome do cliente é obrigatório'], 400);
            return;
        }

        if ($id) {
            Database::update('cp_tutores', $data, "id = :id AND company_id = :cid", ['id' => $id, 'cid' => $company_id]);
            $tutor_id = (int)$id;
            Logger::log('tutor_update', "Atualizou tutor ID #$id: " . $data['nome']);
        } else {
            $tutor_id = (int)Database::insert('cp_tutores', $data);
            Logger::log('tutor_create', "Cadastrou novo tutor: " . $data['nome']);
        }

        // ── UPLOAD DE CONTRATO PDF ──
        if (isset($_FILES['contrato']) && $_FILES['contrato']['error'] === 0) {
            $allowed = ['application/pdf'];
            if (in_array($_FILES['contrato']['type'], $allowed)) {
                $ext = pathinfo($_FILES['contrato']['name'], PATHINFO_EXTENSION);
                $filename = 'contrato_' . $tutor_id . '_' . time() . '.' . $ext;
                $upload_dir = '/uploads/contratos/';
                $full_dir = dirname(__DIR__, 2) . '/public' . $upload_dir;
                
                if (!is_dir($full_dir)) {
                    mkdir($full_dir, 0777, true);
                }
                
                if (move_uploaded_file($_FILES['contrato']['tmp_name'], $full_dir . $filename)) {
                    Database::update('cp_tutores', ['contrato_url' => $upload_dir . $filename], "id = :id", ['id' => $tutor_id]);
                }
            }
        }

        // ── SINCRONIZAR USUÁRIO DO TUTOR (LIBERAR ACESSO) ──
        // Para que o tutor consiga logar, ele precisa de um registro em cp_users vinculado
        $username = !empty($data['email']) ? $data['email'] : ($data['cpf'] ? preg_replace('/\D/', '', $data['cpf']) : 'tutor_' . $tutor_id);
        
        // Verifica se já existe usuário vinculado
        $existingUser = Database::fetch("SELECT id FROM cp_users WHERE tutor_id = :tid", ['tid' => $tutor_id]);
        $username = $_POST['username'] ?? '';
        $password_raw = $_POST['password'] ?? '';
        $send_email = ($_POST['send_email'] ?? '0') === '1';

        if (empty($username)) {
            $this->jsonResponse(['success' => false, 'message' => 'Nome de usuário é obrigatório'], 400);
            return;
        }

        // Verifica se username já existe para outro usuário
        $checkUsername = Database::fetch("SELECT id FROM cp_users WHERE username = :u AND id != :myid", [
            'u' => $username,
            'myid' => $existingUser['id'] ?? 0
        ]);
        if ($checkUsername) {
            $this->jsonResponse(['success' => false, 'message' => 'Este nome de usuário já está em uso'], 400);
            return;
        }

        if ($existingUser) {
            $updateData = [
                'name' => $data['nome'],
                'email' => $data['email'] ?? '',
                'username' => $username,
                'phone' => $data['telefone'] ?? ''
            ];
            if (!empty($password_raw)) {
                $updateData['password'] = password_hash($password_raw, PASSWORD_DEFAULT);
            }
            Database::update('cp_users', $updateData, "id = :id", ['id' => $existingUser['id']]);
        } else {
            $password = password_hash($password_raw ?: 'Tutor123', PASSWORD_DEFAULT);
            Database::insert('cp_users', [
                'company_id' => $company_id,
                'tutor_id' => $tutor_id,
                'name' => $data['nome'],
                'email' => $data['email'] ?? '',
                'username' => $username,
                'password' => $password,
                'role' => 'usuario',
                'phone' => $data['telefone'] ?? '',
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Dispara e-mail de boas-vindas se solicitado
        if ($send_email && !empty($data['email'])) {
            $this->sendWelcomeEmail($data['nome'], $data['email'], $username, $password_raw ?: 'A senha informada no cadastro');
        }

        $this->jsonResponse(['success' => true]);
    }

    private function sendWelcomeEmail($nome, $email, $username, $password): void {
        $system_name = $_SESSION['system_name'] ?? 'Clínica Veterinária';
        $site_url = SITE_URL;
        
        $subject = "Bem-vindo ao Portal do Cliente - $system_name";
        
        $body = "
        <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
            <div style='background: #2563eb; padding: 30px; text-align: center; color: #fff;'>
                <h1 style='margin: 0;'>Olá, $nome!</h1>
                <p style='margin: 10px 0 0;'>Seu acesso ao nosso portal está pronto.</p>
            </div>
            <div style='padding: 30px; line-height: 1.6; color: #333;'>
                <p>A partir de agora, você pode acompanhar o histórico de saúde, vacinas e agendamentos dos seus pets de forma online e rápida.</p>
                
                <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #2563eb;'>Seus dados de acesso:</h3>
                    <p style='margin: 5px 0;'><strong>Link do Portal:</strong> <a href='$site_url/login' style='color: #2563eb;'>$site_url/login</a></p>
                    <p style='margin: 5px 0;'><strong>Usuário:</strong> $username</p>
                    <p style='margin: 5px 0;'><strong>Senha:</strong> $password</p>
                </div>

                <p style='font-size: 14px; color: #64748b;'>Por segurança, recomendamos que altere sua senha após o primeiro acesso.</p>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='$site_url/login' style='background: #2563eb; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold;'>ACESSAR MEU PORTAL</a>
                </div>
            </div>
            <div style='background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b;'>
                Este é um e-mail automático enviado pela plataforma $system_name.
            </div>
        </div>
        ";

        \Mailer::send($email, $subject, $body);
    }

    public function delete(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();

        if (!\Nonce::verify($_POST['nonce'] ?? '', 'tutor_delete')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce inválido)'], 403);
            return;
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            // Cascade delete: Remove associated user if exists
            Database::query("DELETE FROM cp_users WHERE tutor_id = :tid AND company_id = :cid", ['tid' => $id, 'cid' => $company_id]);
            
            // Delete the tutor itself
            Database::query("DELETE FROM cp_tutores WHERE id = :id AND company_id = :cid", ['id' => $id, 'cid' => $company_id]);
            
            Logger::log('tutor_delete', "Removeu tutor ID #$id e seu usuário de acesso.");
            $this->jsonResponse(['success' => true]);
        }
    }

    public function perfil($id): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();

        $tutor = Database::fetch("
            SELECT t.*, u.username, u.active as user_active 
            FROM cp_tutores t 
            LEFT JOIN cp_users u ON u.tutor_id = t.id 
            WHERE t.id = :id AND t.company_id = :cid
        ", ['id' => $id, 'cid' => $company_id]);

        if (!$tutor) {
            $this->redirect('/app/tutores');
            return;
        }

        // Search for associated pets
        $pets = Database::fetchAll("SELECT * FROM cp_pets WHERE tutor_id = :tid AND company_id = :cid", ['tid' => $id, 'cid' => $company_id]);

        $this->render('app/tutor_perfil', [
            'title' => 'Perfil do Cliente - ' . $tutor['nome'],
            'tutor' => $tutor,
            'pets' => $pets
        ]);
    }

    public function details(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }

        $tutor = Database::fetch("
            SELECT t.*, u.username, u.active as user_active 
            FROM cp_tutores t 
            LEFT JOIN cp_users u ON u.tutor_id = t.id 
            WHERE t.id = :id AND t.company_id = :cid
        ", ['id' => $id, 'cid' => $company_id]);

        if (!$tutor) {
            $this->jsonResponse(['success' => false, 'message' => 'Cliente não encontrado'], 404);
            return;
        }

        $pets = Database::fetchAll("SELECT * FROM cp_pets WHERE tutor_id = :tid AND company_id = :cid", ['tid' => $id, 'cid' => $company_id]);
        
        $this->jsonResponse([
            'success' => true,
            'tutor' => $tutor,
            'pets' => $pets
        ]);
    }

    public function toggleStatus(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();
        
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? 1; // 1 = active, 0 = inactive

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID não informado'], 400);
            return;
        }

        // Toggles the linked user's status since cp_tutores doesn't have an active flag
        Database::update('cp_users', ['active' => $status], "tutor_id = :tid AND company_id = :cid", [
            'tid' => $id,
            'cid' => $company_id
        ]);

        $status_text = $status == 1 ? 'Ativado' : 'Inativado';
        Logger::log('tutor_status_toggle', "$status_text tutor ID #$id");

        $this->jsonResponse(['success' => true, 'message' => "Cliente $status_text com sucesso"]);
    }

    public function uploadContract(): void {
        Auth::isLoggedIn();
        $company_id = Auth::companyId();
        
        $tutor_id = (int)($_POST['id'] ?? 0);
        if (!$tutor_id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID do cliente não informado'], 400);
            return;
        }

        if (isset($_FILES['contrato']) && $_FILES['contrato']['error'] === 0) {
            $allowed = ['application/pdf'];
            if (!in_array($_FILES['contrato']['type'], $allowed)) {
                $this->jsonResponse(['success' => false, 'message' => 'Apenas arquivos PDF são permitidos'], 400);
                return;
            }

            $ext = pathinfo($_FILES['contrato']['name'], PATHINFO_EXTENSION);
            $filename = 'contrato_' . $tutor_id . '_' . time() . '.' . $ext;
            $upload_dir = '/uploads/contratos/';
            $full_dir = dirname(__DIR__, 2) . '/public' . $upload_dir;
            
            if (!is_dir($full_dir)) mkdir($full_dir, 0777, true);
            
            if (move_uploaded_file($_FILES['contrato']['tmp_name'], $full_dir . $filename)) {
                Database::update('cp_tutores', ['contrato_url' => $upload_dir . $filename], "id = :id AND company_id = :cid", [
                    'id' => $tutor_id,
                    'cid' => $company_id
                ]);
                $this->jsonResponse(['success' => true, 'message' => 'Documento anexado com sucesso']);
                return;
            }
        }

        $this->jsonResponse(['success' => false, 'message' => 'Nenhum arquivo enviado ou erro no upload'], 400);
    }
}
