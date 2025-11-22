<!-- FILE: /app/views/templates/edit.php -->
<?php
$pageTitle = 'Edit Template';
require __DIR__ . '/../layouts/header.php';
?>

<div class="form-container">
    <form method="POST" action="/templates/update/<?php echo $template['id']; ?>" class="standard-form">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="name">Template Name *</label>
            <input type="text" id="name" name="name" value="<?php echo View::escape($template['name']); ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" required class="form-control">
                <option value="marketing" <?php echo $template['category'] === 'marketing' ? 'selected' : ''; ?>>Marketing</option>
                <option value="support" <?php echo $template['category'] === 'support' ? 'selected' : ''; ?>>Support</option>
                <option value="sales" <?php echo $template['category'] === 'sales' ? 'selected' : ''; ?>>Sales</option>
                <option value="notification" <?php echo $template['category'] === 'notification' ? 'selected' : ''; ?>>Notification</option>
            </select>
        </div>

        <div class="form-group">
            <label for="language">Language</label>
            <input type="text" id="language" name="language" value="<?php echo View::escape($template['language']); ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="body">Message Body *</label>
            <textarea id="body" name="body" rows="6" required class="form-control"><?php echo View::escape($template['body']); ?></textarea>
            <small>Available placeholders: {{name}}, {{phone}}, {{email}}</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Template</button>
            <a href="/templates" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
