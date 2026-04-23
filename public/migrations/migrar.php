<?php
/**
 * Unified Migration Script - SaaS Flow (v2.8.0)
 * Responsável pela evolução do esquema do banco de dados de forma centralizada e idempotente.
 * Regras: .agenterules.md (Seção 3)
 */

declare(strict_types=1);

require_once __DIR__ . '/../../includes/DB.php';
global $pdo, $platform_settings;

// --- CONFIGURAÇÃO E SEGURANÇA ---
$token = $_GET['token'] ?? '';
$validToken = $_ENV['DB_MIGRATION_TOKEN'] ?? ($platform_settings['db_migration_token'] ?? '76269223e7');

if ($token !== $validToken) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Token de migração inválido. Use ?token=' . $validToken]);
    exit;
}

$logs = [];
function addLog(string $msg): void { 
    global $logs; 
    $logs[] = "[" . date('H:i:s') . "] " . $msg; 
}

// --- HELPERS IDEMPOTENTES ---

/**
 * Executa um SQL genérico com captura de erro
 */
function safeExec(string $sql, string $label): void {
    global $pdo;
    try {
        $pdo->exec($sql);
        addLog("✅ " . $label);
    } catch (Exception $e) {
        addLog("⚠️ Ignorado/Erro: $label -> " . $e->getMessage());
    }
}

/**
 * Adiciona uma coluna se ela não existir
 */
function addCol(string $table, string $col, string $def): void {
    global $pdo;
    try {
        $check = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$col'")->fetch();
        if (!$check) {
            $pdo->exec("ALTER TABLE `$table` ADD COLUMN $col $def");
            addLog("✅ Coluna '$col' adicionada em '$table'");
        }
    } catch (Exception $e) {
        addLog("❌ Erro ao adicionar '$col' em '$table': " . $e->getMessage());
    }
}

/**
 * Adiciona um índice se ele não existir
 */
function addIndex(string $table, string $indexName, string $columns): void {
    global $pdo;
    try {
        $idx = $pdo->query("SHOW INDEX FROM `$table` WHERE Key_name = '$indexName'")->fetch();
        if (!$idx) {
            $pdo->exec("ALTER TABLE `$table` ADD INDEX `$indexName` ($columns)");
            addLog("✅ Índice '$indexName' criado em '$table'");
        }
    } catch (Exception $e) {
        addLog("⚠️ Erro ao criar índice '$indexName' em '$table' (ou já existe).");
    }
}

// --- EXECUÇÃO DAS MIGRAÇÕES ---

addLog("Iniciando Migração Centralizada v2.8.0...");

// 1. Tabelas Base (SaaS Core)
safeExec("CREATE TABLE IF NOT EXISTS `cp_settings` (
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_settings");

safeExec("CREATE TABLE IF NOT EXISTS `cp_notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT,
    `link` VARCHAR(255) DEFAULT NULL,
    `type` VARCHAR(50) DEFAULT 'info',
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_notifications");

// 2. Evolução de Tabelas Existentes
addCol('cp_companies', 'expires_at', 'DATE NULL AFTER status');
addCol('cp_companies', 'inactive_since', 'DATE NULL AFTER status');
addCol('cp_companies', 'subscription_status', 'VARCHAR(50) NULL AFTER status');
addCol('cp_companies', 'mp_enabled', 'TINYINT(1) DEFAULT 0');
addCol('cp_companies', 'mp_public_key', "VARCHAR(255) DEFAULT NULL COMMENT 'Mercado Pago Public Key'");
addCol('cp_companies', 'mp_access_token', "TEXT DEFAULT NULL COMMENT 'Mercado Pago Access Token (Production)'");
addCol('cp_companies', 'partner_id', 'INT NULL AFTER plan_id');
addCol('cp_companies', 'custom_domain', 'VARCHAR(255) NULL AFTER slug');
addCol('cp_invoices', 'last_reminder_date', 'DATE NULL');

// 3. Multi-Company Support & Tutor Portal
$tablesToIsolation = ['cp_users', 'cp_financeiro', 'cp_logs', 'cp_invoices'];
foreach ($tablesToIsolation as $tbl) {
    addCol($tbl, 'company_id', 'INT NULL AFTER id');
    addIndex($tbl, 'idx_company_id', 'company_id');
}

// 4. Específicos do Módulo Clínica v2.4.0
safeExec("CREATE TABLE IF NOT EXISTS `cp_planos_pet` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `pet_id` INT NOT NULL,
  `numero_carteirinha` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('ativo', 'inativo', 'cancelado') DEFAULT 'ativo',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_company` (`company_id`),
  INDEX `idx_pet` (`pet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_planos_pet");

addCol('cp_users', 'tutor_id', 'INT NULL AFTER company_id');
addCol('cp_users', 'active', 'TINYINT(1) DEFAULT 1 AFTER state');
addIndex('cp_users', 'idx_users_tutor', 'tutor_id');

addCol('cp_financeiro', 'tutor_id', 'INT NULL AFTER user_id');
addIndex('cp_financeiro', 'idx_finance_tutor', 'tutor_id');

addCol('cp_tutores', 'contrato_url', 'VARCHAR(255) NULL AFTER telefone');

// 5. Módulo de Produtos / Loja
safeExec("CREATE TABLE IF NOT EXISTS `cp_produtos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `descricao` TEXT DEFAULT NULL,
  `preco` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `preco_promocional` DECIMAL(10, 2) DEFAULT NULL,
  `em_promocao` TINYINT(1) DEFAULT 0,
  `capa` VARCHAR(255) DEFAULT NULL,
  `status` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_company` (`company_id`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_produtos_company` FOREIGN KEY (`company_id`) REFERENCES `cp_companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_produtos");

// 6. Módulo Loja Online v2.5.0 - Pedidos via WhatsApp/Carrinho
safeExec("CREATE TABLE IF NOT EXISTS `cp_pedidos_loja` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `cliente_nome` VARCHAR(255) NOT NULL,
  `cliente_telefone` VARCHAR(30) DEFAULT NULL,
  `observacoes` TEXT DEFAULT NULL,
  `total` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pendente', 'confirmado', 'cancelado', 'entregue') DEFAULT 'pendente',
  `cashback_used` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
  `itens_json` JSON DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_company` (`company_id`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_pedidos_loja_company` FOREIGN KEY (`company_id`) REFERENCES `cp_companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_pedidos_loja");

// 7. Configurações Padrão
$defaultSettings = [
    'system_name' => 'VetManager SaaS',
    'grace_period' => '2',
    'days_before_notify' => '5',
    'billing_grace_days' => '10',
    'enable_system_logs' => '1',
    'security_single_session' => '0',
    'system_theme' => 'clinic-blue'
];

foreach ($defaultSettings as $sk => $sv) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO cp_settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute([$sk, $sv]);
}

// 8. Módulo Loja Online v2.6.0 - Checkout Completo & Mercado Pago
addCol('cp_companies', 'taxa_entrega', 'DECIMAL(10,2) DEFAULT 0.00');

addCol('cp_pedidos_loja', 'zip_code', 'VARCHAR(20) NULL');
addCol('cp_pedidos_loja', 'neighborhood', 'VARCHAR(100) NULL');
addCol('cp_pedidos_loja', 'address', 'VARCHAR(255) NULL');
addCol('cp_pedidos_loja', 'city', 'VARCHAR(100) NULL');
addCol('cp_pedidos_loja', 'state', 'VARCHAR(2) NULL');
addCol('cp_pedidos_loja', 'number', 'VARCHAR(20) NULL');
addCol('cp_pedidos_loja', 'complement', 'VARCHAR(255) NULL');
addCol('cp_pedidos_loja', 'tipo', "ENUM('delivery', 'pickup') DEFAULT 'pickup'");
addCol('cp_pedidos_loja', 'payment_mode', "ENUM('delivery', 'online') DEFAULT 'delivery'");
addCol('cp_pedidos_loja', 'payment_status', "ENUM('pending', 'paid') DEFAULT 'pending'");
addCol('cp_pedidos_loja', 'payment_id', 'VARCHAR(255) NULL');
addCol('cp_pedidos_loja', 'frete', 'DECIMAL(10,2) DEFAULT 0.00');
addCol('cp_pedidos_loja', 'tutor_id', 'INT NULL');
addCol('cp_pedidos_loja', 'cashback_used', 'DECIMAL(10,2) DEFAULT 0.00');

// 10. Módulo de Categorias de Produtos
safeExec("CREATE TABLE IF NOT EXISTS `cp_categorias_produtos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_company` (`company_id`),
  CONSTRAINT `fk_categorias_company` FOREIGN KEY (`company_id`) REFERENCES `cp_companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_categorias_produtos");

addCol('cp_produtos', 'categoria_id', 'INT NULL AFTER company_id');
addIndex('cp_produtos', 'idx_categoria', 'categoria_id');

// 11. Módulo Clínica v2.7.0 (Sincronização com Sistema Principal)
addCol('cp_consultas', 'tutor_id', 'INT NULL AFTER id');
addCol('cp_consultas', 'servico', 'VARCHAR(255) NULL AFTER motivo');
addCol('cp_consultas', 'valor_cobrado', 'DECIMAL(10,2) DEFAULT 0.00 AFTER valor');
addIndex('cp_consultas', 'idx_tutor_id', 'tutor_id');

safeExec("CREATE TABLE IF NOT EXISTS `cp_consulta_anexos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `consulta_id` INT NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `arquivo_url` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_consulta` (`consulta_id`),
  CONSTRAINT `fk_anexos_consulta` FOREIGN KEY (`consulta_id`) REFERENCES `cp_consultas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_consulta_anexos");

// 9. Sincronização de Tutores -> Usuários (Portal do Cliente)
function syncTutoresToUsers(): void {
    global $pdo;
    $tutores = $pdo->query("SELECT * FROM cp_tutores")->fetchAll();
    addLog("Sincronizando logins para " . count($tutores) . " tutores...");

    foreach ($tutores as $t) {
        $check = $pdo->prepare("SELECT id FROM cp_users WHERE tutor_id = ?");
        $check->execute([$t['id']]);
        
        if (!$check->fetch()) {
            $email = trim($t['email'] ?? '');
            
            // Verificação de Email Duplicado para evitar erro 1062
            if (!empty($email)) {
                $checkEmail = $pdo->prepare("SELECT id FROM cp_users WHERE email = ?");
                $checkEmail->execute([$email]);
                if ($checkEmail->fetch()) {
                    addLog("⚠️ Ignorado: Email '$email' já existe para outro usuário.");
                    continue;
                }
            }

            $username = !empty($email) ? $email : ($t['cpf'] ? preg_replace('/\D/', '', $t['cpf']) : 'tutor_' . $t['id']);
            
            // Verificação de Username Duplicado
            $checkUser = $pdo->prepare("SELECT id FROM cp_users WHERE username = ?");
            $checkUser->execute([$username]);
            if ($checkUser->fetch()) {
                $username = $username . '_' . $t['id']; // Fallback se o username já existir
            }

            $rawPass = !empty($t['cpf']) ? preg_replace('/\D/', '', $t['cpf']) : 'Tutor123';
            $password = password_hash($rawPass, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO cp_users (company_id, tutor_id, name, email, username, password, role, active, created_at) VALUES (?, ?, ?, ?, ?, ?, 'usuario', 1, NOW())");
                $stmt->execute([
                    $t['company_id'],
                    $t['id'],
                    $t['nome'],
                    $email,
                    $username,
                    $password
                ]);
                addLog("✅ User criado p/: " . $t['nome']);
            } catch (Exception $e) {
                addLog("❌ Erro user " . $t['nome'] . ": " . $e->getMessage());
            }
        }
    }
}
syncTutoresToUsers();

// 10. Módulo de Cashback
safeExec("CREATE TABLE IF NOT EXISTS `cp_cashback_config` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `percentage` DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    `min_order_value` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `active` TINYINT(1) DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `idx_company_unique` (`company_id`),
    CONSTRAINT `fk_cashback_config_company` FOREIGN KEY (`company_id`) REFERENCES `cp_companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_cashback_config");

safeExec("CREATE TABLE IF NOT EXISTS `cp_cashback_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `tutor_id` INT NOT NULL,
    `order_id` INT DEFAULT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `type` ENUM('credit', 'debit') NOT NULL,
    `source` VARCHAR(50) DEFAULT 'admin' COMMENT 'Origem: order, admin, system',
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_company_tutor` (`company_id`, `tutor_id`),
    CONSTRAINT `fk_cashback_logs_company` FOREIGN KEY (`company_id`) REFERENCES `cp_companies`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cashback_logs_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `cp_tutores`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_cashback_logs");

addCol('cp_tutores', 'cashback_balance', 'DECIMAL(10, 2) DEFAULT 0.00');
addCol('cp_cashback_logs', 'source', "VARCHAR(50) DEFAULT 'admin' AFTER type");
addCol('cp_cashback_logs', 'order_id', "INT NULL AFTER tutor_id");

// --- Cashback Withdrawals (ClubePet+) ---
safeExec("CREATE TABLE IF NOT EXISTS `cp_cashback_withdrawals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `tutor_id` INT NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `pix_type` ENUM('cpf', 'cnpj', 'email', 'phone', 'random') NOT NULL,
    `pix_key` VARCHAR(255) NOT NULL,
    `status` ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `paid_at` TIMESTAMP NULL,
    INDEX (`company_id`),
    INDEX (`tutor_id`),
    INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_cashback_withdrawals");

addCol('cp_tutores', 'pix_type', 'VARCHAR(20) DEFAULT NULL');
addCol('cp_tutores', 'pix_key', 'VARCHAR(255) DEFAULT NULL');

// --- Credit Limit (Empréstimo) ---
addCol('cp_tutores', 'credit_limit', 'DECIMAL(10, 2) DEFAULT 0.00');

// --- Cashback Loans (Empréstimo) ---
safeExec("CREATE TABLE IF NOT EXISTS `cp_cashback_loans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `tutor_id` INT NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `installments` INT NOT NULL,
    `total_to_pay` DECIMAL(15,2) NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`company_id`),
    INDEX (`tutor_id`),
    INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;", "Tabela cp_cashback_loans");

// --- RESPOSTA FINAL ---
$version = 'v2.8.0';
if (($_GET['format'] ?? '') === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => "Migração unificada $version concluída!",
        'version' => $version,
        'logs'    => $logs
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migração de Sistema | <?= $version ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg); 
            color: var(--text); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 900px;
            background: var(--card);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            padding: 40px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.025em;
        }

        .header p {
            color: var(--text-muted);
            font-size: 14px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            background: rgba(16, 185, 129, 0.15);
            color: var(--primary);
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 16px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .logs-container {
            padding: 30px;
            max-height: 500px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.2);
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 13px;
            line-height: 1.6;
        }

        .logs-container::-webkit-scrollbar {
            width: 8px;
        }
        .logs-container::-webkit-scrollbar-track {
            background: transparent;
        }
        .logs-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
        }

        .log-item {
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 4px;
            display: flex;
            gap: 12px;
            transition: background 0.2s;
        }

        .log-item:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .log-time {
            color: var(--text-muted);
            white-space: nowrap;
            opacity: 0.6;
        }

        .log-msg {
            word-break: break-all;
        }

        .log-success { color: var(--success); }
        .log-error { color: var(--error); background: rgba(239, 68, 68, 0.05); }
        .log-warning { color: var(--warning); background: rgba(245, 158, 11, 0.05); }
        .log-info { color: var(--info); }

        .footer {
            padding: 20px 40px;
            background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .stats {
            display: flex;
            gap: 20px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .stat-item b {
            color: var(--text);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Migração Centralizada</h1>
            <p>Evolução de esquema e sincronização de dados</p>
            <div class="status-badge">
                <span style="width: 8px; height: 8px; background: currentColor; border-radius: 50%; margin-right: 8px; display: inline-block;"></span>
                Versão <?= $version ?> Concluída
            </div>
        </div>

        <div class="logs-container">
            <?php foreach ($logs as $log): 
                $class = 'log-info';
                if (strpos($log, '✅') !== false) $class = 'log-success';
                if (strpos($log, '❌') !== false) $class = 'log-error';
                if (strpos($log, '⚠️') !== false) $class = 'log-warning';
                
                // Extract time if exists [HH:MM:SS]
                $time = '';
                if (preg_match('/^\[(\d{2}:\d{2}:\d{2})\]/', $log, $matches)) {
                    $time = $matches[1];
                    $log = trim(str_replace($matches[0], '', $log));
                }
            ?>
                <div class="log-item <?= $class ?>">
                    <?php if ($time): ?><span class="log-time"><?= $time ?></span><?php endif; ?>
                    <span class="log-msg"><?= htmlspecialchars($log) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="footer">
            <div class="stats">
                <div class="stat-item">Logs: <b><?= count($logs) ?></b></div>
                <div class="stat-item">Ambiente: <b><?= defined('APP_ENV') ? APP_ENV : 'Production' ?></b></div>
            </div>
            <a href="/" class="btn">Voltar para o Sistema</a>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom of logs
        const container = document.querySelector('.logs-container');
        container.scrollTop = container.scrollHeight;
    </script>
</body>
</html>
<?php
