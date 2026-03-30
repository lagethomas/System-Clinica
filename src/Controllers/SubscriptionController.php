<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;

class SubscriptionController extends Controller {
    
    public function index(): void {
        Auth::requireRole('proprietario'); 
        
        $company_id = Auth::companyId();
        $active_tab = $_GET['tab'] ?? 'invoices';

        // Fetch Company with Plan
        $company = Database::fetch("
            SELECT c.*, p.name as plan_name, p.base_price, p.included_users, p.extra_user_price
            FROM cp_companies c
            LEFT JOIN cp_plans p ON c.plan_id = p.id
            WHERE c.id = :id
        ", ['id' => $company_id]);

        // Calculate active users to show "Excedentes" info
        $active_users = Database::fetch("SELECT COUNT(*) as total FROM cp_users WHERE company_id = :cid AND role != 'tutor'", ['cid' => $company_id])['total'];
        $extra_users = max(0, $active_users - ($company['included_users'] ?? 0));
        $extra_cost = $extra_users * ($company['extra_user_price'] ?? 0);

        $invoices = [];
        $all_plans = [];

        if ($active_tab === 'invoices') {
            $invoices = Database::fetchAll("SELECT * FROM cp_invoices WHERE company_id = :cid ORDER BY due_date DESC", ['cid' => $company_id]);
        }

        if ($active_tab === 'plans') {
            $all_plans = Database::fetchAll("SELECT * FROM cp_plans ORDER BY base_price ASC");
        }

        $this->render('app/subscriptions', [
            'company' => $company,
            'active_tab' => $active_tab,
            'invoices' => $invoices,
            'all_plans' => $all_plans,
            'active_users' => $active_users,
            'extra_users' => $extra_users,
            'extra_cost' => $extra_cost
        ]);
    }

    public function action(): void {
        Auth::requireRole('proprietario');
        $this->jsonResponse(['success' => false, 'message' => 'Funcionalidade temporariamente desabilitada.'], 400);
    }
}
