<?php
// FILE: /app/models/Subscription.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Subscription Model
 * Handles tenant subscriptions to plans
 */
class Subscription extends Model
{
    protected $table = 'tenant_subscriptions';

    /**
     * Get active subscription for tenant
     * @param int $tenantId
     * @return array|false
     */
    public function getActiveByStan($tenantId)
    {
        $sql = "SELECT s.*, p.name as plan_name, p.limits as plan_limits
                FROM {$this->table} s
                INNER JOIN plans p ON s.plan_id = p.id
                WHERE s.tenant_id = ? AND s.status = 'active'
                ORDER BY s.created_at DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        $subscription = $stmt->fetch();

        if ($subscription && !empty($subscription['plan_limits'])) {
            $subscription['plan_limits'] = json_decode($subscription['plan_limits'], true);
        }

        return $subscription;
    }

    /**
     * Create subscription
     * @param array $data
     * @return int Subscription ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Check if tenant has active subscription
     * @param int $tenantId
     * @return bool
     */
    public function hasActive($tenantId)
    {
        $subscription = $this->getActiveByStan($tenantId);
        return $subscription !== false;
    }

    /**
     * Get subscription history for tenant
     * @param int $tenantId
     * @return array
     */
    public function getHistory($tenantId)
    {
        $sql = "SELECT s.*, p.name as plan_name
                FROM {$this->table} s
                INNER JOIN plans p ON s.plan_id = p.id
                WHERE s.tenant_id = ?
                ORDER BY s.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }
}
