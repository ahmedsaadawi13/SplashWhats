<!-- FILE: /app/views/templates/create.php -->
<?php
$pageTitle = 'Create Template';
require __DIR__ . '/../layouts/header.php';
?>

<div class="form-container">
    <form method="POST" action="/templates/store" class="standard-form">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="name">Template Name *</label>
            <input type="text" id="name" name="name" required class="form-control">
        </div>

        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" required class="form-control">
                <option value="marketing">Marketing</option>
                <option value="support">Support</option>
                <option value="sales">Sales</option>
                <option value="notification">Notification</option>
            </select>
        </div>

        <div class="form-group">
            <label for="language">Language</label>
            <input type="text" id="language" name="language" value="en" class="form-control">
        </div>

        <div class="form-group">
            <label for="body">Message Body *</label>
            <textarea id="body" name="body" rows="6" required class="form-control" placeholder="Use {{name}} for contact name and other placeholders"></textarea>
            <small>Available placeholders: {{name}}, {{phone}}, {{email}}</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Template</button>
            <a href="/templates" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
