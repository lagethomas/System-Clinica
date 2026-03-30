<?php
declare(strict_types=1);
global $pdo;
require_once __DIR__ . '/helpers/Auth.php';
require_once __DIR__ . '/helpers/CSRF.php';
require_once __DIR__ . '/repositories/NotificationRepository.php';

$user_id = (int)($_SESSION['user_id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? 'Usuário';
$user_role = $_SESSION['user_role'] ?? 'usuario';

// Page title detector (MVC Aware)
global $current_page;
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// Remove o base path do SITE_URL se houver (ex: /folder/dashboard -> dashboard)
$site_path = parse_url(SITE_URL, PHP_URL_PATH) ?: '';
$route = str_replace($site_path, '', $uri);
$route = trim($route, '/');

// Se for vazio ou index.php (legado), padrão é dashboard
if (empty($route) || $route === 'index.php') {
    $route = 'dashboard';
}

// Para CSS de módulos, pegamos a última parte da rota
$route_parts = explode('/', $route);
$current_page = end($route_parts);
$page_titles = [
    'dashboard.php' => 'Painel de Controle',
    'dashboard' => 'Painel de Controle',
    'users.php' => 'Usuários',
    'users' => 'Usuários',
    'logs.php' => 'Logs Globais',
    'logs' => 'Logs do Sistema',
    'settings.php' => 'Configurações',
    'settings' => 'Configurações',
    'profile' => 'Meu Perfil',
    'integrations.php' => 'Integrações',
    'integrations' => 'Integrações',
    'financeiro' => 'Módulo Financeiro',
    'plans' => 'Pacotes de Assinatura',
    'companies' => 'Empresas Clientes',
    'subscriptions' => 'Faturas e Assinaturas'
];

// Fetch Notifications
$notifRepo = new NotificationRepository($pdo);
if ($user_id && Auth::isProprietario()) {
    $cid = Auth::companyId();
    // 1. Check pending invoices
    $notif_invoices = $pdo->prepare("SELECT id, due_date, amount FROM cp_invoices WHERE company_id = ? AND status = 'pending' LIMIT 5");
    $notif_invoices->execute([$cid]);
    $graceDays = (int)($platform_settings['grace_period'] ?? 2);
    
    foreach ($notif_invoices->fetchAll() as $inv) {
        $link = SITE_URL . '/app/subscriptions';
        $dueDate = strtotime($inv['due_date']);
        $daysDiff = floor(($dueDate - time()) / 86400);
        
        $title = "Fatura Pendente — R$ " . number_format((float)$inv['amount'], 2, ',', '.');
        $msg = "Você possui uma fatura no valor de R$ " . number_format((float)$inv['amount'], 2, ',', '.') . " aguardando pagamento.";

        if ($daysDiff < 0) {
            $daysPast = (string)abs($daysDiff);
            $title = "Fatura Vencida! URGENTE";
            $msg = "Fatura vencida há $daysPast dias. Seu acesso entrará em bloqueio total a qualquer momento.";
        } elseif ($daysDiff == 0) {
            $title = "Atenção: Fatura vence HOJE!";
            $msg = "Pague hoje para evitar o bloqueio automático do seu salão.";
        } elseif ($daysDiff <= 3) {
            $msg = "Fatura de renovação vence em " . (string)$daysDiff . " dias. Próximo do bloqueio automatizado.";
        }
        
        // Prevent duplicates (even if already read)
        $check = $pdo->prepare("SELECT id FROM cp_notifications WHERE user_id = ? AND link = ? AND title = ? LIMIT 1");
        $check->execute([$user_id, $link, $title]);
        if (!$check->fetch()) {
            $notifRepo->create([
                'user_id' => $user_id,
                'title' => $title,
                'message' => $msg,
                'link' => $link,
                'type' => 'receipt'
            ]);
        }
    }
}
$unread_notifications = $user_id ? $notifRepo->getUnreadByUser($user_id) : [];

// --- TUTOR NOTIFICATION FILTER ---
if (Auth::isTutor()) {
    $unread_notifications = array_filter($unread_notifications, function($n) {
        // Tutors only see info/reminder types, never billing or system receipts
        return in_array($n['type'], ['info', 'reminder', 'pet']);
    });
}
$unread_count = count($unread_notifications);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo CSRF::generateToken(); ?>">
    <?php 
    // Use Pre-loaded Platform Settings from db.php (Global SaaS Settings)
    global $platform_settings;
    $theme_slug = $platform_settings['system_theme'] ?? 'gold-black';
    $system_name = $platform_settings['system_name'] ?? 'SaaSFlow Core';
    $system_logo = $platform_settings['system_logo'] ?? '';

    // If Company User, override with Company Name/Theme
    $company_id = Auth::companyId();
    if ($company_id && !Auth::isAdmin()) {
        $company_data = $pdo->prepare("SELECT name, theme, logo FROM cp_companies WHERE id = ?");
        $company_data->execute([$company_id]);
        $comp = $company_data->fetch();
        if ($comp) {
            if (!empty($comp['name'])) $system_name = $comp['name'];
            if (!empty($comp['theme'])) $theme_slug = $comp['theme'];
            if (!empty($comp['logo'])) {
                $system_logo = $comp['logo'];
                // Fix for possible legacy paths or mismatches (Rule 42)
                if (strpos($system_logo, '/assets/img/companies/') !== false) {
                    $system_logo = str_replace('/assets/img/companies/', '/uploads/companies/', $system_logo);
                }
            }
        }
    }
    ?>
    <title><?php echo htmlspecialchars(($page_titles[$current_page] ?? 'Início') . ' | ' . ($system_name ?? 'SaaSFlow')); ?></title>
    
    <?php if (!empty($system_logo)): ?>
        <link rel="icon" type="image/webp" href="<?php echo SITE_URL . $system_logo; ?>">
    <?php endif; ?>
    
    <?php 
    $theme_file = "/assets/css/theme/{$theme_slug}.css";
    if ($theme_slug === 'default' || !file_exists(__DIR__ . '/../public' . $theme_file)) {
        $theme_slug = 'gold-black';
    }
    ?>
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/theme/' . $theme_slug . '.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/components/notifications.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/components/page-content.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/components/main-footer.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/components/popups.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/components/dashboard-stats.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/components/switches.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/components/badges.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/components/global-search.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/components/floating-autocomplete.css'); ?>">
    <link rel="stylesheet" href="<?php echo \App\Core\Controller::asset('/assets/css/app-premium.css'); ?>">
    
    <?php 
    // Auto-load page specific CSS from modules
    $page_name = str_replace('.php', '', $current_page);
    $css_path = dirname(__FILE__) . "/../public/assets/css/modules/{$page_name}.css";
    if (file_exists($css_path)) {
        echo '<link rel="stylesheet" href="' . \App\Core\Controller::asset('/assets/css/modules/' . $page_name . '.css') . '">';
    }
    ?>

    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    <script src="<?php echo \App\Core\Controller::asset('/assets/js/components/floating-autocomplete.js'); ?>"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="<?php echo Auth::isTutor() ? SITE_URL . '/app/tutor/dashboard' : SITE_URL . '/dashboard'; ?>" class="logo">
                    <div class="sidebar-logo-icon">
                        <?php if (!empty($system_logo)): ?>
                            <img src="<?php echo SITE_URL . $system_logo; ?>" style="width: 100%; height: 100%; object-fit: contain;">
<?php else: ?>
                            <i data-lucide="layers" class="icon-lucide"></i>
<?php endif; ?>
                    </div>
                    <span><?php echo htmlspecialchars($system_name); ?></span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <?php if (!Auth::isTutor()): ?>
                    <li class="<?php echo ($current_page == 'dashboard.php' || $current_page == 'dashboard') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/dashboard">
                            <i data-lucide="layout-dashboard" class="icon-lucide"></i> <span>Dashboard</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php 
                    // SUBSCRIPTION BLOCKING LOGIC FOR SIDEBAR
                    $isRestricted = false;
                    $expirationAlert = null;
                    $alertType = 'info';

                    if (Auth::isProprietario()) {
                        $cid = Auth::companyId();
                        $stmtE = $pdo->prepare("SELECT expires_at, status FROM cp_companies WHERE id = ?");
                        $stmtE->execute([$cid]);
                        $compE = $stmtE->fetch();

                        if ($compE && !empty($compE['expires_at'])) {
                            $expires = strtotime($compE['expires_at']);
                            $graceDays = (int)($platform_settings['grace_period'] ?? 2);
                            $daysNotifyBefore = (int)($platform_settings['days_before_notify'] ?? 5);
                            $limit = $expires + ($graceDays * 86400);

                            // Block logic
                            if (time() > $limit && $compE['status'] !== 'trial') {
                                $isRestricted = true;
                            }

                            // Alert stripe logic
                            $daysRemaining = (int)ceil(($expires - time()) / 86400);
                            if (time() > $expires) {
                                $daysInGrace = (int)ceil((time() - $expires) / 86400);
                                $expirationAlert = "Sua assinatura venceu há " . (string)$daysInGrace . " dias! Você está no período de carência. Regularize para evitar bloqueio.";
                                $alertType = 'danger';
                            } elseif ($daysRemaining <= 0) {
                                $expirationAlert = "Sua assinatura vence HOJE! Pague agora para evitar interrupções.";
                                $alertType = 'warning';
                            } elseif ($daysRemaining <= $daysNotifyBefore) {
                                $expirationAlert = "Sua assinatura vence em " . (string)$daysRemaining . " dias. Garanta a continuidade do seu serviço.";
                                $alertType = 'info';
                            }
                        }

                        // IF No company-level expiration alert, check for pending invoices
                        if (!$expirationAlert) {
                            $stmtI = $pdo->prepare("SELECT due_date, amount FROM cp_invoices WHERE company_id = ? AND status = 'pending' ORDER BY due_date ASC LIMIT 1");
                            $stmtI->execute([$cid]);
                            $inv = $stmtI->fetch();
                            
                            if ($inv) {
                                $dueDate = strtotime($inv['due_date']);
                                $amount = number_format((float)$inv['amount'], 2, ',', '.');
                                $daysRemaining = (int)ceil(($dueDate - time()) / 86400);

                                if ($daysRemaining < 0) {
                                    $daysPast = (string)abs($daysRemaining);
                                    $expirationAlert = "Fatura URGENTE de R$ $amount vencida há $daysPast dias! Regularize para evitar suspensão.";
                                    $alertType = 'danger';
                                } elseif ($daysRemaining == 0) {
                                    $expirationAlert = "Fatura de R$ $amount vence HOJE! Pague agora para evitar o bloqueio automático.";
                                    $alertType = 'warning';
                                } elseif ($daysRemaining <= ($platform_settings['days_before_notify'] ?? 5)) {
                                    $expirationAlert = "Fatura de R$ $amount vencendo em " . (string)$daysRemaining . " dias. Garanta o funcionamento e continuidade do serviço.";
                                    $alertType = 'info';
                                }
                            }
                        }
                    }
                    ?>

                    <?php if (Auth::isTutor()): ?>
                    <li class="nav-heading text-muted mt-3 mb-2 px-3 small fw-bold text-uppercase" style="opacity: 0.6; font-size: 0.75rem;">Portal do Cliente</li>
                    <li class="<?php echo ($route == 'app/tutor/dashboard' || strpos($route, 'app/tutor/pet/') !== false) ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/app/tutor/dashboard">
                            <i data-lucide="home" class="icon-lucide"></i> <span>Minha Área</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (!Auth::isAdmin() && !Auth::isTutor() && !$isRestricted): ?>
                    <li class="<?php echo ($current_page == 'consultas') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/app/consultas">
                            <i data-lucide="calendar" class="icon-lucide"></i> <span>Agenda</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'pets') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/app/pets">
                            <i data-lucide="dog" class="icon-lucide"></i> <span>Pets (Pacientes)</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'tutores') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/app/tutores">
                            <i data-lucide="users" class="icon-lucide"></i> <span>Clientes (Tutores)</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'financeiro') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/app/financeiro">
                            <i data-lucide="wallet" class="icon-lucide"></i> <span>Financeiro</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (Auth::isProprietario()): ?>
                        <?php 
                            $pending_count = \App\Core\Database::fetch("SELECT COUNT(*) as total FROM cp_invoices WHERE company_id = :cid AND status = 'pending'", ['cid' => Auth::companyId()])['total'] ?? 0;
                        ?>
                        <li class="<?php echo ($current_page == 'subscriptions') ? 'active' : ''; ?>">
                            <a href="<?php echo SITE_URL; ?>/app/subscriptions" class="d-flex align-items-center">
                                <i data-lucide="receipt" class="icon-lucide"></i> 
                                <span>Minhas Faturas</span>
                                <?php if ($pending_count > 0): ?>
                                    <span class="badge-dot-active ml-auto" style="background: var(--danger); width: 8px; height: 8px; border-radius: 50%; display: inline-block;"></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (Auth::isProprietario() && !$isRestricted): ?>
                    <li class="<?php echo ($current_page == 'users.php' || $current_page == 'users') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/users">
                            <i data-lucide="users" class="icon-lucide"></i> <span>Usuários / Equipe</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'company_settings.php' || $current_page == 'company-settings') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/app/company-settings">
                            <i data-lucide="building" class="icon-lucide"></i> <span>Configurar Empresa</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if (Auth::isAdmin()): ?>
                    <li class="<?php echo ($current_page == 'users.php' || $current_page == 'users') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/users">
                            <i data-lucide="users" class="icon-lucide"></i> <span>Usuários</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'integrations.php' || $current_page == 'integrations') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/integrations">
                            <i data-lucide="plug" class="icon-lucide"></i> <span>Integrações</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'logs.php' || $current_page == 'logs') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/logs">
                            <i data-lucide="terminal" class="icon-lucide"></i> <span>Logs Globais</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'settings.php' || $current_page == 'settings') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/settings">
                            <i data-lucide="settings" class="icon-lucide"></i> <span>Configurações</span>
                        </a>
                    </li>
                    <li class="nav-heading text-muted mt-3 mb-2 px-3 small fw-bold text-uppercase" style="opacity: 0.6; font-size: 0.75rem;">SaaS Management</li>
                    <li class="<?php echo ($current_page == 'plans' || $current_page == 'admin/plans') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/plans">
                            <i data-lucide="package-open" class="icon-lucide"></i> <span>Planos de Assinatura</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'companies' || $current_page == 'admin/companies') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/companies">
                            <i data-lucide="building" class="icon-lucide"></i> <span>Empresas Clientes</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'financeiro' || $current_page == 'admin/financeiro') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/financeiro">
                            <i data-lucide="line-chart" class="icon-lucide"></i> <span>Financeiro SaaS</span>
                        </a>
                    </li>
                    <li class="<?php echo ($current_page == 'subscriptions' || $current_page == 'admin/subscriptions') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/subscriptions">
                            <i data-lucide="receipt" class="icon-lucide"></i> <span>Faturas / Assinaturas</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <div class="user-profile" id="user-profile-trigger">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($user_name); ?> <i data-lucide="chevron-up" class="icon-lucide"></i></span>
                        <span class="user-role"><?php echo ucfirst($user_role); ?></span>
                    </div>
                </div>
                <!-- Popup de Perfil/Sair -->
                <div class="sidebar-user-dropdown" id="user-dropdown">
                    <?php if (!Auth::isTutor()): ?>
                    <a href="<?php echo SITE_URL; ?>/profile" class="btn-secondary" style="display: flex; align-items: center; gap: 10px; padding: 12px; border-radius: 8px; text-decoration: none; color: var(--text-main); background: rgba(255,255,255,0.03); border: 1px solid var(--border);">
                        <i data-lucide="user-circle" class="icon-lucide"></i> Meu Perfil Maroto
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="user-dropdown-item danger">
                        <i data-lucide="log-out" class="icon-lucide"></i> Sair do Sistema
                    </a>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <button class="menu-toggle mobile-only" onclick="toggleSidebar()">
                        <i data-lucide="menu" class="icon-lucide"></i>
                    </button>
                    <button class="sidebar-collapse-toggle desktop-only" onclick="toggleSidebarCollapse()" title="Alternar Menu">
                        <i data-lucide="<?php echo ($isCollapsed ?? false) ? 'chevrons-right' : 'chevrons-left'; ?>" class="icon-lucide icon-sm"></i>
                    </button>
                    <h2 class="page-title"><?php echo $page_titles[$current_page] ?? 'Início'; ?></h2>
                </div>

                <div class="top-nav-right">
                    <!-- Global Search -->
                    <?php if (!Auth::isTutor()): ?>
                    <div class="global-search-container" id="global-search">
                        <div class="search-input-wrapper">
                            <i data-lucide="search" class="icon-lucide search-icon"></i>
                            <input type="text" id="global-search-input" placeholder="Buscar pacientes, tutores, prontuários..." autocomplete="off">
                            <kbd class="search-shortcut">/</kbd>
                        </div>
                        <div class="global-search-results" id="global-search-results">
                            <!-- Results inject via JS -->
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Notificações -->
                    <div class="notif-trigger" id="notif-trigger">
                        <i data-lucide="bell" class="icon-lucide"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="notif-badge"><?php echo (string)$unread_count; ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="notification-dropdown" id="notif-dropdown">
                        <div class="notif-header">
                            <span>Notificações</span>
                            <?php if ($unread_count > 0): ?>
                                <button onclick="markAllRead()" class="btn-mark-read">Marcar todas como lidas</button>
                            <?php endif; ?>
                        </div>
                        <div class="notif-list">
                            <?php if (empty($unread_notifications)): ?>
                                <div class="notif-empty">
                                    <i data-lucide="bell-off" class="icon-lucide"></i>
                                    <span>Nenhuma nova notificação</span>
                                </div>
                            <?php else: ?>
                                <?php foreach ($unread_notifications as $notif): ?>
                                    <div class="notif-wrapper">
                                        <a href="<?php echo $notif['link'] ?: '#'; ?>" class="notif-item">
                                            <div class="notif-icon primary">
                                                <i data-lucide="info" class="icon-lucide"></i>
                                            </div>
                                            <div class="notif-content">
                                                <span class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></span>
                                                <span class="notif-text"><?php echo htmlspecialchars($notif['message']); ?></span>
                                                <span class="notif-time"><?php echo date('d/m H:i', strtotime($notif['created_at'])); ?></span>
                                            </div>
                                        </a>
                                        <button onclick="markRead(<?php echo $notif['id']; ?>, event)" class="btn-notif-delete-right" title="Remover">
                                            <i data-lucide="x" class="icon-lucide"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                                <style>
                                    .notif-wrapper { position: relative; display: flex; overflow: hidden; border-bottom: 1px solid var(--border); transition: 0.2s; }
                                    .notif-wrapper:hover { background: rgba(255,255,255,0.02); }
                                    .btn-notif-delete-right { 
                                        width: 0; 
                                        min-width: 0; 
                                        background: #ef4444; 
                                        color: #fff; 
                                        border: none; 
                                        cursor: pointer; 
                                        display: flex; 
                                        align-items: center; 
                                        justify-content: center; 
                                        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                        font-size: 14px;
                                    }
                                    .notif-wrapper:hover .btn-notif-delete-right { width: 45px; }
                                    .notif-item { flex: 1; border-bottom: none !important; }
                                </style>
                            <?php endif; ?>
                        </div>
                        <div class="notif-footer">
                            <a href="#">Ver todas as notificações</a>
                        </div>
                    </div>
                </div>
            </header>
            <div class="page-content">
                <?php if ($expirationAlert): ?>
                    <div class="alert-stripe-billing <?php echo $alertType; ?>" style="margin-bottom: 20px; padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; gap: 15px; font-weight: 600; border: 1px solid rgba(255,255,255,0.1); color: #fff;">
                        <i data-lucide="<?php echo $alertType === 'danger' ? 'alert-triangle' : 'info'; ?>" class="icon-lucide"></i>
                        <span><?php echo $expirationAlert; ?></span>
                        <a href="<?php echo SITE_URL; ?>/app/subscriptions" class="btn-primary" style="margin-left: auto; padding: 6px 15px; font-size: 13px; border-radius: 8px;">Pagar Agora</a>
                    </div>
                    <style>
                        .alert-stripe-billing.info { background: linear-gradient(90deg, #3b82f6, #2563eb); }
                        .alert-stripe-billing.warning { background: linear-gradient(90deg, #f59e0b, #d97706); }
                        .alert-stripe-billing.danger { background: linear-gradient(90deg, #ef4444, #dc2626); }
                    </style>
                <?php endif; ?>
<script>

// Dropdown de perfil
document.getElementById('user-profile-trigger').addEventListener('click', function(e) {
    e.stopPropagation();
    const dropdown = document.getElementById('user-dropdown');
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
    this.classList.toggle('active');
});

// Dropdown de notificações
document.getElementById('notif-trigger').addEventListener('click', function(e) {
    e.stopPropagation();
    document.getElementById('notif-dropdown').classList.toggle('active');
});

// Fechar dropdowns ao clicar fora
document.addEventListener('click', function() {
    document.getElementById('user-dropdown').style.display = 'none';
    const notif = document.getElementById('notif-dropdown');
    if (notif) notif.classList.remove('active');
});

async function markRead(id, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    
    // Identificar o wrapper da notificação
    const btn = event.currentTarget;
    const wrapper = btn.closest('.notif-wrapper');
    
    if (wrapper) {
        wrapper.style.opacity = '0.5';
        wrapper.style.pointerEvents = 'none';
    }

    try {
        const res = await fetch('<?php echo SITE_URL; ?>/api/notifications/read/' + id);
        const data = await res.json();

        if (res.ok && data.success) {
            if (wrapper) {
                // Animação de saída
                wrapper.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                wrapper.style.transform = 'translateX(50px)';
                wrapper.style.opacity = '0';
                
                setTimeout(() => {
                    wrapper.remove();
                    
                    // Atualizar o contador no sininho
                    const badge = document.querySelector('.notif-badge');
                    if (badge) {
                        let count = parseInt(badge.innerText) || 0;
                        count--;
                        if (count <= 0) {
                            badge.remove();
                        } else {
                            badge.innerText = count;
                        }
                    }

                    // Se não houver mais notificações, mostrar o estado vazio
                    const notifList = document.querySelector('.notif-list');
                    if (notifList && notifList.querySelectorAll('.notif-wrapper').length === 0) {
                        notifList.innerHTML = `
                            <div class="notif-empty">
                                <i data-lucide="bell-off" class="icon-lucide"></i>
                                <span>Nenhuma nova notificação</span>
                            </div>
                        `;
                        if (window.lucide) lucide.createIcons();
                    }
                }, 400);
            }
        }
    } catch (e) {
        if (wrapper) {
            wrapper.style.opacity = '1';
            wrapper.style.pointerEvents = 'all';
        }
        console.error("Erro ao marcar como lido:", e);
    }
}

async function markAllRead() {
    const res = await fetch('<?php echo SITE_URL; ?>/api/notifications/read_all');
    if (res.ok) window.location.reload();
}

// ── GLOBAL SEARCH ──────────────────────────────────────────────
const globalSearchInput = document.getElementById('global-search-input');
const globalSearchResults = document.getElementById('global-search-results');
let searchDebounce = null;

if (globalSearchInput) {
    globalSearchInput.addEventListener('input', function() {
        clearTimeout(searchDebounce);
        const query = this.value.trim();
        
        if (query.length < 2) {
            globalSearchResults.classList.remove('active');
            return;
        }

        searchDebounce = setTimeout(async () => {
            try {
                const res = await fetch(`<?php echo SITE_URL; ?>/api/search?q=${encodeURIComponent(query)}`);
                const data = await res.json();
                
                if (data.results && data.results.length > 0) {
                    renderSearchResults(data.results);
                } else {
                    globalSearchResults.innerHTML = '<div class="p-3 text-center text-muted small">Nenhum resultado encontrado.</div>';
                    globalSearchResults.classList.add('active');
                }
            } catch (err) {
                console.error("Erro na busca global:", err);
            }
        }, 300);
    });

    // Keyboard Shortcut (/)
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
            e.preventDefault();
            globalSearchInput.focus();
        }
        if (e.key === 'Escape') {
            globalSearchResults.classList.remove('active');
            globalSearchInput.blur();
        }
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#global-search')) {
            globalSearchResults.classList.remove('active');
        }
    });
}

function renderSearchResults(results) {
    let html = '';
    const groups = {};

    results.forEach(item => {
        if (!groups[item.type]) groups[item.type] = [];
        groups[item.type].push(item);
    });

    const typeIcons = {
        'pet': 'dog',
        'tutor': 'user',
        'company': 'building',
        'user': 'users'
    };

    const typeLabels = {
        'pet': 'Pets / Pacientes',
        'tutor': 'Clientes / Tutores',
        'company': 'Empresas',
        'user': 'Equipe / Usuários'
    };

    for (const type in groups) {
        html += `<div class="search-result-group">
                    <div class="search-group-title">${typeLabels[type] || type}</div>
                    ${groups[type].map(res => `
                        <a href="${res.url}" class="search-result-item">
                            <div class="search-result-icon">
                                <i data-lucide="${typeIcons[type] || 'info'}" class="icon-lucide"></i>
                            </div>
                            <div class="search-result-info">
                                <span class="result-name">${res.name}</span>
                                <span class="result-sub">${res.sub || ''}</span>
                            </div>
                        </a>
                    `).join('')}
                </div>`;
    }

    globalSearchResults.innerHTML = html;
    globalSearchResults.classList.add('active');
    if (window.lucide) lucide.createIcons();
}
</script>
