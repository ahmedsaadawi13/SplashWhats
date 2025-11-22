<!-- FILE: /app/views/dashboard/index.php -->
<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo View::formatNumber($stats['total_contacts'], 0); ?></div>
                <div class="stat-label">Total Contacts</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💬</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo View::formatNumber($stats['messages_this_month'], 0); ?></div>
                <div class="stat-label">Messages This Month</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📢</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['active_campaigns']; ?></div>
                <div class="stat-label">Active Campaigns</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🔔</div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $stats['open_conversations']; ?></div>
                <div class="stat-label">Open Conversations</div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-section">
            <h2>Messages Per Day (Last 30 Days)</h2>
            <div class="chart-container">
                <canvas id="messagesChart"></canvas>
            </div>
        </div>

        <div class="dashboard-section">
            <h2>Top Tags</h2>
            <div class="tags-list">
                <?php if (empty($topTags)): ?>
                    <p class="empty-state">No tags yet</p>
                <?php else: ?>
                    <?php foreach ($topTags as $tag): ?>
                        <div class="tag-item">
                            <span class="tag-badge" style="background-color: <?php echo View::escape($tag['color']); ?>">
                                <?php echo View::escape($tag['name']); ?>
                            </span>
                            <span class="tag-count"><?php echo $tag['contact_count']; ?> contacts</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="/contacts/create" class="btn btn-primary">Add Contact</a>
            <a href="/campaigns/create" class="btn btn-primary">Create Campaign</a>
            <a href="/templates/create" class="btn btn-primary">New Template</a>
            <a href="/conversations" class="btn btn-secondary">View Inbox</a>
        </div>
    </div>
</div>

<script>
// Simple chart rendering for messages per day
const messagesData = <?php echo json_encode($messagesPerDay); ?>;
// Chart implementation would go here (using Canvas API or library)
console.log('Messages per day:', messagesData);
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
