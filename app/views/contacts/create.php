<!-- FILE: /app/views/contacts/create.php -->
<?php
$pageTitle = 'Add Contact';
require __DIR__ . '/../layouts/header.php';
?>

<div class="form-container">
    <form method="POST" action="/contacts/store" class="standard-form">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" required class="form-control">
        </div>

        <div class="form-group">
            <label for="phone">Phone *</label>
            <input type="text" id="phone" name="phone" required class="form-control" placeholder="+1234567890">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control">
        </div>

        <div class="form-group">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" class="form-control">
        </div>

        <div class="form-group">
            <label for="timezone">Timezone</label>
            <input type="text" id="timezone" name="timezone" value="UTC" class="form-control">
        </div>

        <div class="form-group">
            <label>Tags</label>
            <div class="checkbox-group">
                <?php foreach ($tags as $tag): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>">
                        <?php echo View::escape($tag['name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4" class="form-control"></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Contact</button>
            <a href="/contacts" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
