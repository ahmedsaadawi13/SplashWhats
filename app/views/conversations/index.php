<!-- FILE: /app/views/conversations/index.php -->
<?php
$pageTitle = 'Inbox';
require __DIR__ . '/../layouts/header.php';
?>

<div class="filters-bar">
    <form method="GET" action="/conversations" class="filters-form">
        <select name="status" class="form-control">
            <option value="">All Statuses</option>
            <option value="open" <?php echo ($filters['status'] ?? '') === 'open' ? 'selected' : ''; ?>>Open</option>
            <option value="closed" <?php echo ($filters['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
        </select>

        <select name="assigned_to" class="form-control">
            <option value="">All Agents</option>
            <?php foreach ($agents as $agent): ?>
                <option value="<?php echo $agent['id']; ?>" <?php echo ($filters['assigned_to'] ?? '') == $agent['id'] ? 'selected' : ''; ?>>
                    <?php echo View::escape($agent['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="checkbox-label">
            <input type="checkbox" name="unread" value="1" <?php echo !empty($filters['unread']) ? 'checked' : ''; ?>>
            Unread only
        </label>

        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="/conversations" class="btn btn-link">Clear</a>
    </form>
</div>

<div class="conversations-list">
    <?php if (empty($conversations)): ?>
        <div class="empty-state">No conversations found</div>
    <?php else: ?>
        <?php foreach ($conversations as $conv): ?>
            <div class="conversation-item">
                <a href="/conversations/<?php echo $conv['id']; ?>" class="conversation-link">
                    <div class="conversation-header">
                        <strong><?php echo View::escape($conv['contact_name']); ?></strong>
                        <span class="conversation-phone"><?php echo View::escape($conv['contact_phone']); ?></span>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="conversation-preview">
                        <?php echo View::escape(substr($conv['last_message'] ?? 'No messages yet', 0, 100)); ?>
                    </div>
                    <div class="conversation-meta">
                        <span class="badge badge-<?php echo $conv['status']; ?>"><?php echo View::escape($conv['status']); ?></span>
                        <?php if ($conv['assigned_agent_name']): ?>
                            <span class="agent-name">Assigned to: <?php echo View::escape($conv['assigned_agent_name']); ?></span>
                        <?php endif; ?>
                        <span class="timestamp"><?php echo View::formatDate($conv['last_message_at'], 'Y-m-d H:i'); ?></span>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($total > $perPage): ?>
    <div class="pagination">
        <?php
        $totalPages = ceil($total / $perPage);
        for ($i = 1; $i <= $totalPages; $i++):
        ?>
            <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $page == $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
