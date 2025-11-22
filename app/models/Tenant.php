<?php
// FILE: /app/models/Tenant.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Tenant Model
 * Handles tenant (company) data
 */
class Tenant extends Model
{
    protected $table = 'tenants';

    /**
     * Create a new tenant
     * @param array $data
     * @return int Tenant ID
     */
    public function create($data)
    {
        // Generate API key
        $data['api_key'] = $this->generateApiKey();
        $data['status'] = $data['status'] ?? 'active';
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->insert($data);
    }

    /**
     * Generate unique API key
     * @return string
     */
    private function generateApiKey()
    {
        return 'sk_' . bin2hex(random_bytes(32));
    }

    /**
     * Find tenant by API key
     * @param string $apiKey
     * @return array|false
     */
    public function findByApiKey($apiKey)
    {
        $sql = "SELECT * FROM {$this->table} WHERE api_key = ? AND status = 'active' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$apiKey]);
        return $stmt->fetch();
    }

    /**
     * Get all active tenants
     * @return array
     */
    public function getActiveTenants()
    {
        return $this->findAll(['status' => 'active']);
    }

    /**
     * Update tenant settings
     * @param int $tenantId
     * @param array $settings
     * @return bool
     */
    public function updateSettings($tenantId, $settings)
    {
        $data = [
            'settings' => json_encode($settings),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        return $this->update($tenantId, $data);
    }
}
