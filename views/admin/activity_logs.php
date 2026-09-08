<?php
$page_title = 'Activity Logs';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-history"></i> Activity Logs</h1>

<div class="card">
    <form class="search-bar" method="GET" action="index.php">
        <input type="hidden" name="page" value="admin_activity_logs">
        <input type="text" name="q" placeholder="Search action, user, details..." value="<?= e($_GET['q'] ?? '') ?>">
        <input type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>">
        <input type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Search</button>
    </form>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Time</th><th>User</th><th>Role</th><th>Action</th><th>Details</th><th>IP</th></tr>
            </thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="6" class="empty-state">No logs found.</td></tr>
            <?php else: foreach ($logs as $l): ?>
                <tr>
                    <td><?= e(date('d M Y H:i', strtotime($l['created_at']))) ?></td>
                    <td><?= e($l['full_name'] ?? $l['username'] ?? 'System') ?></td>
                    <td><?= e($l['role'] ?? '-') ?></td>
                    <td><code><?= e($l['action']) ?></code></td>
                    <td><?= e($l['details'] ?? '') ?></td>
                    <td><?= e($l['ip_address'] ?? '') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
