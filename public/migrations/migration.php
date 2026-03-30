<?php
/**
 * Migration Script - Master Wrapper
 * Path: scripts/migration.php
 * Regra 3.26: Centralizar alterações e garantir idempotência.
 */

// Este script apenas chama a migração unificada em public/migrations/
require_once __DIR__ . '/../public/migrations/migrar.php';
