<?php
$page_title = 'Manage Notices';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-bullhorn"></i> Notices & Announcements</h1>

<div class="form-panel">
    <h3><?= $edit ? 'Edit Notice' : 'Create Notice' ?></h3>
    <form method="POST" action="index.php?page=admin_notices" data-validate>
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if ($edit): ?><input type="hidden" name="notice_id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        
        <div class="form-group">
            <label>Title *</label>
            <input type="text" name="title" class="form-control" required value="<?= e($edit['title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Content *</label>
            <textarea name="content" class="form-control" rows="4" required><?= e($edit['content'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Target Role</label>
                <select name="target_role" class="form-control">
                    <?php foreach (['all','admin','doctor','patient','receptionist'] as $r): ?>
                        <option value="<?= $r ?>" <?= ($edit['target_role'] ?? 'all') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Priority</label>
                <select name="priority" class="form-control">
                    <?php foreach (['low','medium','high'] as $p): ?>
                        <option value="<?= $p ?>" <?= ($edit['priority'] ?? 'medium') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Expires At</label>
                <input type="date" name="expires_at" class="form-control" value="<?= e($edit['expires_at'] ?? '') ?>">
            </div>
            <?php if ($edit): ?>
            <div class="form-group">
                <label><input type="checkbox" name="is_active" value="1" <?= !empty($edit['is_active']) ? 'checked' : '' ?>> Active</label>
            </div>
            <?php endif; ?>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            <?php if ($edit): ?><a href="index.php?page=admin_notices" class="btn btn-secondary">Cancel</a><?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3>All Notices</h3>
        <form class="search-bar" method="GET">
            <input type="hidden" name="page" value="admin_notices">
            <input type="text" name="q" placeholder="Search..." value="<?= e($_GET['q'] ?? '') ?>">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Title</th><th>Target</th><th>Priority</th><th>Status</th><th>Author</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($notices)): ?>
                <tr><td colspan="7" class="empty-state">No notices.</td></tr>
            <?php else: foreach ($notices as $n): ?>
                <tr>
                    <td><?= e($n['title']) ?></td>
                    <td><?= e($n['target_role']) ?></td>
                    <td><span class="badge badge-<?= e($n['priority']) ?>"><?= e($n['priority']) ?></span></td>
                    <td><span class="badge badge-<?= $n['is_active'] ? 'active' : 'inactive' ?>"><?= $n['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td><?= e($n['author'] ?? '-') ?></td>
                    <td><?= e(date('d M Y', strtotime($n['created_at']))) ?></td>
                    <td class="actions">
                        <a href="index.php?page=admin_notices&id=<?= (int)$n['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                        <form method="POST" style="display:inline;" data-confirm="Delete this notice?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="notice_id" value="<?= (int)$n['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
