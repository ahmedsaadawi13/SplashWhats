<?php
// FILE: /app/models/User.php

require_once __DIR__ . '/../core/Model.php';

/**
 * User Model
 * Handles user data and authentication
 */
class User extends Model
{
    protected $table = 'users';

    /**
     * Find user by email
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Create a new user
     * @param array $data
     * @return int User ID
     */
    public function create($data)
    {
        // Hash password before storing
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insert($data);
    }

    /**
     * Update last login timestamp
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin($userId)
    {
        $sql = "UPDATE {$this->table} SET last_login = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([date('Y-m-d H:i:s'), $userId]);
    }

    /**
     * Get users by tenant
     * @param int $tenantId
     * @return array
     */
    public function getByTenant($tenantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Get agents by tenant
     * @param int $tenantId
     * @return array
     */
    public function getAgentsByTenant($tenantId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? AND role = 'agent' ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    /**
     * Count users by tenant
     * @param int $tenantId
     * @return int
     */
    public function countByTenant($tenantId)
    {
        return $this->count(['tenant_id' => $tenantId]);
    }
}
