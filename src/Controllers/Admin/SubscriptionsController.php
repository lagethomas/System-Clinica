<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Pagination;
use Auth;
use Nonce;
use PDO;

class SubscriptionsController extends Controller {
    
    public function index(): void {
        Auth::requireAdmin();
        global $pdo;
        
        $totalItems = (int)\App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_invoices")['total'];
        $pagination = Pagination::getParams($totalItems, 25);

        // Exibir Faturas / Assinaturas de todas as empresas
        $stmt = $pdo->prepare("
            SELECT i.*, c.name as company_name, c.slug, c.email
            FROM cp_invoices i
            JOIN cp_companies c ON i.company_id = c.id
            ORDER BY i.due_date DESC, i.id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $pagination['limit'], PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->execute();
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch companies for manual invoice select
        $stmt_comp = $pdo->query("SELECT id, name FROM cp_companies WHERE active = 1 ORDER BY name ASC");
        $companies = $stmt_comp->fetchAll(PDO::FETCH_ASSOC);

        $this->render('admin/subscriptions', [
            'invoices' => $invoices,
            'companies' => $companies,
            'pagination' => $pagination,
            'nonces' => [
                'generate' => Nonce::create('generate_manual_invoice')
            ]
        ]);
    }

    public function generateManual(): void {
        Auth::requireAdmin();
        
        if (!Nonce::verify($_POST['nonce'] ?? '', 'generate_manual_invoice')) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro de segurança (Nonce).'], 403);
            return;
        }

        $company_id = !empty($_POST['company_id']) ? (int)$_POST['company_id'] : null;
        $amount = !empty($_POST['amount']) ? (float)str_replace(',', '.', $_POST['amount']) : 0;
        $due_date = $_POST['due_date'] ?? date('Y-m-d');
        $description = $_POST['description'] ?? 'Fatura Avulsa / Ajuste';

        if (!$company_id || $amount <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Empresa e valor são obrigatórios.'], 400);
            return;
        }

        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO cp_invoices (company_id, amount, status, type, due_date, description) 
            VALUES (?, ?, 'pending', 'single', ?, ?)
        ");
        $res = $stmt->execute([$company_id, $amount, $due_date, $description]);

        if ($res) {
            $this->jsonResponse(['success' => true, 'message' => 'Fatura gerada com sucesso!']);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao gerar fatura.'], 500);
        }
    }
}
