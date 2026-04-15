<?php
require_once __DIR__ . '/../../includes/DB.php';
global $pdo;
try {
    $stmt = $pdo->query("DESC cp_consultas");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cols, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
