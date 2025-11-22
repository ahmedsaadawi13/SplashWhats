<!-- FILE: /app/views/subscriptions/index.php -->
<?php
$pageTitle = 'Subscription';
require __DIR__ . '/../layouts/header.php';
?>

<div class="subscription-container">
    <?php if ($subscription): ?>
        <div class="current-plan">
            <h2>Current Plan: <?php echo View::escape($subscription['plan_name']); ?></h2>
            <p><strong>Status:</strong> <span class="badge badge-<?php echo $subscription['status']; ?>"><?php echo View::escape($subscription['status']); ?></span></p>
            <p><strong>Starts:</strong> <?php echo View::formatDate($subscription['starts_at'], 'Y-m-d'); ?></p>
            <p><strong>Ends:</strong> <?php echo View::formatDate($subscription['ends_at'], 'Y-m-d'); ?></p>
        </div>

        <?php if ($usage): ?>
            <div class="usage-stats">
                <h3>Current Month Usage</h3>
                <div class="usage-grid">
                    <div class="usage-item">
                        <div class="usage-label">Contacts</div>
                        <div class="usage-value"><?php echo $usage['contacts_count'] ?? 0; ?> / <?php echo $subscription['plan_limits']['max_contacts'] ?? 'Unlimited'; ?></div>
                    </div>
                    <div class="usage-item">
                        <div class="usage-label">Messages</div>
                        <div class="usage-value"><?php echo $usage['messages_sent'] ?? 0; ?> / <?php echo $subscription['plan_limits']['max_messages_per_month'] ?? 'Unlimited'; ?></div>
                    </div>
                    <div class="usage-item">
                        <div class="usage-label">Campaigns</div>
                        <div class="usage-value"><?php echo $usage['campaigns_count'] ?? 0; ?> / <?php echo $subscription['plan_limits']['max_campaigns'] ?? 'Unlimited'; ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="available-plans">
        <h2>Available Plans</h2>
        <div class="plans-grid">
            <?php foreach ($plans as $plan): ?>
                <div class="plan-card">
                    <h3><?php echo View::escape($plan['name']); ?></h3>
                    <div class="plan-price">
                        $<?php echo View::formatNumber($plan['price']); ?> / <?php echo View::escape($plan['billing_period']); ?>
                    </div>
                    <div class="plan-features">
                        <?php
                        $limits = json_decode($plan['limits'], true);
                        if ($limits):
                        ?>
                            <ul>
                                <li>Max Contacts: <?php echo $limits['max_contacts'] ?? 'Unlimited'; ?></li>
                                <li>Messages/Month: <?php echo $limits['max_messages_per_month'] ?? 'Unlimited'; ?></li>
                                <li>Campaigns: <?php echo $limits['max_campaigns'] ?? 'Unlimited'; ?></li>
                                <li>Agents: <?php echo $limits['max_agents'] ?? 'Unlimited'; ?></li>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <?php if (!$subscription || $subscription['plan_id'] != $plan['id']): ?>
                        <form method="POST" action="/subscriptions/change-plan">
                            <?php echo CSRF::field(); ?>
                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                            <button type="submit" class="btn btn-primary">Select Plan</button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>Current Plan</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
