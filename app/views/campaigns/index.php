<!-- FILE: /app/views/campaigns/index.php -->
<?php
$pageTitle = 'Campaigns';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="page-actions">
        <a href="/campaigns/create" class="btn btn-primary">Create Campaign</a>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Recipients</th>
                <th>Sent</th>
                <th>Failed</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($campaigns)): ?>
                <tr>
                    <td colspan="7" class="empty-state">No campaigns yet. Create one to get started.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($campaigns as $campaign): ?>
                    <tr>
                        <td><?php echo View::escape($campaign['name']); ?></td>
                        <td><span class="badge badge-<?php echo $campaign['status']; ?>"><?php echo View::escape($campaign['status']); ?></span></td>
                        <td><?php echo View::formatNumber($campaign['total_recipients'], 0); ?></td>
                        <td><?php echo View::formatNumber($campaign['sent_count'], 0); ?></td>
                        <td><?php echo View::formatNumber($campaign['failed_count'], 0); ?></td>
                        <td><?php echo View::formatDate($campaign['created_at'], 'Y-m-d'); ?></td>
                        <td class="actions">
                            <a href="/campaigns/<?php echo $campaign['id']; ?>" class="btn btn-sm btn-secondary">View</a>
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
