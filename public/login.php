<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/DB.php';
require_once __DIR__ . '/../src/Core/Autoloader.php';
App\Core\Autoloader::register();
require_once __DIR__ . '/../includes/helpers/Auth.php';
require_once __DIR__ . '/../includes/helpers/CSRF.php';
require_once __DIR__ . '/../includes/logs.php';
require_once __DIR__ . '/../includes/helpers/Nonce.php';

if (Auth::isLoggedIn()) {
    header('Location: dashboard');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF & Nonce Validation
    $csrf_token = $_POST['csrf_token'] ?? '';
    $login_nonce = $_POST['login_nonce'] ?? '';

    if (!CSRF::verifyToken($csrf_token)) {
        $error = 'Erro de segurança (CSRF). Tente novamente.';
    } elseif (!Nonce::verify($login_nonce, 'user_login', 0)) {
        $error = 'Erro de segurança (Nonce). Tente novamente.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username && $password) {
            $stmt = $pdo->prepare('SELECT * FROM cp_users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                Auth::login($user);
                
                \App\Helpers\Logger::log('login', "Login realizado.");
                
                header('Location: dashboard');
                exit;
            } else {
                $error = 'Credenciais inválidas.';
            }
        } else {
            $error = 'Preencha todos os campos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo htmlspecialchars($platform_settings['system_name'] ?? 'SaaSFlow Core'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo (string)time(); ?>">
    <link rel="stylesheet" href="assets/css/modules/auth.css?v=<?php echo (string)time(); ?>">
    <?php 
        $theme_slug = $platform_settings['system_theme'] ?? 'gold-black';
        echo '<link rel="stylesheet" href="assets/css/theme/' . $theme_slug . '.css?v=' . (string)time() . '">';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
                <i data-lucide="layer-group" class="icon-lucide"></i>
            <h2 class="auth-title"><?php echo htmlspecialchars($platform_settings['system_name'] ?? 'SaaSFlow'); ?></h2>
            <p class="auth-subtitle">Acesse sua conta para continuar</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-error">
                <i data-lucide="alert-circle" class="icon-lucide"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo CSRF::generateToken(); ?>">
            <input type="hidden" name="login_nonce" value="<?php echo Nonce::create('user_login', 0); ?>">
            <div class="form-group">
                <label class="auth-label">Usuário</label>
                <input type="text" name="username" class="form-control" placeholder="Seu usuário" required autofocus>
            </div>

            <div class="form-group mt-3">
                <label class="auth-label">Senha</label>
                <div class="password-toggle-wrapper">
                    <input type="password" name="password" id="password" class="form-control pr-10" placeholder="Sua senha" required>
                    <button type="button" class="btn-password-toggle" onclick="togglePassword('password')">
                        <i data-lucide="key" class="icon-lucide" id="password-toggle-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-primary btn-block mt-4" id="btnLogin">
                <span class="btn-text">Entrar no Sistema <i data-lucide="log-in" class="icon-lucide ml-2"></i></span>
                <span class="btn-loader" style="display: none;">
                    <i data-lucide="loader" class="icon-lucide lc-spin mr-2"></i> Processando...
                </span>
            </button>
        </form>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById('password-toggle-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-lucide', 'unlock');
            } else {
                input.type = 'password';
                icon.setAttribute('data-lucide', 'key');
            }
            if (window.lucide) lucide.createIcons({root: icon.parentElement});
        }

        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('btnLogin');
            const btnText = btn.querySelector('.btn-text');
            const btnLoader = btn.querySelector('.btn-loader');
            
            // Desabilitar para evitar múltiplos cliques
            btn.disabled = true;
            btnText.style.display = 'none';
            btnLoader.style.display = 'flex';
            btnLoader.style.alignItems = 'center';
            btnLoader.style.justifyContent = 'center';
        });
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
