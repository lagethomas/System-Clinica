<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Logger;
use Auth;
class CompanySettingsController extends Controller {
    
    public function index(): void {
        Auth::requirePermission('relatorios'); 
        Auth::requireRole('proprietario'); 
        
        $company_id = Auth::companyId();
        if (!$company_id) {
            header("Location: " . SITE_URL . "/dashboard");
            exit;
        }

        $active_tab = $_GET['tab'] ?? 'general';
        $company = Database::fetch("SELECT * FROM cp_companies WHERE id = :id", ['id' => $company_id]);
        
        require_once __DIR__ . '/../../includes/helpers/ThemeHelper.php';
        $themes = \ThemeHelper::getAvailableThemes();

        $this->render('app/company_settings', [
            'company' => $company,
            'active_tab' => $active_tab,
            'themes' => $themes,
            'nonce' => \Nonce::create('save_company_settings')
        ]);
    }

    public function save(): void {
        Auth::requireRole('proprietario');
        $company_id = Auth::companyId();
        
        $company = Database::fetch("SELECT * FROM cp_companies WHERE id = :id", ['id' => $company_id]);
        
        // Merge POST data with existing record to allow partial updates of tabs
        $data = [
            'name'          => isset($_POST['name']) ? trim($_POST['name']) : ($company['name'] ?? ''),
            'email'         => isset($_POST['email']) ? trim($_POST['email']) : ($company['email'] ?? ''),
            'phone'         => isset($_POST['phone']) ? trim($_POST['phone']) : ($company['phone'] ?? ''),
            'document'      => isset($_POST['document']) ? trim($_POST['document']) : ($company['document'] ?? ''),
            'custom_domain' => isset($_POST['custom_domain']) ? trim($_POST['custom_domain']) : ($company['custom_domain'] ?? ''),
            'theme'         => $_POST['theme'] ?? ($company['theme'] ?? 'gold-black'),
            // Mercado Pago Integration
            'mp_public_key'   => isset($_POST['mp_public_key']) ? trim($_POST['mp_public_key']) : ($company['mp_public_key'] ?? ''),
            'mp_access_token' => isset($_POST['mp_access_token']) ? trim($_POST['mp_access_token']) : ($company['mp_access_token'] ?? ''),
            'mp_enabled'      => isset($_POST['mp_enabled']) ? 1 : 0,
            'taxa_entrega'    => isset($_POST['taxa_entrega']) ? (float)str_replace(',', '.', $_POST['taxa_entrega']) : ($company['taxa_entrega'] ?? 0.00),
        ];

        // Inherit theme_color from selected theme primary color
        require_once __DIR__ . '/../../includes/helpers/ThemeHelper.php';
        $themes = \ThemeHelper::getAvailableThemes();
        if (isset($themes[$data['theme']])) {
            $data['theme_color'] = $themes[$data['theme']]['color'];
        }

        if (empty($data['name'])) {
            $this->jsonResponse(['success' => false, 'message' => 'O nome da empresa é obrigatório.'], 400);
            return;
        }

        // Handle Image Upload for Logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['logo']['tmp_name'];
            $filename = 'logo_' . (string)$company_id . '_' . time() . '.png';
            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/companies/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (move_uploaded_file($tmpPath, $uploadDir . $filename)) {
                $data['logo'] = '/uploads/companies/' . $filename;
            }
        }

        // Handle Image Upload for Background
        if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['background_image']['tmp_name'];
            $filename = 'bg_' . (string)$company_id . '_' . time() . '.jpg';
            $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/companies/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            if (move_uploaded_file($tmpPath, $uploadDir . $filename)) {
                $data['background_image'] = '/uploads/companies/' . $filename;
            }
        }

        Database::update('cp_companies', $data, 'id = :id', ['id' => $company_id]);
        
        $user_id = (int)$_SESSION['user_id'];
        $msg = "Configurações da empresa atualizadas.";
        Logger::log('company_settings_updated', $msg);
        \App\Helpers\Notification::forCompany((int)$company_id, 'Configurações Alteradas', $msg, "/app/company-settings", 'info', $user_id);

        $this->jsonResponse(['success' => true]);
    }
}
