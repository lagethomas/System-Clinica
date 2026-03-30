<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    /**
     * Check if user is logged in and session is valid
     */
    public static function isLoggedIn(): bool {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // --- SESSION HIJACKING PROTECTION (Rule 6) ---
        // Validate User-Agent and IP to prevent stolen session usage
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $userIp = $_SERVER['REMOTE_ADDR'] ?? '';

        if (!isset($_SESSION['secure_ua']) || !isset($_SESSION['secure_ip'])) {
            // Passive initialization fallback (Rule 6 migration)
            $_SESSION['secure_ua'] = $userAgent;
            $_SESSION['secure_ip'] = $userIp;
            return true; 
        }

        if ($_SESSION['secure_ua'] !== $userAgent || $_SESSION['secure_ip'] !== $userIp) {
            self::logout();
            return false;
        }

        return true;
    }

    /**
     * Check if user is a global administrator
     */
    public static function isAdmin(): bool {
        return self::isLoggedIn() && isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'administrador';
    }

    public static function isProprietario(): bool {
        return self::isLoggedIn() && (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'proprietario');
    }

    /**
     * Check if user is a clinic tutor (client)
     * Rule: Role is 'usuario' but linked to a tutor_id
     */
    public static function isTutor(): bool {
        return self::isLoggedIn() && !empty($_SESSION['tutor_id']);
    }

    public static function hasPermission(string $roleRequired): bool {
        if ($roleRequired === 'administrador') return self::isAdmin();
        if ($roleRequired === 'proprietario') return self::isProprietario();
        if ($roleRequired === 'tutor') return self::isTutor();
        return false;
    }

    /**
     * Redirect if not logged in or subscription expired
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            // Check if it's an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Sessão expirada. Por favor, faça login novamente.', 'redirect' => SITE_URL . '/login']);
                exit;
            }

            $slug = $_SESSION['company_slug'] ?? $_COOKIE['last_company_slug'] ?? null;
            if (!empty($slug)) {
                header("Location: " . SITE_URL . "/" . $slug . "/login");
            } else {
                header("Location: " . SITE_URL . "/login");
            }
            exit;
        }

        // --- SUBSCRIPTION CHECK (Grace Period Logic) ---
        if (self::isProprietario()) {
            self::checkSubscriptionStatus();
        }
    }

    /**
     * Blocks access if subscription + grace period expired
     */
    public static function checkSubscriptionStatus(): void {
        $cid = self::companyId();
        if (!$cid) return;

        global $pdo, $platform_settings;
        $stmt = $pdo->prepare("SELECT expires_at, status FROM cp_companies WHERE id = ?");
        $stmt->execute([$cid]);
        $comp = $stmt->fetch();

        if ($comp && !empty($comp['expires_at'])) {
            $expires = strtotime($comp['expires_at']);
            $graceDays = (int)($platform_settings['grace_period'] ?? 2);
            $limit = $expires + ($graceDays * 86400);

            // If completely expired (after grace)
            if (time() > $limit && $comp['status'] !== 'trial') {
                // Stay on subscriptions page, but block others
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                if (strpos($uri, 'subscriptions') === false && strpos($uri, 'logout') === false) {
                    header("Location: " . SITE_URL . "/app/subscriptions?blocked=1");
                    exit;
                }
            }
        }
    }

    /**
     * Require Specific Role access
     */
    public static function requireRole(string $role): void {
        self::requireLogin();
        if (!self::hasPermission($role)) {
            header("Location: " . SITE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * Require Global Admin access
     */
    public static function requireAdmin(): void {
        self::requireRole('administrador');
    }

    /**
     * Login user and initialize secure session markers
     */
    public static function login(array $user): void {
        // Force session ID regeneration on login
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['company_id'] = $user['company_id'] ?? null;
        $_SESSION['company_slug'] = $user['company_slug'] ?? null;
        $_SESSION['tutor_id'] = $user['tutor_id'] ?? null;
        $_SESSION['last_activity'] = time();

        // Security markers
        $_SESSION['secure_ua'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['secure_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Persist slug in cookie for redirect fallbacks (timeout/expired sessions)
        if (!empty($user['company_slug'])) {
            setcookie('last_company_slug', $user['company_slug'], time() + (86400 * 30), "/"); // 30 days
        }

        // Update last login and session ID in DB
        global $pdo;
        $stmt = $pdo->prepare("UPDATE cp_users SET last_login = NOW(), current_session_id = ?, last_pulse = NOW() WHERE id = ?");
        $stmt->execute([session_id(), $user['id']]);
    }

    /**
     * Single Session Enforcement: Check if user already has an active session elsewhere
     */
    public static function hasActiveSession(int $userId): bool {
        global $pdo;
        $stmt = $pdo->prepare("SELECT current_session_id, last_pulse FROM cp_users WHERE id = ?");
        $stmt->execute([$userId]);
        $res = $stmt->fetch();

        if ($res && !empty($res['current_session_id'])) {
            // Check if session is actually still active (last pulse < 10 mins)
            $lastPulse = strtotime($res['last_pulse'] ?? '0');
            if ((time() - $lastPulse) < 600) {
                // If it's the SAME session, don't block
                if ($res['current_session_id'] === session_id()) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    public static function clearSessionFromDB(int $userId): void {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE cp_users SET current_session_id = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }

    /**
     * Logout user and clear session
     */
    public static function logout(): void {
        $slug = $_SESSION['company_slug'] ?? $_COOKIE['last_company_slug'] ?? null;
        
        session_unset();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        
        if (!headers_sent()) {
            if (!empty($slug)) {
                header("Location: " . SITE_URL . "/" . $slug . "/login");
            } else {
                header("Location: " . SITE_URL . "/login");
            }
            exit;
        }
    }


    /**
     * Check for session inactivity (2 hours)
     */
    public static function checkInactivity(): void {
        if (!isset($_SESSION['user_id'])) return;

        $timeout = 7200; // 2 hours
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            self::logout();
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Get current user ID
     */
    public static function id(): ?int {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    /**
     * Get current user's company ID
     */
    public static function companyId(): ?int {
        return isset($_SESSION['company_id']) ? (int)$_SESSION['company_id'] : null;
    }

    /**
     * Get current user's tutor ID (if role is tutor)
     */
    public static function tutorId(): ?int {
        return isset($_SESSION['tutor_id']) ? (int)$_SESSION['tutor_id'] : null;
    }

    /**
     * Require Specific Permission for a module
     */
    public static function requirePermission(string $module): void {
        self::requireLogin();
        
        $role = strtolower($_SESSION['user_role'] ?? '');
        
        // Define module permissions
        $permissions = [
            'users' => ['administrador', 'proprietario'],
            'financeiro' => ['administrador', 'proprietario'],
            'relatorios' => ['administrador', 'proprietario'],
            'vet' => ['administrador', 'proprietario']
        ];

        // Rule: Tutors are strictly forbidden from any management module
        if (self::isTutor()) {
            header("Location: " . SITE_URL . "/app/tutor/dashboard");
            exit;
        }

        if (!isset($permissions[$module]) || !in_array($role, $permissions[$module])) {
            header("Location: " . SITE_URL . "/dashboard");
            exit;
        }
    }
}

// Global inactivity check
Auth::checkInactivity();
