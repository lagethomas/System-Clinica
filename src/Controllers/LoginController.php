<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use Auth;
use App\Helpers\Logger;

class LoginController extends Controller {

    /**
     * Show login form (GET /login)
     */
    public function index(): void {
        global $pdo, $platform_settings;

        if (Auth::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/dashboard');
            exit;
        }

        $this->renderLogin('', $platform_settings ?? []);
    }

    /**
     * Show company-specific login form (GET /{slug}/login)
     */
    public function companyLogin(string $slug): void {
        global $pdo, $platform_settings;

        if (Auth::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/dashboard');
            exit;
        }

        require_once __DIR__ . '/../Core/Database.php';
        $company = \App\Core\Database::fetch("SELECT * FROM cp_companies WHERE slug = :slug AND active = 1 AND trashed_at IS NULL", ['slug' => $slug]);

        if (!$company) {
            header('Location: ' . SITE_URL . '/login');
            exit;
        }

        $this->renderLogin('', $platform_settings ?? [], false, '', '', $company);
    }

    /**
     * Handle login form submission (POST /login)
     */
    public function attempt(): void {
        global $pdo, $platform_settings;

        if (Auth::isLoggedIn()) {
            header('Location: ' . SITE_URL . '/dashboard');
            exit;
        }

        $error        = '';
        $warn_session = false; // flag for "active session" warning
        $username     = trim($_POST['username'] ?? '');
        $password     = $_POST['password'] ?? '';
        $force        = isset($_POST['force_login']); // user chose to force-logout old session

        if (!$username || !$password) {
            $error = 'Preencha todos os campos.';
        } else {
            $stmt = $pdo->prepare('
                SELECT u.*, c.slug as company_slug 
                FROM cp_users u 
                LEFT JOIN cp_companies c ON u.company_id = c.id 
                WHERE u.username = ?
            ');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                
                if (($user['active'] ?? 1) == 0) {
                    $error = 'Sua conta está inativa. Por favor, entre em contato com a administração.';
                } else {
                    $posted_company_id = $_POST['company_id'] ?? '';

                if (empty($posted_company_id) && strtolower($user['role']) !== 'administrador') {
                    $error = 'Acesso restrito a administradores. Por favor, utilize o link específico da sua empresa para logar.';
                } elseif (!empty($posted_company_id) && $user['company_id'] != $posted_company_id) {
                    $error = 'Estas credenciais não pertencem a esta empresa.';
                } else {
                    // ── SINGLE SESSION CHECK ───────────────────────────────
                    $single_session = ($platform_settings['security_single_session'] ?? '0') === '1';
                    if ($single_session && !$force && Auth::hasActiveSession((int)$user['id'])) {
                        // Block login — show warning with a "force" button
                        $warn_session = true;
                        $company_obj = !empty($posted_company_id) ? $this->getCompanyById((int)$posted_company_id) : null;
                        $this->renderLogin($error, $platform_settings ?? [], $warn_session, $username, $password, $company_obj);
                        return;
                    }

                    // If force = true, clear the old session from DB before logging in
                    if ($single_session && $force) {
                        Auth::clearSessionFromDB((int)$user['id']);
                    }
                    // ── END SINGLE SESSION CHECK ───────────────────────────

                    Auth::login($user);

                    try {
                        require_once __DIR__ . '/../../includes/logs.php';
                        Logger::log('login', 'Login realizado via rota MVC.');
                    } catch (\Exception $e) {}

                    header('Location: ' . SITE_URL . '/dashboard');
                    exit;
                }
            }

            } else {
                $error = 'Credenciais inválidas.';
            }
        }

        $this->renderLogin($error, $platform_settings ?? [], false, $username, $password, $_POST['company_id'] ? $this->getCompanyById((int)$_POST['company_id']) : null);
    }

    private function getCompanyById(int $id): ?array {
        require_once __DIR__ . '/../Core/Database.php';
        return \App\Core\Database::fetch("SELECT * FROM cp_companies WHERE id = :id", ['id' => $id]);
    }

    /**
     * Logout (GET /logout)
     */
    public function logout(): void {
        try {
            require_once __DIR__ . '/../../includes/logs.php';
            Logger::log('logout', 'Logout realizado.');
        } catch (\Exception $e) {}

        Auth::logout();
    }

    /**
     * Renders the login page HTML directly (no layout header/footer).
     */
    private function renderLogin(
        string $error,
        array  $settings = [],
        bool   $warn_session = false,
        string $pre_username = '',
        string $pre_password = '',
        ?array $company = null
    ): void {
        global $platform_settings;
        $settings = array_merge($platform_settings ?? [], $settings);
        $system_name  = $company ? $company['name'] : htmlspecialchars($settings['system_name'] ?? 'SaaSFlow Core');
        
        // Prioritize company theme if available
        $theme_slug = 'gold-black';
        if ($company && !empty($company['theme'])) {
            $theme_slug = $company['theme'];
        } else {
            $theme_slug = $settings['system_theme'] ?? 'gold-black';
        }
        $theme_slug = htmlspecialchars($theme_slug);
        $csrf_token   = \CSRF::generateToken();
        $v            = (string)time();

        include __DIR__ . '/../Views/auth/login.php';
        exit;
    }
}
