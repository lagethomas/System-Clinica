<?php
declare(strict_types=1);

namespace App\Modules\Produtos\Models;

use App\Core\Database;

class Categoria {
    public static function create(array $data): string {
        return Database::insert('cp_categorias_produtos', $data);
    }

    public static function update(int $id, array $data): bool {
        return Database::update('cp_categorias_produtos', $data, 'id = :id', ['id' => $id]);
    }

    public static function delete(int $id): bool {
        return Database::delete('cp_categorias_produtos', 'id = :id', ['id' => $id]);
    }

    public static function find(int $id) {
        return Database::fetch("SELECT * FROM cp_categorias_produtos WHERE id = ?", [$id]);
    }

    public static function allByCompany(int $companyId) {
        return Database::fetchAll("SELECT * FROM cp_categorias_produtos WHERE company_id = ? ORDER BY nome ASC", [$companyId]);
    }
}
