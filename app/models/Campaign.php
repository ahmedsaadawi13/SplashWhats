<?php
// FILE: /app/models/Campaign.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Campaign Model
 * Handles bulk messaging campaigns
 */
class Campaign extends Model
{
    protected $table = 'campaigns';

    /**
     * Get campaigns by tenant with pagination
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 20, $offset = 0)
    {
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM campaign_messages WHERE campaign_id = c.id) as total_recipients,
                (SELECT COUNT(*) FROM campaign_messages WHERE campaign_id = c.id AND status = 'sent') as sent_count,
                (SELECT COUNT(*) FROM campaign_messages WHERE campaign_id = c.id AND status = 'failed') as failed_count
                FROM {$this->table} c
                WHERE c.tenant_id = ?
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Create campaign
     * @param array $data
     * @return int Campaign ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Count campaigns by tenant
     * @param int $tenantId
     * @return int
     */
    public function countByTenant($tenantId)
    {
        return $this->count(['tenant_id' => $tenantId]);
    }

    /**
     * Get campaign with full details
     * @param int $campaignId
     * @return array|false
     */
    public function getWithDetails($campaignId)
    {
        $sql = "SELECT c.*,
                (SELECT COUNT(*) FROM campaign_messages WHERE campaign_id = c.id) as total_recipients,
                (SELECT COUNT(*) FROM campaign_messages WHERE campaign_id = c.id AND status = 'sent') as sent_count,
                (SELECT COUNT(*) FROM campaign_messages WHERE campaign_id = c.id AND status = 'delivered') as delivered_count,
                (SELECT COUNT(*) FROM campaign_messages WHERE campaign_id = c.id AND status = 'failed') as failed_count,
                (SELECT COUNT(*) FROM campaign_messages WHERE campaign_id = c.id AND status = 'pending') as pending_count
                FROM {$this->table} c
                WHERE c.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$campaignId]);
        return $stmt->fetch();
    }

    /**
     * Update campaign status
     * @param int $campaignId
     * @param string $status
     * @return bool
     */
    public function updateStatus($campaignId, $status)
    {
        return $this->update($campaignId, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get active campaigns count
     * @param int $tenantId
     * @return int
     */
    public function countActive($tenantId)
    {
        return $this->count(['tenant_id' => $tenantId, 'status' => 'running']);
    }
}
