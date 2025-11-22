<?php
// FILE: /app/models/Invoice.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Invoice Model
 * Handles billing invoices
 */
class Invoice extends Model
{
    protected $table = 'invoices';

    /**
     * Get invoices by tenant
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 20, $offset = 0)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE tenant_id = ?
                ORDER BY created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Create invoice
     * @param array $data
     * @return int Invoice ID
     */
    public function create($data)
    {
        // Generate invoice number
        $data['invoice_number'] = $this->generateInvoiceNumber();
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Generate unique invoice number
     * @return string
     */
    private function generateInvoiceNumber()
    {
        return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Update invoice status
     * @param int $invoiceId
     * @param string $status
     * @return bool
     */
    public function updateStatus($invoiceId, $status)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($status === 'paid') {
            $data['paid_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($invoiceId, $data);
    }

    /**
     * Count invoices by tenant
     * @param int $tenantId
     * @return int
     */
    public function countByTenant($tenantId)
    {
        return $this->count(['tenant_id' => $tenantId]);
    }
}
