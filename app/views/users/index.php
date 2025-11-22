<!-- FILE: /app/views/users/index.php -->
<?php
$pageTitle = 'Users';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div class="page-actions">
        <button onclick="showCreateUserModal()" class="btn btn-primary">Add User</button>
    </div>
</div>

<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="empty-state">No users found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo View::escape($user['name']); ?></td>
                        <td><?php echo View::escape($user['email']); ?></td>
                        <td><span class="badge"><?php echo View::escape($user['role']); ?></span></td>
                        <td><span class="badge badge-<?php echo $user['status']; ?>"><?php echo View::escape($user['status']); ?></span></td>
                        <td><?php echo $user['last_login'] ? View::formatDate($user['last_login'], 'Y-m-d H:i') : 'Never'; ?></td>
                        <td class="actions">
                            <?php if ($user['id'] != Auth::id()): ?>
                                <form method="POST" action="/users/delete/<?php echo $user['id']; ?>" style="display: inline;" onsubmit="return confirm('Delete this user?');">
                                    <?php echo CSRF::field(); ?>
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Add New User</h3>
        <form method="POST" action="/users/store">
            <?php echo CSRF::field(); ?>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required class="form-control">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required class="form-control">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required class="form-control" minlength="6">
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control">
                    <option value="agent">Agent</option>
                    <option value="tenant_admin">Tenant Admin</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create User</button>
                <button type="button" onclick="hideCreateUserModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function showCreateUserModal() {
    document.getElementById('createUserModal').style.display = 'flex';
}
function hideCreateUserModal() {
    document.getElementById('createUserModal').style.display = 'none';
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
