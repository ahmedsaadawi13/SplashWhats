<?php
// FILE: /app/models/Tag.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Tag Model
 * Handles tags for contact segmentation
 */
class Tag extends Model
{
    protected $table = 'tags';

    /**
     * Get tags by tenant
     * @param int $tenantId
     * @return array
     */
    public function getByTenant($tenantId)
    {
        $sql = "SELECT t.*, COUNT(ct.contact_id) as contact_count
                FROM {$this->table} t
                LEFT JOIN contact_tags ct ON t.id = ct.tag_id
                WHERE t.tenant_id = ?
                GROUP BY t.id
                ORDER BY t.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Create tag
     * @param array $data
     * @return int Tag ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Find tag by name and tenant
     * @param string $name
     * @param int $tenantId
     * @return array|false
     */
    public function findByName($name, $tenantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE name = ? AND tenant_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name, $tenantId]);
        return $stmt->fetch();
    }

    /**
     * Get top tags by contact count
     * @param int $tenantId
     * @param int $limit
     * @return array
     */
    public function getTopTags($tenantId, $limit = 5)
    {
        $sql = "SELECT t.*, COUNT(ct.contact_id) as contact_count
                FROM {$this->table} t
                LEFT JOIN contact_tags ct ON t.id = ct.tag_id
                WHERE t.tenant_id = ?
                GROUP BY t.id
                ORDER BY contact_count DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit]);
        return $stmt->fetchAll();
    }
}
