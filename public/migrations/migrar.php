<?php
/**
 * Unified Migration Script - SaaS Flow (v2.4.0)
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

addLog("Iniciando Migração Centralizada v2.4.0...");

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

// 5. Configurações Padrão
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

// 6. Sincronização de Tutores -> Usuários (Portal do Cliente)
function syncTutoresToUsers(): void {
    global $pdo;
    $tutores = $pdo->query("SELECT * FROM cp_tutores")->fetchAll();
    addLog("Sincronizando logins para " . count($tutores) . " tutores...");

    foreach ($tutores as $t) {
        $check = $pdo->prepare("SELECT id FROM cp_users WHERE tutor_id = ?");
        $check->execute([$t['id']]);
        
        if (!$check->fetch()) {
            $username = !empty($t['email']) ? $t['email'] : ($t['cpf'] ? preg_replace('/\D/', '', $t['cpf']) : 'tutor_' . $t['id']);
            $rawPass = !empty($t['cpf']) ? preg_replace('/\D/', '', $t['cpf']) : 'Tutor123';
            $password = password_hash($rawPass, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("INSERT INTO cp_users (company_id, tutor_id, name, email, username, password, role, active, created_at) VALUES (?, ?, ?, ?, ?, ?, 'usuario', 1, NOW())");
                $stmt->execute([
                    $t['company_id'],
                    $t['id'],
                    $t['nome'],
                    $t['email'] ?? '',
                    $username,
                    $password
                ]);
                addLog("User criado p/: " . $t['nome']);
            } catch (Exception $e) {
                addLog("Erro user " . $t['nome'] . ": " . $e->getMessage());
            }
        }
    }
}
syncTutoresToUsers();

// --- RESPOSTA FINAL ---
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Migração unificada v2.4.0 concluída!',
    'version' => 'v2.4.0',
    'logs' => $logs
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
