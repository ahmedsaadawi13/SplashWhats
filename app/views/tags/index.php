<!-- FILE: /app/views/tags/index.php -->
<?php
$pageTitle = 'Tags';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="page-actions">
        <button onclick="showCreateTagModal()" class="btn btn-primary">Create Tag</button>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Color</th>
                <th>Contacts</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tags)): ?>
                <tr>
                    <td colspan="5" class="empty-state">No tags yet. Create one to get started.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($tags as $tag): ?>
                    <tr>
                        <td>
                            <span class="tag-badge" style="background-color: <?php echo View::escape($tag['color']); ?>">
                                <?php echo View::escape($tag['name']); ?>
                            </span>
                        </td>
                        <td><?php echo View::escape($tag['color']); ?></td>
                        <td><?php echo $tag['contact_count']; ?></td>
                        <td><?php echo View::formatDate($tag['created_at'], 'Y-m-d'); ?></td>
                        <td class="actions">
                            <form method="POST" action="/tags/delete/<?php echo $tag['id']; ?>" style="display: inline;" onsubmit="return confirm('Delete this tag?');">
                                <?php echo CSRF::field(); ?>
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Create Tag Modal -->
<div id="createTagModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Create New Tag</h3>
        <form method="POST" action="/tags/store">
            <?php echo CSRF::field(); ?>
            <div class="form-group">
                <label for="name">Tag Name</label>
                <input type="text" id="name" name="name" required class="form-control">
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="color" id="color" name="color" value="#3B82F6" class="form-control">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create</button>
                <button type="button" onclick="hideCreateTagModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateTagModal() {
    document.getElementById('createTagModal').style.display = 'flex';
}
function hideCreateTagModal() {
    document.getElementById('createTagModal').style.display = 'none';
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
