<!-- FILE: /app/views/billing/index.php -->
<?php
$pageTitle = 'Billing';
require __DIR__ . '/../layouts/header.php';
?>

<div class="billing-container">
    <h2>Invoices</h2>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Period</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">No invoices yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo View::escape($invoice['invoice_number']); ?></td>
                            <td><?php echo View::escape($invoice['currency']); ?> <?php echo View::formatNumber($invoice['amount']); ?></td>
                            <td><span class="badge badge-<?php echo $invoice['status']; ?>"><?php echo View::escape($invoice['status']); ?></span></td>
                            <td><?php echo View::formatDate($invoice['period_start'], 'Y-m-d'); ?> - <?php echo View::formatDate($invoice['period_end'], 'Y-m-d'); ?></td>
                            <td><?php echo View::formatDate($invoice['due_date'], 'Y-m-d'); ?></td>
                            <td class="actions">
                                <?php if ($invoice['status'] === 'pending'): ?>
                                    <form method="POST" action="/billing/simulate-payment" style="display: inline;">
                                        <?php echo CSRF::field(); ?>
                                        <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-primary">Pay Now (Simulate)</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h2>Payment History</h2>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Invoice #</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">No payments yet</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?php echo View::escape($payment['transaction_id']); ?></td>
                            <td><?php echo View::escape($payment['invoice_number'] ?? 'N/A'); ?></td>
                            <td><?php echo View::escape($payment['currency']); ?> <?php echo View::formatNumber($payment['amount']); ?></td>
                            <td><?php echo View::escape($payment['payment_method']); ?></td>
                            <td><span class="badge badge-<?php echo $payment['status']; ?>"><?php echo View::escape($payment['status']); ?></span></td>
                            <td><?php echo View::formatDate($payment['created_at'], 'Y-m-d H:i'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
