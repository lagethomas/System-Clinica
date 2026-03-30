<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\Logger;
use Auth;
use PDO;
use UserRepository;
use Nonce;

class UsersController extends Controller {
    public function index(): void {
        Auth::requirePermission('users');
        $company_id = Auth::companyId();
        
        require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
        global $pdo;
        
        // Fetch users with company name, filtered by company if not admin
        $sql = "SELECT u.*, c.name as company_name FROM cp_users u LEFT JOIN cp_companies c ON u.company_id = c.id";
        $params = [];
        if ($company_id) {
            $sql .= " WHERE u.company_id = :cid";
            $params['cid'] = $company_id;
        }
        $sql .= " ORDER BY u.name ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch companies for the dropdown (only for global admins)
        $companies = [];
        if (Auth::isAdmin()) {
            $stmtC = $pdo->query("SELECT id, name FROM cp_companies ORDER BY name ASC");
            $companies = $stmtC->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->render('admin/users', [
            'all_users' => $users,
            'companies' => $companies,
            'nonces' => [
                'save' => Nonce::create('save_user'),
                'delete' => Nonce::create('delete_user')
            ]
        ]);
    }

    public function save(): void {
        Auth::requirePermission('users');
        $current_company_id = Auth::companyId();

        if (!Nonce::verify($_POST['nonce'] ?? '', 'save_user')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }

        require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
        $userRepo = new UserRepository(\App\Core\Database::getInstance());

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? null;
        $role = $_POST['role'] ?? 'usuario';
        
        // Logical check: Proprietario can only set roles for their own company employees
        if ($current_company_id) {
            // If editing, check if user belongs to this company
            if ($id) {
                $targetUser = $userRepo->getById($id);
                if (!$targetUser || (int)$targetUser['company_id'] !== $current_company_id) {
                    $this->jsonResponse(['success' => false, 'message' => 'Você não tem permissão para editar este usuário.'], 403);
                    return;
                }
            }
            $company_id = $current_company_id;
            // Prevent proprietario from creating admins or anything outside company context
            if ($role === 'administrador') $role = 'usuario';
        } else {
            $company_id = !empty($_POST['company_id']) ? (int)$_POST['company_id'] : null;
        }

        if (!$name || !$email || (!$id && !$username)) {
            $this->jsonResponse(['success' => false, 'message' => 'Nome, e-mail e username são obrigatórios.'], 400);
            return;
        }

        try {
            // Check if email already exists
            $existingUser = $userRepo->getByEmail($email);
            if ($existingUser && (int)$existingUser['id'] !== $id) {
                $this->jsonResponse(['success' => false, 'message' => 'Este e-mail já está sendo utilizado por outro usuário.'], 400);
                return;
            }

            // Check if username already exists
            if (!$id && $username) {
                $existingByUsername = $userRepo->getByUsername($username);
                if ($existingByUsername) {
                    $this->jsonResponse(['success' => false, 'message' => 'Este nome de usuário já está sendo utilizado.'], 400);
                    return;
                }
            }

            $userData = [
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'password' => $password,
                'role' => $role,
                'phone' => trim($_POST['phone'] ?? ''),
                'zip_code' => trim($_POST['zip_code'] ?? ''),
                'street' => trim($_POST['street'] ?? ''),
                'neighborhood' => trim($_POST['neighborhood'] ?? ''),
                'address_number' => trim($_POST['address_number'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'company_id' => $company_id,
                'created_by' => Auth::id()
            ];

            $userRepo->save($userData);
            
            // Recalculate billing if associated with a company
            if ($company_id) {
                require_once __DIR__ . '/../../../includes/repositories/CompanyRepository.php';
                $compRepo = new \CompanyRepository(\App\Core\Database::getInstance());
                $compRepo->checkAutoBilling($company_id);
            }
            
            require_once __DIR__ . '/../../../includes/logs.php';
            $msg = $id ? "Editou o usuário " . (string)$name : "Criou o usuário " . (string)$name;
            Logger::log($id ? 'edit_user' : 'create_user', $msg);
            
            if ($company_id) {
                \App\Helpers\Notification::forCompany((int)$company_id, 'Gestão de Usuários', $msg, "/users", 'info', (int)Auth::id());
            }
            
            $this->jsonResponse(['success' => true, 'message' => 'Usuário salvo com sucesso!', 'redirect' => 'users']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function delete(): void {
        Auth::requirePermission('users');
        $current_company_id = Auth::companyId();
        
        if (!Nonce::verify($_POST['nonce'] ?? '', 'delete_user')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
            return;
        }

        if ($id === Auth::id()) {
            $this->jsonResponse(['success' => false, 'message' => 'Você não pode excluir sua própria conta.'], 400);
            return;
        }

        try {
            require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
            $userRepo = new UserRepository(\App\Core\Database::getInstance());
            $user = $userRepo->getById($id);

            if (!$user) {
                $this->jsonResponse(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
                return;
            }
            
            // Security: Proprietario can only delete users from their own company
            if ($current_company_id && (int)$user['company_id'] !== $current_company_id) {
                $this->jsonResponse(['success' => false, 'message' => 'Permissão negada.'], 403);
                return;
            }

            // Delete associated image if exists
            if (!empty($user['avatar'])) {
                require_once __DIR__ . '/../../../includes/image_helper.php';
                $uploadDir = __DIR__ . '/../../../uploads/profile/';
                \ImageHelper::safeDelete($user['avatar'], $uploadDir);
            }

            // Cascade delete: if this user is a tutor, delete the tutor entry as well
            if (!empty($user['tutor_id'])) {
                \App\Core\Database::query("DELETE FROM cp_tutores WHERE id = :tid AND company_id = :cid", [
                    'tid' => $user['tutor_id'], 
                    'cid' => $user['company_id']
                ]);
            }

            $userRepo->delete($id);

            // Recalculate billing
            if (!empty($user['company_id'])) {
                require_once __DIR__ . '/../../../includes/repositories/CompanyRepository.php';
                $compRepo = new \CompanyRepository(\App\Core\Database::getInstance());
                $compRepo->checkAutoBilling((int)$user['company_id']);
                
                $msg = "Usuário " . (string)$user['name'] . " removido por administrador.";
                Logger::log('delete_user', $msg);
                \App\Helpers\Notification::forCompany((int)$user['company_id'], 'Usuário Removido', $msg, "/users", 'danger', (int)Auth::id());
            }
            
            $this->jsonResponse(['success' => true, 'message' => 'Usuário removido.']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    public function sendCredentials(): void {
        Auth::requirePermission('users');
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $password = !empty($_POST['password']) ? trim($_POST['password']) : null;

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido.'], 400);
            return;
        }

        // Generate auto-password if not provided (now handled in backend)
        if (!$password) {
            $password = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 8);
        }

        require_once __DIR__ . '/../../../includes/repositories/UserRepository.php';
        $userRepo = new UserRepository(\App\Core\Database::getInstance());
        $user = $userRepo->getById($id);

        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => 'Usuário não encontrado.'], 404);
            return;
        }

        // If a new password was provided in the prompt, update it in the DB
        if ($password) {
            $userRepo->updatePassword($id, $password);
        }

        // Fetch company slug if it's a company user
        $loginUrl = SITE_URL . "/login";
        $companyName = "SaaSFlow Core";

        if (!empty($user['company_id'])) {
            require_once __DIR__ . '/../../../includes/repositories/CompanyRepository.php';
            $compRepo = new \CompanyRepository(\App\Core\Database::getInstance());
            $company = $compRepo->getById((int)$user['company_id']);
            if ($company) {
                $loginUrl = SITE_URL . "/" . $company['slug'] . "/login";
                $companyName = $company['name'];
            }
        }

        try {
            require_once __DIR__ . '/../../../includes/helpers/Mailer.php';
            $subject = "Seus Dados de Acesso - $companyName 🔐";
            
            $passwordRow = $password ? "<p style='margin: 5px 0;'><strong>Senha:</strong> {$password} <span style='font-size:11px; color:#999;'>(atualizada)</span></p>" : "";

            $body = "
                <div style='font-family: sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h2 style='color: #4a6cf7;'>Olá, {$user['name']}!</h2>
                    <p>Sua conta no sistema <strong>$companyName</strong> está pronta para ser utilizada.</p>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p style='margin: 5px 0;'><strong>Usuário:</strong> {$user['username']}</p>
                        $passwordRow
                        <p style='margin: 5px 0;'><strong>Link de Acesso:</strong> <a href='$loginUrl'>$loginUrl</a></p>
                    </div>
                    <p style='margin-bottom: 20px;'>Recomendamos que você altere sua senha em seu primeiro acesso para maior segurança.</p>
                    <p style='font-size: 12px; color: #777; border-top: 1px solid #eee; padding-top: 10px;'>Sistema desenvolvido por SaaSFlow Core</p>
                </div>
            ";

            $sent = \Mailer::send($user['email'], $subject, $body);

            if ($sent) {
                $this->jsonResponse(['success' => true, 'message' => 'Dados de acesso enviados com sucesso para ' . $user['email']]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Falha ao enviar e-mail. Verifique a configuração SMTP.'], 500);
            }
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }
}
