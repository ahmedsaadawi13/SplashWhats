<!-- FILE: /app/views/contacts/import.php -->
<?php
$pageTitle = 'Import Contacts';
require __DIR__ . '/../layouts/header.php';
?>

<div class="form-container">
    <div class="import-instructions">
        <h3>CSV Import Instructions</h3>
        <p>Upload a CSV file with the following columns:</p>
        <ul>
            <li>Column 1: Name (required)</li>
            <li>Column 2: Phone (required)</li>
            <li>Column 3: Email (optional)</li>
            <li>Column 4: Country (optional)</li>
        </ul>
        <p><strong>Note:</strong> First row should be headers and will be skipped.</p>
    </div>

    <form method="POST" action="/contacts/import" enctype="multipart/form-data" class="standard-form">
        <?php echo CSRF::field(); ?>

        <div class="form-group">
            <label for="csv_file">CSV File</label>
            <input type="file" id="csv_file" name="csv_file" accept=".csv" required class="form-control">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Import Contacts</button>
            <a href="/contacts" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
