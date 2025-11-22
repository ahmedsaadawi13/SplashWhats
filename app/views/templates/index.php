<!-- FILE: /app/views/templates/index.php -->
<?php
$pageTitle = 'Templates';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="page-actions">
        <a href="/templates/create" class="btn btn-primary">Create Template</a>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Language</th>
                <th>Preview</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($templates)): ?>
                <tr>
                    <td colspan="6" class="empty-state">No templates yet. Create one to get started.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($templates as $template): ?>
                    <tr>
                        <td><?php echo View::escape($template['name']); ?></td>
                        <td><span class="badge"><?php echo View::escape($template['category']); ?></span></td>
                        <td><?php echo View::escape($template['language']); ?></td>
                        <td class="template-preview"><?php echo View::escape(substr($template['body'], 0, 50)) . '...'; ?></td>
                        <td><?php echo View::formatDate($template['created_at'], 'Y-m-d'); ?></td>
                        <td class="actions">
                            <a href="/templates/edit/<?php echo $template['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <form method="POST" action="/templates/delete/<?php echo $template['id']; ?>" style="display: inline;" onsubmit="return confirm('Delete this template?');">
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

<?php require __DIR__ . '/../layouts/footer.php'; ?>
