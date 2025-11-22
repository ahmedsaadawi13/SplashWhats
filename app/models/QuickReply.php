<?php
// FILE: /app/models/QuickReply.php

require_once __DIR__ . '/../core/Model.php';

/**
 * QuickReply Model
 * Handles quick reply shortcuts for agents
 */
class QuickReply extends Model
{
    protected $table = 'quick_replies';

    /**
     * Get quick replies by tenant
     * @param int $tenantId
     * @return array
     */
    public function getByTenant($tenantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? ORDER BY shortcut ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Create quick reply
     * @param array $data
     * @return int Quick Reply ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Find by shortcut
     * @param string $shortcut
     * @param int $tenantId
     * @return array|false
     */
    public function findByShortcut($shortcut, $tenantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE shortcut = ? AND tenant_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$shortcut, $tenantId]);
        return $stmt->fetch();
    }
}
