<?php
namespace App\Controllers;

use App\Core\Controller;

class NotificationController extends Controller {
    public function read($id): void {
        $user_id = (int)$_SESSION['user_id'];
        $id = (int)$id;
        
        require_once __DIR__ . '/../../includes/repositories/NotificationRepository.php';
        $notifRepo = new \NotificationRepository(\App\Core\Database::getInstance());
        
        $success = $notifRepo->markAsRead($id, $user_id);

        if ($success) {
            try {
                require_once __DIR__ . '/../../includes/logs.php';
                \App\Helpers\Logger::log('notification', "Notificação #$id marcada como lida pelo usuário #$user_id");
            } catch (\Exception $e) {}
        }
        
        $this->jsonResponse([
            'success' => $success,
            'message' => $success ? 'Notificação lida' : 'Falha ao marcar como lida ou permissão negada'
        ]);
    }

    public function readAll(): void {
        $user_id = (int)$_SESSION['user_id'];
        require_once __DIR__ . '/../../includes/repositories/NotificationRepository.php';
        $notifRepo = new \NotificationRepository(\App\Core\Database::getInstance());
        
        $notifRepo->markAllAsRead($user_id);
        $this->jsonResponse(['success' => true, 'message' => 'Todas lidas']);
    }
}
