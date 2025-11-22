<!-- FILE: /app/views/contacts/index.php -->
<?php
$pageTitle = 'Contacts';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="page-actions">
        <a href="/contacts/create" class="btn btn-primary">Add Contact</a>
        <a href="/contacts/import" class="btn btn-secondary">Import CSV</a>
    </div>
</div>

<div class="filters-bar">
    <form method="GET" action="/contacts" class="filters-form">
        <input type="text" name="search" placeholder="Search contacts..." value="<?php echo View::escape($filters['search'] ?? ''); ?>" class="form-control">

        <select name="status" class="form-control">
            <option value="">All Statuses</option>
            <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="blocked" <?php echo ($filters['status'] ?? '') === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
        </select>

        <select name="tag_id" class="form-control">
            <option value="">All Tags</option>
            <?php foreach ($tags as $tag): ?>
                <option value="<?php echo $tag['id']; ?>" <?php echo ($filters['tag_id'] ?? '') == $tag['id'] ? 'selected' : ''; ?>>
                    <?php echo View::escape($tag['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="/contacts" class="btn btn-link">Clear</a>
    </form>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Tags</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($contacts)): ?>
                <tr>
                    <td colspan="7" class="empty-state">No contacts found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?php echo View::escape($contact['name']); ?></td>
                        <td><?php echo View::escape($contact['phone']); ?></td>
                        <td><?php echo View::escape($contact['email']); ?></td>
                        <td>
                            <?php if (!empty($contact['tags'])): ?>
                                <span class="tags-inline"><?php echo View::escape($contact['tags']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $contact['status']; ?>">
                                <?php echo View::escape($contact['status']); ?>
                            </span>
                        </td>
                        <td><?php echo View::formatDate($contact['created_at'], 'Y-m-d'); ?></td>
                        <td class="actions">
                            <a href="/contacts/edit/<?php echo $contact['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
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
