<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Pagination;
use Auth;
use LogRepository;

class LogsController extends Controller {
    public function index(): void {
        Auth::requireAdmin();
        
        global $pdo;
        require_once __DIR__ . '/../../../includes/repositories/LogRepository.php';
        
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';
        $action_filter = $_GET['action'] ?? '';

        $logRepo = new LogRepository($pdo);
        $filters = [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'action' => $action_filter
        ];

        $totalItems = $logRepo->countAll($filters);
        $pagination = Pagination::getParams($totalItems, 25);
        $logs = $logRepo->getAll($filters, $pagination['limit'], $pagination['offset']);

        $this->render('admin/logs', [
            'logs' => $logs,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'action_filter' => $action_filter,
            'pagination' => $pagination
        ]);
    }
}
