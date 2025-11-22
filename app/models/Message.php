<?php
// FILE: /app/models/Message.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Message Model
 * Handles individual messages within conversations
 */
class Message extends Model
{
    protected $table = 'messages';

    /**
     * Get messages by conversation
     * @param int $conversationId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByConversation($conversationId, $limit = 50, $offset = 0)
    {
        $sql = "SELECT m.*, u.name as sender_name
                FROM {$this->table} m
                LEFT JOIN users u ON m.user_id = u.id
                WHERE m.conversation_id = ?
                ORDER BY m.created_at ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$conversationId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Create message
     * @param array $data
     * @return int Message ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Mark messages as read
     * @param int $conversationId
     * @return bool
     */
    public function markAsRead($conversationId)
    {
        $sql = "UPDATE {$this->table} SET status = 'read'
                WHERE conversation_id = ? AND direction = 'inbound' AND status = 'unread'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$conversationId]);
    }

    /**
     * Count messages by tenant for a time period
     * @param int $tenantId
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function countByTenantAndPeriod($tenantId, $startDate, $endDate)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} m
                INNER JOIN conversations c ON m.conversation_id = c.id
                WHERE c.tenant_id = ? AND m.created_at BETWEEN ? AND ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $startDate, $endDate]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get messages sent per day for analytics
     * @param int $tenantId
     * @param int $days
     * @return array
     */
    public function getMessagesPerDay($tenantId, $days = 30)
    {
        $sql = "SELECT DATE(m.created_at) as date, COUNT(*) as count
                FROM {$this->table} m
                INNER JOIN conversations c ON m.conversation_id = c.id
                WHERE c.tenant_id = ? AND m.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(m.created_at)
                ORDER BY date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $days]);
        return $stmt->fetchAll();
    }

    /**
     * Count sent messages this month by tenant
     * @param int $tenantId
     * @return int
     */
    public function countSentThisMonth($tenantId)
    {
        $startOfMonth = date('Y-m-01 00:00:00');
        $endOfMonth = date('Y-m-t 23:59:59');

        $sql = "SELECT COUNT(*) as count FROM {$this->table} m
                INNER JOIN conversations c ON m.conversation_id = c.id
                WHERE c.tenant_id = ?
                AND m.direction = 'outbound'
                AND m.created_at BETWEEN ? AND ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $startOfMonth, $endOfMonth]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
}
