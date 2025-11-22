<!-- FILE: /app/views/contacts/edit.php -->
<?php
$pageTitle = 'Edit Contact';
require __DIR__ . '/../layouts/header.php';
?>

<div class="form-container">
    <form method="POST" action="/contacts/update/<?php echo $contact['id']; ?>" class="standard-form">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" value="<?php echo View::escape($contact['name']); ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="phone">Phone *</label>
            <input type="text" id="phone" name="phone" value="<?php echo View::escape($contact['phone']); ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo View::escape($contact['email']); ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="country">Country</label>
            <input type="text" id="country" name="country" value="<?php echo View::escape($contact['country']); ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="timezone">Timezone</label>
            <input type="text" id="timezone" name="timezone" value="<?php echo View::escape($contact['timezone']); ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="active" <?php echo $contact['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="blocked" <?php echo $contact['status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="4" class="form-control"><?php echo View::escape($contact['notes']); ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Contact</button>
            <a href="/contacts" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
