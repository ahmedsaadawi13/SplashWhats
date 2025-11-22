<!-- FILE: /app/views/campaigns/show.php -->
<?php
$pageTitle = 'Campaign Details';
require __DIR__ . '/../layouts/header.php';
?>

<div class="campaign-details">
    <div class="campaign-header">
        <h2><?php echo View::escape($campaign['name']); ?></h2>
        <span class="badge badge-<?php echo $campaign['status']; ?>"><?php echo View::escape($campaign['status']); ?></span>
    </div>

    <div class="campaign-stats">
        <div class="stat-item">
            <div class="stat-label">Total Recipients</div>
            <div class="stat-value"><?php echo View::formatNumber($campaign['total_recipients'], 0); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Sent</div>
            <div class="stat-value"><?php echo View::formatNumber($campaign['sent_count'], 0); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Delivered</div>
            <div class="stat-value"><?php echo View::formatNumber($campaign['delivered_count'] ?? 0, 0); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Failed</div>
            <div class="stat-value"><?php echo View::formatNumber($campaign['failed_count'], 0); ?></div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Pending</div>
            <div class="stat-value"><?php echo View::formatNumber($campaign['pending_count'] ?? 0, 0); ?></div>
        </div>
    </div>

    <?php if ($campaign['status'] === 'draft'): ?>
        <div class="campaign-actions">
            <form method="POST" action="/campaigns/<?php echo $campaign['id']; ?>/start" onsubmit="return confirm('Start this campaign now?');">
                <?php echo CSRF::field(); ?>
                <button type="submit" class="btn btn-primary">Start Campaign</button>
            </form>
        </div>
    <?php endif; ?>

    <h3>Campaign Messages</h3>
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Sent At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="4" class="empty-state">No messages</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <tr>
                            <td><?php echo View::escape($message['contact_name']); ?></td>
                            <td><?php echo View::escape($message['contact_phone']); ?></td>
                            <td><span class="badge badge-<?php echo $message['status']; ?>"><?php echo View::escape($message['status']); ?></span></td>
                            <td><?php echo $message['sent_at'] ? View::formatDate($message['sent_at'], 'Y-m-d H:i') : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
