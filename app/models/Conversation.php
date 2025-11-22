<?php
// FILE: /app/models/Conversation.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Conversation Model
 * Handles conversations between contacts and agents
 */
class Conversation extends Model
{
    protected $table = 'conversations';

    /**
     * Get conversations by tenant with pagination
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @param array $filters
     * @return array
     */
    public function getByTenant($tenantId, $limit = 20, $offset = 0, $filters = [])
    {
        $sql = "SELECT c.*,
                co.name as contact_name,
                co.phone as contact_phone,
                u.name as assigned_agent_name,
                (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND direction = 'inbound' AND status = 'unread') as unread_count,
                (SELECT body FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_at
                FROM {$this->table} c
                INNER JOIN contacts co ON c.contact_id = co.id
                LEFT JOIN users u ON c.assigned_to = u.id
                WHERE c.tenant_id = ?";

        $params = [$tenantId];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND c.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        if (isset($filters['unread']) && $filters['unread']) {
            $sql .= " AND (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND direction = 'inbound' AND status = 'unread') > 0";
        }

        $sql .= " ORDER BY last_message_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find or create conversation for contact
     * @param int $contactId
     * @param int $tenantId
     * @return array
     */
    public function findOrCreate($contactId, $tenantId)
    {
        // Try to find existing conversation
        $sql = "SELECT * FROM {$this->table} WHERE contact_id = ? AND tenant_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contactId, $tenantId]);
        $conversation = $stmt->fetch();

        if ($conversation) {
            return $conversation;
        }

        // Create new conversation
        $data = [
            'tenant_id' => $tenantId,
            'contact_id' => $contactId,
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $conversationId = $this->insert($data);
        return $this->findById($conversationId);
    }

    /**
     * Count conversations by tenant
     * @param int $tenantId
     * @param array $filters
     * @return int
     */
    public function countByTenant($tenantId, $filters = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE tenant_id = ?";
        $params = [$tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['assigned_to'])) {
            $sql .= " AND assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Assign conversation to agent
     * @param int $conversationId
     * @param int $agentId
     * @return bool
     */
    public function assignToAgent($conversationId, $agentId)
    {
        return $this->update($conversationId, [
            'assigned_to' => $agentId,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update conversation status
     * @param int $conversationId
     * @param string $status
     * @return bool
     */
    public function updateStatus($conversationId, $status)
    {
        return $this->update($conversationId, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
