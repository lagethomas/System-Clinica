<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class RegisterController extends Controller {

    public function index(): void {
        global $pdo;

        require_once __DIR__ . '/../../includes/repositories/PlanRepository.php';
        $planRepo = new \PlanRepository($pdo);
        $plans = $planRepo->getAll();

        // Render the register page layout (can be a standalone landing view)
        $this->render('register', [
            'plans' => $plans
        ]);
    }

    public function doRegister(): void {
        global $pdo;

        $name = trim($_POST['restaurant_name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $ownerName = trim($_POST['owner_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $plan_id = (int)($_POST['plan_id'] ?? 0);

        if (empty($name) || empty($slug) || empty($email) || empty($password)) {
            $this->jsonResponse(['success' => false, 'message' => 'Todos os campos obrigatórios precisam estar preenchidos.'], 400);
            return;
        }
        
        // Formatar o slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug), '-'));

        require_once __DIR__ . '/../../includes/repositories/CompanyRepository.php';
        $companyRepo = new \CompanyRepository($pdo);

        if ($companyRepo->isSlugTaken($slug)) {
            $this->jsonResponse(['success' => false, 'message' => 'Este link (slug) já está em uso, escolha outro.'], 400);
            return;
        }

        try {
            // Iniciar transação
            $pdo->beginTransaction();

            $companyData = [
                'name' => $name,
                'slug' => $slug,
                'email' => $email,
                'plan_id' => $plan_id,
                'theme' => 'default'
            ];
            
            // create() returns the ID of the new company
            $company_id = $companyRepo->save($companyData); // note: save returns ID if it's an insert
            
            // Retrieve last insert id manually in case save doesn't return it
            if (!$company_id || $company_id === true) {
                 $company_id = $pdo->lastInsertId();
            }

            // Create Owner User
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            $stmtUser = $pdo->prepare("INSERT INTO cp_users (name, email, password, role, company_id) VALUES (?, ?, ?, 'proprietario', ?)");
            $stmtUser->execute([$ownerName, $email, $hashedPass, $company_id]);

            $pdo->commit();

            $this->jsonResponse(['success' => true, 'message' => 'Cadastro realizado com sucesso! Faça login.']);

        } catch (\Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao realizar cadastro: ' . $e->getMessage()], 500);
        }
    }
}
