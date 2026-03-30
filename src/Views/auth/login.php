<?php
// Handled by LoginController::renderLogin
$systemLogo = (isset($company) && !empty($company['logo'])) ? SITE_URL . '/' . ltrim($company['logo'], '/') : (!empty($settings['system_logo']) ? SITE_URL . '/' . ltrim($settings['system_logo'], '/') : null);
$bgImage = (isset($company) && !empty($company['background_image'])) ? SITE_URL . '/' . ltrim($company['background_image'], '/') : (!empty($settings['cardapio_bg']) ? SITE_URL . '/' . ltrim($settings['cardapio_bg'], '/') : null);
$primaryColor = (isset($company) && !empty($company['theme_color'])) ? $company['theme_color'] : ($settings['system_color'] ?? null);

// Helper to convert hex to RGB
function hex2rgb_auth($hex) {
    if (!$hex) return "212, 175, 55";
    $hex = str_replace("#", "", $hex);
    // Verificar se é um hex válido, se não for (ex: var(--primary)), retorna default
    if (!ctype_xdigit($hex)) return "212, 175, 55";
    
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } elseif(strlen($hex) == 6) {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    } else {
        return "212, 175, 55";
    }
    return "$r, $g, $b";
}
$primaryRGB = hex2rgb_auth($primaryColor);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $system_name; ?></title>
    
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo $v; ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modules/auth.css?v=<?php echo $v; ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/theme/<?php echo $theme_slug; ?>.css?v=<?php echo $v; ?>">
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            <?php if ($primaryColor): ?>
            --primary: <?php echo $primaryColor; ?> !important;
            --primary-rgb: <?php echo $primaryRGB; ?> !important;
            <?php endif; ?>
        }
        <?php if ($bgImage): ?>
        body.auth-wrapper {
            background: linear-gradient(rgba(10, 12, 16, 0.8), rgba(10, 12, 16, 0.9)), url('<?php echo $bgImage; ?>') center/cover no-repeat fixed !important;
        }
        <?php endif; ?>
    </style>
</head>
<body class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo-box">
                <?php if ($systemLogo): ?>
                    <img src="<?php echo $systemLogo; ?>" alt="Logo">
                <?php else: ?>
                    <i data-lucide="utensils" class="icon-lucide"></i>
                <?php endif; ?>
            </div>
            <h2 class="auth-title"><?php echo $system_name; ?></h2>
            <p class="auth-subtitle">Acesse para gerenciar sua unidade</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <i data-lucide="alert-circle" class="icon-lucide"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($warn_session): ?>
            <div class="alert-session">
                <i data-lucide="shield" class="icon-lucide"></i> <strong>Sessão Ativa</strong>
                <p style="margin: 5px 0;">Já existe uma sessão aberta para este usuário.</p>
                <form method="POST" action="<?php echo SITE_URL; ?>/login" id="forceForm">
                    <input type="hidden" name="csrf_token"  value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="username"    value="<?php echo htmlspecialchars($pre_username); ?>">
                    <input type="hidden" name="password"    value="<?php echo htmlspecialchars($pre_password); ?>">
                    <input type="hidden" name="force_login" value="1">
                    <input type="hidden" name="company_id"  value="<?php echo $company['id'] ?? ''; ?>">
                    <button type="submit" class="btn-force" id="btnForce">Encerrar anterior e entrar</button>
                </form>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo SITE_URL; ?>/login" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="company_id" value="<?php echo $company['id'] ?? ''; ?>">
            
            <div class="form-group">
                <label class="auth-label">Usuário</label>
                <input type="text" name="username" class="form-control"
                       value="<?php echo htmlspecialchars($pre_username); ?>"
                       placeholder="Digite seu usuário" required autofocus>
            </div>

            <div class="form-group">
                <label class="auth-label">Senha</label>
                <div class="password-toggle-wrapper">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Sua senha secreta" required>
                    <button type="button" class="btn-password-toggle" onclick="togglePassword(this, 'password')">
                        <i data-lucide="eye" class="icon-lucide"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-auth-submit mt-2" id="btnLogin">
                <span class="btn-text">Entrar no Sistema <i data-lucide="arrow-right" class="icon-lucide"></i></span>
                <span class="btn-loader" style="display: none;">
                    <i data-lucide="loader" class="icon-lucide"></i> Entrando...
                </span>
            </button>
        </form>
        
        <?php if (!empty($company) && !empty($company['slug'])): ?>
        <div class="auth-footer">
             <a href="<?php echo SITE_URL . '/' . htmlspecialchars($company['slug']); ?>">
                 <i data-lucide="arrow-left" class="icon-lucide"></i> Voltar ao Cardápio
             </a>
        </div>
        <?php endif; ?>
    </div>

    <script src="<?php echo SITE_URL; ?>/assets/js/components/ui-core.js"></script>
    <script>
        function togglePassword(btn, id) {
            const input = document.getElementById(id);
            if(!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.innerHTML = '<i data-lucide="' + (isPassword ? 'eye-off' : 'eye') + '" class="icon-lucide"></i>';
            if(window.lucide) {
                lucide.createIcons({nameAttr: 'data-lucide', root: btn});
            }
        }


        function lockBtn(formId, btnId) {
            const form = document.getElementById(formId);
            if (!form) return;
            form.addEventListener('submit', function() {
                const btn = document.getElementById(btnId);
                if (!btn) return;
                btn.disabled = true;
                const text   = btn.querySelector('.btn-text');
                const loader = btn.querySelector('.btn-loader');
                if (text)   text.style.display  = 'none';
                if (loader) { loader.style.display = 'flex'; loader.style.alignItems = 'center'; }
            });
        }
        lockBtn('loginForm', 'btnLogin');
        lockBtn('forceForm',  'btnForce');
        
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
