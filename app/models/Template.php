<?php
// FILE: /app/models/Template.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Template Model
 * Handles message templates with placeholders
 */
class Template extends Model
{
    protected $table = 'templates';

    /**
     * Get templates by tenant
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
     * Create template
     * @param array $data
     * @return int Template ID
     */
    public function create($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Replace placeholders in template
     * @param string $body
     * @param array $variables
     * @return string
     */
    public function replacePlaceholders($body, $variables)
    {
        foreach ($variables as $key => $value) {
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }
        return $body;
    }

    /**
     * Get templates by category
     * @param int $tenantId
     * @param string $category
     * @return array
     */
    public function getByCategory($tenantId, $category)
    {
        $sql = "SELECT * FROM {$this->table} WHERE tenant_id = ? AND category = ? ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $category]);
        return $stmt->fetchAll();
    }
}
