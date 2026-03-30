<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use Auth;
use PDO;

class FinancialController extends Controller {
    public function index(): void {
        Auth::requireAdmin();
        global $pdo;

        // 1. Total Paid (All time)
        $total_paid = \App\Core\Database::fetch("SELECT SUM(amount) as total FROM cp_invoices WHERE status = 'paid'")['total'] ?? 0;
        
        // 2. Pending (Expected)
        $total_pending = \App\Core\Database::fetch("SELECT SUM(amount) as total FROM cp_invoices WHERE status = 'pending'")['total'] ?? 0;

        // 3. Monthly Revenue (Last 6 months)
        $monthly_revenue = \App\Core\Database::fetchAll("
            SELECT 
                DATE_FORMAT(paid_at, '%m/%Y') as month,
                SUM(amount) as total
            FROM cp_invoices 
            WHERE status = 'paid' AND paid_at IS NOT NULL
            GROUP BY month
            ORDER BY paid_at DESC
            LIMIT 6
        ");
        // Reverse to show chronological
        $monthly_revenue = array_reverse($monthly_revenue);

        // 4. Top 10 Paying Companies
        $top_companies = \App\Core\Database::fetchAll("
            SELECT 
                c.name,
                SUM(i.amount) as total_paid,
                COUNT(i.id) as invoice_count
            FROM cp_invoices i
            JOIN cp_companies c ON i.company_id = c.id
            WHERE i.status = 'paid'
            GROUP BY i.company_id
            ORDER BY total_paid DESC
            LIMIT 10
        ");

        // 5. Recent Subscriptions activity
        $recent_sales = \App\Core\Database::fetchAll("
            SELECT i.*, c.name as company_name
            FROM cp_invoices i
            JOIN cp_companies c ON i.company_id = c.id
            WHERE i.status = 'paid'
            ORDER BY i.paid_at DESC
            LIMIT 15
        ");

        $this->render('admin/financial', [
            'total_paid' => (float)$total_paid,
            'total_pending' => (float)$total_pending,
            'monthly_revenue' => $monthly_revenue,
            'top_companies' => $top_companies,
            'recent_sales' => $recent_sales
        ]);
    }
}
