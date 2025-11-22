<!-- FILE: /app/views/conversations/show.php -->
<?php
$pageTitle = 'Conversation';
require __DIR__ . '/../layouts/header.php';
?>

<div class="conversation-view">
    <div class="conversation-sidebar">
        <h3>Contact Info</h3>
        <p><strong>Phone:</strong> <?php echo View::escape($conversation['contact_phone'] ?? 'N/A'); ?></p>
        <p><strong>Status:</strong> <span class="badge badge-<?php echo $conversation['status']; ?>"><?php echo View::escape($conversation['status']); ?></span></p>

        <h4>Assign Agent</h4>
        <form method="POST" action="/conversations/<?php echo $conversation['id']; ?>/assign">
            <?php echo CSRF::field(); ?>
            <select name="agent_id" class="form-control">
                <option value="">Unassigned</option>
                <?php foreach ($agents as $agent): ?>
                    <option value="<?php echo $agent['id']; ?>" <?php echo ($conversation['assigned_to'] ?? '') == $agent['id'] ? 'selected' : ''; ?>>
                        <?php echo View::escape($agent['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Assign</button>
        </form>

        <h4>Update Status</h4>
        <form method="POST" action="/conversations/<?php echo $conversation['id']; ?>/status">
            <?php echo CSRF::field(); ?>
            <select name="status" class="form-control">
                <option value="open" <?php echo $conversation['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                <option value="closed" <?php echo $conversation['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
            </select>
            <button type="submit" class="btn btn-sm btn-primary">Update</button>
        </form>
    </div>

    <div class="conversation-main">
        <div class="messages-container">
            <?php if (empty($messages)): ?>
                <div class="empty-state">No messages yet</div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                    <div class="message message-<?php echo $message['direction']; ?>">
                        <div class="message-body">
                            <?php echo nl2br(View::escape($message['body'])); ?>
                        </div>
                        <div class="message-meta">
                            <?php if ($message['direction'] === 'outbound' && $message['sender_name']): ?>
                                <span class="sender"><?php echo View::escape($message['sender_name']); ?></span>
                            <?php endif; ?>
                            <span class="timestamp"><?php echo View::formatDate($message['created_at'], 'Y-m-d H:i'); ?></span>
                            <span class="status"><?php echo View::escape($message['status']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="message-composer">
            <form method="POST" action="/conversations/<?php echo $conversation['id']; ?>/send">
                <?php echo CSRF::field(); ?>
                <textarea name="body" rows="3" placeholder="Type your message..." required class="form-control"></textarea>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
