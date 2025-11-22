<?php
// FILE: /app/controllers/BillingController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Invoice.php';
require_once __DIR__ . '/../models/Payment.php';

/**
 * BillingController
 * Handles billing, invoices, and payments
 */
class BillingController extends Controller
{
    /**
     * Show billing overview
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'platform_admin']);

        $tenantId = $this->currentTenantId();
        $invoiceModel = new Invoice();
        $paymentModel = new Payment();

        // Pagination
        $page = (int)$this->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $invoices = $invoiceModel->getByTenant($tenantId, $perPage, $offset);
        $payments = $paymentModel->getByTenant($tenantId, $perPage, $offset);
        $total = $invoiceModel->countByTenant($tenantId);

        $this->render('billing/index', [
            'invoices' => $invoices,
            'payments' => $payments,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ]);
    }

    /**
     * Simulate payment
     */
    public function simulatePayment()
    {
        $this->requireAuth();
        $this->requireRole('tenant_admin');

        if (!CSRF::validateToken($this->post('csrf_token'))) {
            $this->json(['error' => 'Invalid request'], 403);
        }

        $invoiceId = (int)$this->post('invoice_id');
        $tenantId = $this->currentTenantId();

        $invoiceModel = new Invoice();
        $paymentModel = new Payment();

        $invoice = $invoiceModel->findById($invoiceId);

        // Verify tenant ownership
        if (!$invoice || $invoice['tenant_id'] != $tenantId) {
            $this->json(['error' => 'Unauthorized'], 403);
        }

        // Create payment record
        $paymentModel->create([
            'tenant_id' => $tenantId,
            'invoice_id' => $invoiceId,
            'amount' => $invoice['amount'],
            'currency' => $invoice['currency'],
            'payment_method' => 'simulated',
            'status' => 'completed'
        ]);

        // Update invoice status
        $invoiceModel->updateStatus($invoiceId, 'paid');

        Session::flash('success', 'Payment processed successfully');
        $this->redirect('/billing');
    }
}
