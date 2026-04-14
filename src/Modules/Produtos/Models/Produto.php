<?php
declare(strict_types=1);

namespace App\Modules\Produtos\Models;

use App\Core\Database;

class Produto {
    public static function create(array $data): string {
        return Database::insert('cp_produtos', $data);
    }

    public static function update(int $id, array $data): bool {
        return Database::update('cp_produtos', $data, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): bool {
        return Database::delete('cp_produtos', 'id = :id', ['id' => $id]);
    }

    public static function find(int $id) {
        return Database::fetch("SELECT * FROM cp_produtos WHERE id = ?", [$id]);
    }

    public static function allByCompany(int $companyId) {
        return Database::fetchAll("SELECT * FROM cp_produtos WHERE company_id = ? ORDER BY id DESC", [$companyId]);
    }

    public static function allPublicByCompany(int $companyId) {
        return Database::fetchAll("SELECT * FROM cp_produtos WHERE company_id = ? AND status = 1 ORDER BY id DESC", [$companyId]);
    }
}
