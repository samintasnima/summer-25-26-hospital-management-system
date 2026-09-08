<?php
$selected_role = $_GET['role'] ?? '';
$page_title = $selected_role ? ucfirst($selected_role) . ' Management' : 'Manage Users';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-users"></i> <?= e($page_title) ?></h1>

<div class="form-panel">
    <h3><?= $edit_user ? 'Edit User' : 'Create New User' ?></h3>
    <form method="POST" action="index.php?page=admin_users" data-validate>
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="<?= $edit_user ? 'update' : 'create' ?>">
        <?php if ($edit_user): ?><input type="hidden" name="user_id" value="<?= (int)$edit_user['id'] ?>"><?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" class="form-control" required value="<?= e($edit_user['username'] ?? '') ?>" <?= $edit_user ? 'readonly' : '' ?>>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required value="<?= e($edit_user['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" class="form-control" required value="<?= e($edit_user['full_name'] ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Role *</label>
                <select name="role" class="form-control" required>
                    <?php foreach (['admin','doctor','patient','receptionist'] as $r): ?>
                        <option value="<?= $r ?>" <?= ($edit_user['role'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= e($edit_user['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Specialization (Doctor)</label>
                <input type="text" name="specialization" class="form-control" value="<?= e($edit_user['specialization'] ?? '') ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password <?= $edit_user ? '(leave blank to keep)' : '*' ?></label>
                <input type="password" name="password" class="form-control" <?= $edit_user ? '' : 'required minlength="6"' ?>>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?= ($edit_user['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($edit_user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $edit_user ? 'Update' : 'Create' ?></button>
            <?php if ($edit_user): ?>
                <a href="index.php?page=admin_users" class="btn btn-secondary">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Users</h3>
        <form class="search-bar" method="GET" action="index.php">
            <input type="hidden" name="page" value="admin_users">
            <input type="text" name="q" placeholder="Search..." value="<?= e($_GET['q'] ?? '') ?>">
            <select name="role">
                <option value="">All Roles</option>
                <?php foreach (['admin','doctor','patient','receptionist'] as $r): ?>
                    <option value="<?= $r ?>" <?= ($_GET['role'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Phone</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($users)): ?>
                <tr><td colspan="8" class="empty-state">No users found.</td></tr>
            <?php else: foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['role']==='admin'?'high':($u['role']==='doctor'?'confirmed':'active') ?>"><?= e($u['role']) ?></span></td>
                    <td><?= e($u['phone'] ?? '-') ?></td>
                    <td><span class="badge badge-<?= e($u['status']) ?>"><?= e($u['status']) ?></span></td>
                    <td class="actions">
                        <a href="index.php?page=admin_users&id=<?= (int)$u['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <?php if ($u['id'] != current_user()['id'] && $u['role'] !== 'admin'): ?>
                        <form method="POST" action="index.php?page=admin_users" style="display:inline;" data-confirm="Delete this user?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
