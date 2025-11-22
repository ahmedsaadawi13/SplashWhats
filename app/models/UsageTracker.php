<?php
// FILE: /app/models/UsageTracker.php

require_once __DIR__ . '/../core/Model.php';

/**
 * UsageTracker Model
 * Tracks tenant usage against subscription limits
 */
class UsageTracker extends Model
{
    protected $table = 'usage_tracker';

    /**
     * Get current month usage for tenant
     * @param int $tenantId
     * @return array|false
     */
    public function getCurrentMonthUsage($tenantId)
    {
        $month = date('Y-m');

        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? AND month = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $month]);
        return $stmt->fetch();
    }

    /**
     * Increment usage counter
     * @param int $tenantId
     * @param string $metric (contacts_count, messages_sent, campaigns_count, etc.)
     * @param int $amount
     * @return bool
     */
    public function increment($tenantId, $metric, $amount = 1)
    {
        $month = date('Y-m');

        // Check if record exists
        $usage = $this->getCurrentMonthUsage($tenantId);

        if ($usage) {
            // Update existing record
            $sql = "UPDATE {$this->table} SET $metric = $metric + ? WHERE tenant_id = ? AND month = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$amount, $tenantId, $month]);
        } else {
            // Create new record
            $data = [
                'tenant_id' => $tenantId,
                'month' => $month,
                $metric => $amount,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->insert($data);
            return true;
        }
    }

    /**
     * Check if tenant has exceeded limit
     * @param int $tenantId
     * @param string $metric
     * @param int $limit
     * @return bool
     */
    public function hasExceededLimit($tenantId, $metric, $limit)
    {
        $usage = $this->getCurrentMonthUsage($tenantId);

        if (!$usage) {
            return false;
        }

        return isset($usage[$metric]) && $usage[$metric] >= $limit;
    }

    /**
     * Get usage history for tenant
     * @param int $tenantId
     * @param int $months
     * @return array
     */
    public function getHistory($tenantId, $months = 6)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY month DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $months]);
        return $stmt->fetchAll();
    }
}
