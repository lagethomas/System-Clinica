<?php

class PlanRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all plans
     */
    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM cp_plans ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get plan by ID
     */
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM cp_plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Save/Update plan
     */
    public function save($data) {
        if (isset($data['id']) && $data['id']) {
            $stmt = $this->pdo->prepare("
                UPDATE cp_plans SET 
                name = ?, base_price = ?, included_users = ?, extra_user_price = ?, trial_days = ?, partner_commission_percentage = ?
                WHERE id = ?
            ");
            return $stmt->execute([
                $data['name'], $data['base_price'], $data['included_users'], $data['extra_user_price'], $data['trial_days'] ?? 7, 
                $data['partner_commission_percentage'] ?? 0, $data['id']
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO cp_plans (name, base_price, included_users, extra_user_price, trial_days, partner_commission_percentage) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $data['name'], $data['base_price'], $data['included_users'], $data['extra_user_price'], $data['trial_days'] ?? 7,
                $data['partner_commission_percentage'] ?? 0
            ]);
        }
    }

    /**
     * Delete plan
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM cp_plans WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
