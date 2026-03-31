<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Auth;
use PDO;

class DashboardController extends Controller {
    public function index(): void {
        if (Auth::isTutor()) {
            $this->redirect('/app/tutor/dashboard');
            return;
        }
        $user_name = $_SESSION['user_name'] ?? 'Usuário';
        $total_users = 0;
        $total_logs = 0;
        $admin_stats = [];

        try {
            if (Auth::isAdmin()) {
                // --- ADMIN GLOBAL STATS ---
                $admin_stats['total_companies'] = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_companies WHERE trashed_at IS NULL")['total'] ?? 0;
                $admin_stats['active_companies'] = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_companies WHERE active = 1 AND trashed_at IS NULL")['total'] ?? 0;
                
                // Financials (Global)
                $admin_stats['total_revenue'] = \App\Core\Database::fetch("SELECT SUM(amount) as total FROM cp_invoices WHERE status = 'paid'")['total'] ?? 0;
                $admin_stats['pending_revenue'] = \App\Core\Database::fetch("SELECT SUM(amount) as total FROM cp_invoices WHERE status = 'pending'")['total'] ?? 0;
                
                // System wide users and logs
                $total_users = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_users")['total'] ?? 0;
                $total_logs = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_logs")['total'] ?? 0;

                // Recent companies
                $admin_stats['recent_companies'] = \App\Core\Database::fetchAll("SELECT * FROM cp_companies WHERE trashed_at IS NULL ORDER BY id DESC LIMIT 5");
                
                // Stats for the generic row
                $summary_stats = [
                    ['label' => 'Empresas Ativas', 'value' => $admin_stats['active_companies'], 'icon' => 'building', 'color' => 'orange', 'link' => '/admin/companies'],
                    ['label' => 'Faturado Total', 'value' => 'R$ ' . number_format((float)$admin_stats['total_revenue'], 2, ',', '.'), 'icon' => 'dollar-sign', 'color' => 'green', 'link' => '/admin/subscriptions'],
                    ['label' => 'Usuários Transantes', 'value' => $total_users, 'icon' => 'users', 'color' => 'blue', 'link' => '/admin/users'],
                    ['label' => 'Logs de Atividade', 'value' => $total_logs, 'icon' => 'activity', 'color' => 'pink', 'link' => '/admin/logs'],
                ];
            } else {
                // --- COMPANY SPECIFIC STATS ---
                $cid = Auth::companyId();
                $total_users = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_users WHERE company_id = :cid", ['cid' => $cid])['total'] ?? 0;
                $total_logs = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_logs WHERE company_id = :cid", ['cid' => $cid])['total'] ?? 0;

                $vet_stats = [];
                $vet_stats['total_pets'] = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_pets WHERE company_id = :cid", ['cid' => $cid])['total'] ?? 0;
                $vet_stats['total_tutores'] = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_tutores WHERE company_id = :cid", ['cid' => $cid])['total'] ?? 0;
                $vet_stats['today_consultas'] = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_consultas WHERE company_id = :cid AND DATE(data_consulta) = CURDATE()", ['cid' => $cid])['total'] ?? 0;
                
                $vet_stats['monthly_appointments'] = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_consultas WHERE company_id = :cid AND MONTH(data_consulta) = MONTH(CURDATE()) AND YEAR(data_consulta) = YEAR(CURDATE())", ['cid' => $cid])['total'] ?? 0;
                
                // Company details
                $vet_stats['company'] = \App\Core\Database::fetch("SELECT * FROM cp_companies WHERE id = :cid", ['cid' => $cid]) ?: [];

                // Detailed data
                $vet_stats['species_distribution'] = \App\Core\Database::fetchAll("SELECT especie, COUNT(*) as total FROM cp_pets WHERE company_id = :cid GROUP BY especie ORDER BY total DESC", ['cid' => $cid]);
                $vet_stats['recent_appointments'] = \App\Core\Database::fetchAll("SELECT c.*, p.nome as pet_nome FROM cp_consultas c JOIN cp_pets p ON c.pet_id = p.id WHERE c.company_id = :cid ORDER BY c.data_consulta DESC LIMIT 5", ['cid' => $cid]);

                // Monthly Revenue
                $monthly_revenue = \App\Core\Database::fetch("SELECT SUM(valor) as total FROM cp_financeiro WHERE company_id = :cid AND tipo = 'entrada' AND MONTH(data_movimentacao) = MONTH(CURDATE()) AND YEAR(data_movimentacao) = YEAR(CURDATE())", ['cid' => $cid])['total'] ?? 0;

                // Stats for the generic row
                $summary_stats = [
                    ['label' => 'Pets Sob Cuidado', 'value' => $vet_stats['total_pets'], 'icon' => 'dog', 'color' => 'orange', 'link' => '/app/pets'],
                    ['label' => 'Consultas Hoje', 'value' => $vet_stats['today_consultas'], 'icon' => 'calendar-check', 'color' => 'green', 'link' => '/app/consultas'],
                    ['label' => 'Tutores Cadastrados', 'value' => $vet_stats['total_tutores'], 'icon' => 'users', 'color' => 'blue', 'link' => '/app/tutores'],
                    ['label' => 'Faturamento Mensal', 'value' => 'R$ ' . number_format((float)$monthly_revenue, 2, ',', '.'), 'icon' => 'dollar-sign', 'color' => 'pink', 'link' => '/app/financeiro'],
                ];
            }
        } catch (\Exception $e) {
            $summary_stats = [];
        }

        $this->render('app/dashboard', [
            'user_name' => $user_name,
            'summary_stats' => $summary_stats,
            'admin_stats' => $admin_stats ?? [],
            'vet_stats' => $vet_stats ?? []
        ]);
    }
}
