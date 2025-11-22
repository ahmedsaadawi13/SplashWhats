<?php
// FILE: /app/models/Contact.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Contact Model
 * Handles contact data for each tenant
 */
class Contact extends Model
{
    protected $table = 'contacts';

    /**
     * Get contacts by tenant with pagination
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @param array $filters
     * @return array
     */
    public function getByTenant($tenantId, $limit = 20, $offset = 0, $filters = [])
    {
        $sql = "SELECT c.*, GROUP_CONCAT(t.name SEPARATOR ', ') as tags
                FROM {$this->table} c
                LEFT JOIN contact_tags ct ON c.id = ct.contact_id
                LEFT JOIN tags t ON ct.tag_id = t.id
                WHERE c.tenant_id = ?";

        $params = [$tenantId];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['tag_id'])) {
            $sql .= " AND ct.tag_id = ?";
            $params[] = $filters['tag_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " GROUP BY c.id ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find contact by phone and tenant
     * @param string $phone
     * @param int $tenantId
     * @return array|false
     */
    public function findByPhone($phone, $tenantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE phone = ? AND tenant_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$phone, $tenantId]);
        return $stmt->fetch();
    }

    /**
     * Create contact
     * @param array $data
     * @return int Contact ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Count contacts by tenant
     * @param int $tenantId
     * @param array $filters
     * @return int
     */
    public function countByTenant($tenantId, $filters = [])
    {
        $sql = "SELECT COUNT(DISTINCT c.id) as count FROM {$this->table} c
                LEFT JOIN contact_tags ct ON c.id = ct.contact_id
                WHERE c.tenant_id = ?";

        $params = [$tenantId];

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['tag_id'])) {
            $sql .= " AND ct.tag_id = ?";
            $params[] = $filters['tag_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get contact tags
     * @param int $contactId
     * @return array
     */
    public function getTags($contactId)
    {
        $sql = "SELECT t.* FROM tags t
                INNER JOIN contact_tags ct ON t.id = ct.tag_id
                WHERE ct.contact_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contactId]);
        return $stmt->fetchAll();
    }

    /**
     * Attach tag to contact
     * @param int $contactId
     * @param int $tagId
     * @return bool
     */
    public function attachTag($contactId, $tagId)
    {
        $sql = "INSERT IGNORE INTO contact_tags (contact_id, tag_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$contactId, $tagId]);
    }

    /**
     * Detach tag from contact
     * @param int $contactId
     * @param int $tagId
     * @return bool
     */
    public function detachTag($contactId, $tagId)
    {
        $sql = "DELETE FROM contact_tags WHERE contact_id = ? AND tag_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$contactId, $tagId]);
    }

    /**
     * Get contacts by tag
     * @param int $tagId
     * @param int $tenantId
     * @return array
     */
    public function getByTag($tagId, $tenantId)
    {
        $sql = "SELECT c.* FROM {$this->table} c
                INNER JOIN contact_tags ct ON c.id = ct.contact_id
                WHERE ct.tag_id = ? AND c.tenant_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tagId, $tenantId]);
        return $stmt->fetchAll();
    }
}
