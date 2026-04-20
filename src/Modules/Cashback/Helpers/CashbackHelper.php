<?php
declare(strict_types=1);

namespace App\Modules\Cashback\Helpers;

use App\Core\Database;
use App\Helpers\Logger;

class CashbackHelper {

    /**
     * Calculates and applies cashback for a specific order.
     */
    public static function applyForOrder(int $order_id): void {
        // Fetch order details
        $order = Database::fetch("SELECT * FROM cp_pedidos_loja WHERE id = :id", ['id' => $order_id]);
        if (!$order || !$order['tutor_id']) return;

        $company_id = (int)$order['company_id'];
        $tutor_id   = (int)$order['tutor_id'];
        $total      = (float)$order['total'];

        // Check if cashback was already applied for this order
        $already_applied = Database::fetch("SELECT id FROM cp_cashback_logs WHERE order_id = :oid AND type = 'credit'", ['oid' => $order_id]);
        if ($already_applied) return;

        // Fetch company cashback config
        $config = Database::fetch("SELECT * FROM cp_cashback_config WHERE company_id = :cid AND active = 1", ['cid' => $company_id]);
        if (!$config || (float)$config['percentage'] <= 0) return;

        // Calculate cashback
        $percentage = (float)$config['percentage'];
        $cashback_amount = ($total * $percentage) / 100;

        if ($cashback_amount <= 0) return;

        try {
            // 1. Update Tutor Balance
            Database::query("UPDATE cp_tutores SET cashback_balance = cashback_balance + :amt WHERE id = :tid", [
                'amt' => $cashback_amount,
                'tid' => $tutor_id
            ]);

            // 2. Log the transaction
            Database::insert('cp_cashback_logs', [
                'company_id' => $company_id,
                'tutor_id'   => $tutor_id,
                'order_id'   => $order_id,
                'amount'     => $cashback_amount,
                'type'       => 'credit',
                'description' => "Cashback ref. Pedido #$order_id (" . number_format($percentage, 1, ',', '.') . "%)"
            ]);

            Logger::log('cashback_applied', "Cashback de R$ " . number_format($cashback_amount, 2, ',', '.') . " aplicado ao tutor #$tutor_id (Pedido #$order_id)");

        } catch (\Exception $e) {
            Logger::log('cashback_error', "Erro ao aplicar cashback para pedido #$order_id: " . $e->getMessage());
        }
    }
}
