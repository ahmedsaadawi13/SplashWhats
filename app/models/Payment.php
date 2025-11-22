<?php
// FILE: /app/models/Payment.php

require_once __DIR__ . '/../core/Model.php';

/**
 * Payment Model
 * Handles payment records
 */
class Payment extends Model
{
    protected $table = 'payments';

    /**
     * Get payments by tenant
     * @param int $tenantId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getByTenant($tenantId, $limit = 20, $offset = 0)
    {
        $sql = "SELECT p.*, i.invoice_number
                FROM {$this->table} p
                LEFT JOIN invoices i ON p.invoice_id = i.id
                WHERE p.tenant_id = ?
                ORDER BY p.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tenantId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Create payment
     * @param array $data
     * @return int Payment ID
     */
    public function create($data)
    {
        // Generate transaction ID
        $data['transaction_id'] = $this->generateTransactionId();
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Generate unique transaction ID
     * @return string
     */
    private function generateTransactionId()
    {
        return 'TXN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -8));
    }

    /**
     * Get payment by invoice
     * @param int $invoiceId
     * @return array|false
     */
    public function getByInvoice($invoiceId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE invoice_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        return $stmt->fetch();
    }
}
