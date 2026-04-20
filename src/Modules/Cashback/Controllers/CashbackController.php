<?php
declare(strict_types=1);

namespace App\Modules\Cashback\Controllers;

use App\Core\Controller;
use App\Core\Database;
use Auth;

class CashbackController extends Controller {

    public function tutorIndex(): void {
        Auth::requireRole('tutor');
        $company_id = Auth::companyId();
        $tutor_id   = Auth::tutorId();

        // Get tutor balance, history and credit limit
        $tutor = Database::fetch("SELECT nome, cashback_balance, credit_limit FROM cp_tutores WHERE id = :id", ['id' => $tutor_id]);
        
        // If the balance doesn't exist (column was just added), default to 0
        $balance = $tutor['cashback_balance'] ?? 0.00;
        $creditLimit = $tutor['credit_limit'] ?? 0.00;

        $stats = Database::fetch("
            SELECT 
                SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_earned,
                SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_used
            FROM cp_cashback_logs 
            WHERE tutor_id = :tid AND company_id = :cid
        ", ['tid' => $tutor_id, 'cid' => $company_id]);

        $pendingLoans = Database::fetch("
            SELECT SUM(amount) as total FROM cp_cashback_loans 
            WHERE tutor_id = :tid AND company_id = :cid AND status = 'pending'
        ", ['tid' => $tutor_id, 'cid' => $company_id])['total'] ?? 0;

        $totalEarned = $stats['total_earned'] ?? 0.00;

        $chart_data = Database::fetchAll("
            SELECT 
                DATE_FORMAT(created_at, '%b') as month,
                SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as earned,
                SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as used
            FROM cp_cashback_logs 
            WHERE tutor_id = :tid AND company_id = :cid
            GROUP BY month
            ORDER BY MIN(created_at) ASC
            LIMIT 6
        ", ['tid' => $tutor_id, 'cid' => $company_id]);

        $history = Database::fetchAll("
            (SELECT 'cashback' as source, amount, type, description, 'completed' as item_status, created_at 
             FROM cp_cashback_logs 
             WHERE tutor_id = :tid1 AND company_id = :cid1)
            UNION ALL
            (SELECT 'loan' as source, amount, 'debit' as type, 'Solicitação de Empréstimo' as description, status as item_status, created_at 
             FROM cp_cashback_loans 
             WHERE tutor_id = :tid2 AND company_id = :cid2)
            ORDER BY created_at DESC 
            LIMIT 10
        ", [
            'tid1' => $tutor_id, 'cid1' => $company_id,
            'tid2' => $tutor_id, 'cid2' => $company_id
        ]);

        // Fetch withdrawals
        $withdrawals = Database::fetchAll("
            SELECT * FROM cp_cashback_withdrawals 
            WHERE tutor_id = :tid AND company_id = :cid 
            ORDER BY created_at DESC
        ", ['tid' => $tutor_id, 'cid' => $company_id]);

        // Fetch pix info from tutor
        $tutor_full = Database::fetch("SELECT pix_type, pix_key FROM cp_tutores WHERE id = :id", ['id' => $tutor_id]);

        $this->render('Modules/Cashback/Views/tutor_index', [
            'title'   => 'Meu Cashback',
            'tutor'   => $tutor,
            'balance' => $balance,
            'totalEarned' => $totalEarned,
            'creditLimit' => $creditLimit,
            'pendingLoans' => $pendingLoans,
            'stats'   => $stats,
            'chart_data' => $chart_data,
            'history' => $history,
            'withdrawals' => $withdrawals,
            'pix' => $tutor_full
        ]);
    }

    /**
     * API: Request Withdrawal
     */
    public function requestWithdrawal(): void {
        Auth::requireRole('tutor');
        $company_id = Auth::companyId();
        $tutor_id   = Auth::tutorId();

        $amount = (float)str_replace(',', '.', $_POST['amount'] ?? '0');
        $pix_type = $_POST['pix_type'] ?? '';
        $pix_key  = $_POST['pix_key'] ?? '';

        if ($amount <= 0 || empty($pix_type) || empty($pix_key)) {
            $this->jsonResponse(['success' => false, 'message' => 'Todos os campos são obrigatórios'], 400);
            return;
        }

        // Check balance
        $tutor = Database::fetch("SELECT cashback_balance, nome FROM cp_tutores WHERE id = :id", ['id' => $tutor_id]);
        $current_balance = (float)($tutor['cashback_balance'] ?? 0);

        if ($amount > $current_balance) {
            $this->jsonResponse(['success' => false, 'message' => 'Saldo insuficiente para este saque'], 400);
            return;
        }

        try {
            // 1. Create Withdrawal Request
            Database::insert('cp_cashback_withdrawals', [
                'company_id' => $company_id,
                'tutor_id'   => $tutor_id,
                'amount'     => $amount,
                'pix_type'   => $pix_type,
                'pix_key'    => $pix_key,
                'status'     => 'pending'
            ]);

            // 2. Debit Tutor Balance
            Database::query("UPDATE cp_tutores SET cashback_balance = cashback_balance - :amt, pix_type = :pt, pix_key = :pk WHERE id = :tid", [
                'amt' => $amount,
                'pt'  => $pix_type,
                'pk'  => $pix_key,
                'tid' => $tutor_id
            ]);

            // 3. Log the Debit
            Database::insert('cp_cashback_logs', [
                'company_id' => $company_id,
                'tutor_id'   => $tutor_id,
                'amount'     => $amount,
                'type'       => 'debit',
                'description' => 'Solicitação de saque via Pix'
            ]);

            \App\Helpers\Logger::log('cashback_withdrawal_request', "Solicitação de saque de R$ " . number_format($amount, 2, ',', '.') . " pelo tutor #$tutor_id");

            // 4. Notify the Company Owner(s)
            $owners = Database::fetchAll("SELECT id FROM cp_users WHERE company_id = :cid AND role = 'proprietario'", ['cid' => $company_id]);
            foreach ($owners as $owner) {
                Database::insert('cp_notifications', [
                    'user_id' => $owner['id'],
                    'title'   => 'Nova Solicitação de Saque',
                    'message' => "O tutor " . $tutor['nome'] . " solicitou um saque de R$ " . number_format($amount, 2, ',', '.') . " via Pix.",
                    'link'    => '/app/admin/cashback/saques',
                    'type'    => 'info'
                ]);
            }

            $this->jsonResponse(['success' => true, 'message' => 'Solicitação enviada com sucesso!']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao processar solicitação: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Admin: List Withdrawal Requests
     */
    public function adminWithdrawals(): void {
        // Force manual check to avoid any middleware redirect loops
        if (!Auth::isLoggedIn()) {
            header("Location: " . SITE_URL . "/login");
            exit;
        }
        
        // Explicitly allow Owners and Admins
        if (!Auth::isProprietario() && !Auth::isAdmin()) {
            header("Location: " . SITE_URL . "/dashboard");
            exit;
        }

        $company_id = Auth::companyId();

        $withdrawals = Database::fetchAll("
            SELECT w.*, t.nome as tutor_nome 
            FROM cp_cashback_withdrawals w
            JOIN cp_tutores t ON t.id = w.tutor_id
            WHERE w.company_id = :cid
            ORDER BY w.created_at DESC
        ", ['cid' => $company_id]);

        $loans = Database::fetchAll("
            SELECT l.*, t.nome as tutor_nome 
            FROM cp_cashback_loans l
            JOIN cp_tutores t ON t.id = l.tutor_id
            WHERE l.company_id = :cid
            ORDER BY l.created_at DESC
        ", ['cid' => $company_id]);

        $this->render('Modules/Cashback/Views/admin_withdrawals', [
            'title' => 'Gestão ClubePet+',
            'withdrawals' => $withdrawals,
            'loans' => $loans
        ]);
    }

    /**
     * API: Tutor requests a loan
     */
    public function requestLoan(): void {
        Auth::requireLogin();
        $tutor_id   = Auth::tutorId();
        $company_id = Auth::companyId();

        $amount       = (float)str_replace(['.', ','], ['', '.'], $_POST['amount'] ?? '0');
        $installments = (int)($_POST['installments'] ?? 1);
        $total_to_pay = (float)str_replace(['.', ','], ['', '.'], $_POST['total_to_pay'] ?? '0');

        if ($amount <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'Valor inválido'], 400);
            return;
        }

        try {
            $tutor = Database::fetch("SELECT nome, credit_limit FROM cp_tutores WHERE id = :id", ['id' => $tutor_id]);
            if ($amount > (float)$tutor['credit_limit']) {
                $this->jsonResponse(['success' => false, 'message' => 'O valor excede seu limite disponível'], 400);
                return;
            }

            Database::insert('cp_cashback_loans', [
                'company_id'   => $company_id,
                'tutor_id'     => $tutor_id,
                'amount'       => $amount,
                'installments' => $installments,
                'total_to_pay' => $total_to_pay,
                'status'       => 'pending'
            ]);

            // Notify Owners
            $owners = Database::fetchAll("SELECT id FROM cp_users WHERE company_id = :cid AND role = 'proprietario'", ['cid' => $company_id]);
            foreach ($owners as $owner) {
                Database::insert('cp_notifications', [
                    'user_id' => $owner['id'],
                    'title'   => 'Nova Solicitação de Empréstimo',
                    'message' => "O tutor " . $tutor['nome'] . " solicitou um crédito de R$ " . number_format($amount, 2, ',', '.') . ".",
                    'link'    => '/app/admin/cashback/saques',
                    'type'    => 'info'
                ]);
            }

            $this->jsonResponse(['success' => true, 'message' => 'Solicitação enviada com sucesso!']);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Erro ao processar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API Admin: Approve or Reject Loan
     */
    public function processLoan(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();
        
        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? ''; // 'approve' or 'reject'

        if (!$id || !in_array($action, ['approve', 'reject'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Dados inválidos'], 400);
            return;
        }

        try {
            $loan = Database::fetch("SELECT * FROM cp_cashback_loans WHERE id = :id AND company_id = :cid", ['id' => $id, 'cid' => $company_id]);
            if (!$loan || $loan['status'] !== 'pending') {
                $this->jsonResponse(['success' => false, 'message' => 'Solicitação não encontrada ou já processada'], 404);
                return;
            }

            if ($action === 'approve') {
                $db = Database::getInstance();
                $db->beginTransaction();

                try {
                    Database::query("UPDATE cp_cashback_loans SET status = 'approved' WHERE id = :id", ['id' => $id]);
                    
                    // ADD TO TUTOR BALANCE AND DEDUCT FROM LIMIT (Using unique placeholders for PDO compatibility)
                    Database::query("UPDATE cp_tutores SET cashback_balance = cashback_balance + :amt1, credit_limit = credit_limit - :amt2 WHERE id = :tid", [
                        'amt1' => $loan['amount'],
                        'amt2' => $loan['amount'],
                        'tid' => $loan['tutor_id']
                    ]);

                    // LOG THE CREDIT
                    Database::insert('cp_cashback_logs', [
                        'company_id'  => $company_id,
                        'tutor_id'    => $loan['tutor_id'],
                        'amount'      => $loan['amount'],
                        'type'        => 'credit',
                        'description' => "Crédito de Empréstimo Aprovado (#$id)"
                    ]);

                    $db->commit();
                } catch (\Exception $e) {
                    $db->rollBack();
                    throw $e;
                }

                // 3. Notify Tutor (Fail-safe)
                try {
                    $user = Database::fetch("SELECT id FROM cp_users WHERE tutor_id = :tid AND company_id = :cid LIMIT 1", [
                        'tid' => $loan['tutor_id'],
                        'cid' => $company_id
                    ]);
                    if ($user) {
                        Database::insert('cp_notifications', [
                            'user_id' => $user['id'],
                            'title'   => 'Crédito Aprovado!',
                            'message' => "Seu empréstimo de R$ " . number_format((float)$loan['amount'], 2, ',', '.') . " foi aprovado e o saldo já está disponível.",
                            'link'    => '/app/tutor/cashback',
                            'type'    => 'success'
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log error if needed, but don't fail the request
                }
            } else {
                Database::query("UPDATE cp_cashback_loans SET status = 'rejected' WHERE id = :id", ['id' => $id]);
                
                // Notify Tutor (Fail-safe)
                try {
                    $user = Database::fetch("SELECT id FROM cp_users WHERE tutor_id = :tid AND company_id = :cid LIMIT 1", [
                        'tid' => $loan['tutor_id'],
                        'cid' => $company_id
                    ]);
                    if ($user) {
                        Database::insert('cp_notifications', [
                            'user_id' => $user['id'],
                            'title'   => 'Solicitação de Crédito',
                            'message' => "Sua solicitação de crédito de R$ " . number_format((float)$loan['amount'], 2, ',', '.') . " não foi aprovada no momento.",
                            'link'    => '/app/tutor/cashback',
                            'type'    => 'warning'
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log error if needed
                }
            }

            $this->jsonResponse(['success' => true, 'message' => 'Solicitação processada com sucesso!']);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false, 
                'message' => 'Erro interno ao processar: ' . $e->getMessage(),
                'debug'   => $e->getFile() . ' L:' . $e->getLine()
            ], 500);
        }
    }

    /**
     * API Admin: Mark Withdrawal as Paid
     */
    public function markAsPaid(): void {
        Auth::requireLogin();
        $company_id = Auth::companyId();
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) {
            $this->jsonResponse(['success' => false, 'message' => 'ID inválido'], 400);
            return;
        }

        $withdraw = Database::fetch("SELECT * FROM cp_cashback_withdrawals WHERE id = :id AND company_id = :cid", ['id' => $id, 'cid' => $company_id]);
        if (!$withdraw) {
            $this->jsonResponse(['success' => false, 'message' => 'Solicitação não encontrada'], 404);
            return;
        }

        Database::query("UPDATE cp_cashback_withdrawals SET status = 'paid', paid_at = NOW() WHERE id = :id", ['id' => $id]);
        
        // Notify Tutor
        try {
            $user = Database::fetch("SELECT id FROM cp_users WHERE tutor_id = :tid AND company_id = :cid LIMIT 1", [
                'tid' => $withdraw['tutor_id'],
                'cid' => $company_id
            ]);
            if ($user) {
                Database::insert('cp_notifications', [
                    'user_id' => $user['id'],
                    'title'   => 'Saque Pago!',
                    'message' => "Seu resgate de R$ " . number_format((float)$withdraw['amount'], 2, ',', '.') . " foi processado e pago.",
                    'link'    => '/app/tutor/cashback',
                    'type'    => 'success'
                ]);
            }
        } catch (\Exception $e) {
            // fail-safe
        }

        \App\Helpers\Logger::log('cashback_withdrawal_paid', "Saque #$id marcado como pago.");
        $this->jsonResponse(['success' => true]);
    }
}
