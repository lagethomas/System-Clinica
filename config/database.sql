-- SaaSFlow Core System Database Schema (v2.4.0)
-- Full Versatile SaaS Flow for Veterinary Clinics
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cp_plans
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `base_price` DECIMAL(10,2) NOT NULL DEFAULT 40.00,
    `included_users` INT NOT NULL DEFAULT 4,
    `extra_user_price` DECIMAL(10,2) NOT NULL DEFAULT 30.00,
    `trial_days` INT NOT NULL DEFAULT 7,
    `partner_commission_percentage` DECIMAL(5,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cp_companies
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_companies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE NOT NULL,
    `document` VARCHAR(255) DEFAULT NULL,
    `cnpj` VARCHAR(20) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `zip_code` VARCHAR(20) DEFAULT NULL,
    `street` VARCHAR(255) DEFAULT NULL,
    `neighborhood` VARCHAR(100) DEFAULT NULL,
    `address_number` VARCHAR(10) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `state` VARCHAR(50) DEFAULT NULL,
    `theme_color` VARCHAR(50) DEFAULT '#2563eb',
    `theme` VARCHAR(50) DEFAULT 'clinic-blue',
    `plan_id` INT DEFAULT NULL,
    `partner_id` INT DEFAULT NULL,
    `base_price` DECIMAL(10,2) DEFAULT 40.00,
    `included_users` INT DEFAULT 4,
    `extra_user_price` DECIMAL(10,2) DEFAULT 30.00,
    `peak_users_count` INT DEFAULT 0,
    `logo` VARCHAR(255) DEFAULT NULL,
    `background_image` VARCHAR(255) DEFAULT NULL,
    `active` TINYINT(1) DEFAULT 1,
    `status` ENUM('active', 'inactive', 'suspended', 'trial') DEFAULT 'active',
    `mp_access_token` TEXT DEFAULT NULL,
    `mp_public_key` TEXT DEFAULT NULL,
    `mp_enabled` TINYINT(1) DEFAULT 0,
    `subscription_status` VARCHAR(50) DEFAULT NULL,
    `inactive_since` DATE DEFAULT NULL,
    `trashed_at` DATETIME DEFAULT NULL,
    `expires_at` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_active` (`active`),
    INDEX `idx_expires` (`expires_at`),
    INDEX `idx_partner` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Table structure for cp_users
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `tutor_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'usuario',
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `current_session_id` varchar(255) DEFAULT NULL,
  `last_pulse` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `neighborhood` varchar(100) DEFAULT NULL,
  `address_number` varchar(10) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_email` (`email`),
  UNIQUE KEY `idx_username` (`username`),
  KEY `idx_company` (`company_id`),
  KEY `idx_tutor` (`tutor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_financeiro
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_financeiro` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT DEFAULT NULL,
  `user_id` INT DEFAULT NULL,
  `tutor_id` INT DEFAULT NULL,
  `descricao` VARCHAR(255) NOT NULL,
  `valor` DECIMAL(10, 2) NOT NULL,
  `tipo` ENUM('entrada', 'saida') NOT NULL,
  `categoria` VARCHAR(100) DEFAULT 'Geral',
  `metodo_pagamento` VARCHAR(50) DEFAULT 'Diversos',
  `data_movimentacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_company` (`company_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_tutor` (`tutor_id`),
  INDEX `idx_tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_invoices
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending','paid','cancelled','expired') DEFAULT 'pending',
    `type` ENUM('single','recurring') DEFAULT 'single',
    `description` TEXT DEFAULT NULL,
    `due_date` DATE NOT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_company` (`company_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_settings
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_settings` (
    `setting_key` VARCHAR(100) NOT NULL,
    `setting_value` TEXT,
    PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_notifications
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_notifications` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_logs
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `action` varchar(255) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_company` (`company_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_tutores (Clientes)
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_tutores` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `cpf` VARCHAR(20) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `telefone` VARCHAR(20) DEFAULT NULL,
  `zip_code` VARCHAR(20) DEFAULT NULL,
  `street` VARCHAR(255) DEFAULT NULL,
  `neighborhood` VARCHAR(100) DEFAULT NULL,
  `address_number` VARCHAR(10) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(50) DEFAULT NULL,
  `endereco` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_company` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_pets
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_pets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `tutor_id` INT NOT NULL,
  `nome` VARCHAR(255) NOT NULL,
  `numero_carteirinha` VARCHAR(100) DEFAULT NULL,
  `especie` VARCHAR(100) DEFAULT NULL,
  `raca` VARCHAR(100) DEFAULT NULL,
  `sexo` ENUM('M', 'F') DEFAULT NULL,
  `idade` VARCHAR(50) DEFAULT NULL,
  `peso` DECIMAL(10, 2) DEFAULT NULL,
  `cor` VARCHAR(50) DEFAULT NULL,
  `foto_url` VARCHAR(255) DEFAULT NULL,
  `microchip` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_company` (`company_id`),
  INDEX `idx_tutor` (`tutor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_planos_pet (Health Plans)
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_planos_pet` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `pet_id` INT NOT NULL,
  `numero_carteirinha` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('ativo', 'inativo', 'cancelado') DEFAULT 'ativo',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_company` (`company_id`),
  INDEX `idx_pet` (`pet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for cp_consultas
-- ----------------------------
CREATE TABLE IF NOT EXISTS `cp_consultas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `pet_id` INT NOT NULL,
  `data_consulta` DATETIME NOT NULL,
  `hora_consulta` TIME DEFAULT NULL,
  `servico` VARCHAR(100) DEFAULT 'Consulta',
  `diagnostico` TEXT DEFAULT NULL,
  `descricao` TEXT DEFAULT NULL,
  `valor_cobrado` DECIMAL(10, 2) DEFAULT 0.00,
  `status` ENUM('Agendada', 'Concluída', 'Cancelada') DEFAULT 'Agendada',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_company` (`company_id`),
  INDEX `idx_pet` (`pet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Constraints
-- ----------------------------
ALTER TABLE `cp_users` ADD FOREIGN KEY (`company_id`) REFERENCES `cp_companies`(`id`) ON DELETE SET NULL;
ALTER TABLE `cp_users` ADD FOREIGN KEY (`tutor_id`) REFERENCES `cp_tutores`(`id`) ON DELETE CASCADE;
ALTER TABLE `cp_financeiro` ADD FOREIGN KEY (`company_id`) REFERENCES `cp_companies`(`id`) ON DELETE CASCADE;
ALTER TABLE `cp_pets` ADD FOREIGN KEY (`tutor_id`) REFERENCES `cp_tutores`(`id`) ON DELETE CASCADE;
ALTER TABLE `cp_consultas` ADD FOREIGN KEY (`pet_id`) REFERENCES `cp_pets`(`id`) ON DELETE CASCADE;
ALTER TABLE `cp_planos_pet` ADD FOREIGN KEY (`pet_id`) REFERENCES `cp_pets`(`id`) ON DELETE CASCADE;
ALTER TABLE `cp_planos_pet` ADD FOREIGN KEY (`company_id`) REFERENCES `cp_companies`(`id`) ON DELETE CASCADE;

-- ----------------------------
-- Initial Data (Base)
-- ----------------------------
INSERT IGNORE INTO `cp_users` (`name`, `username`, `email`, `password`, `role`) VALUES 
('Administrador', 'admin', 'admin@admin.com', '$2y$12$P3WwePwHVEpmLvd4MSxVmuHwLFdmeMRKVNxUOrpT1IWs0YIyNbZBG', 'administrador');

INSERT IGNORE INTO `cp_settings` (`setting_key`, `setting_value`) VALUES 
('system_name', 'VetManager SaaS'),
('grace_period', '2'),
('days_before_notify', '5'),
('enable_system_logs', '1'),
('security_single_session', '0'),
('system_theme', 'clinic-blue');

SET FOREIGN_KEY_CHECKS = 1;
