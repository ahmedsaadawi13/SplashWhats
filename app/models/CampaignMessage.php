<?php
// FILE: /app/models/CampaignMessage.php

require_once __DIR__ . '/../core/Model.php';

/**
 * CampaignMessage Model
 * Handles individual messages within campaigns
 */
class CampaignMessage extends Model
{
    protected $table = 'campaign_messages';

    /**
     * Get messages by campaign with pagination
     * @param int $campaignId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByCampaign($campaignId, $limit = 50, $offset = 0)
    {
        $sql = "SELECT cm.*, c.name as contact_name, c.phone as contact_phone
                FROM {$this->table} cm
                INNER JOIN contacts c ON cm.contact_id = c.id
                WHERE cm.campaign_id = ?
                ORDER BY cm.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$campaignId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Create campaign message
     * @param array $data
     * @return int Campaign Message ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Update message status
     * @param int $messageId
     * @param string $status
     * @return bool
     */
    public function updateStatus($messageId, $status)
    {
        $data = ['status' => $status];

        if ($status === 'sent') {
            $data['sent_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($messageId, $data);
    }

    /**
     * Get pending messages for a campaign
     * @param int $campaignId
     * @param int $limit
     * @return array
     */
    public function getPending($campaignId, $limit = 100)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE campaign_id = ? AND status = 'pending'
                ORDER BY id ASC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$campaignId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Count messages by status
     * @param int $campaignId
     * @param string $status
     * @return int
     */
    public function countByStatus($campaignId, $status)
    {
        return $this->count(['campaign_id' => $campaignId, 'status' => $status]);
    }
}
