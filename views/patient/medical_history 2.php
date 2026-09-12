<?php
$page_title = 'My Medical History';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-file-medical"></i> My Medical History</h1>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Type</th><th>Title</th><th>Description</th><th>Doctor</th></tr></thead>
            <tbody>
            <?php if (empty($records)): ?>
                <tr><td colspan="5" class="empty-state">No medical records found.</td></tr>
            <?php else: foreach ($records as $r): ?>
                <tr>
                    <td><?= e($r['record_date']) ?></td>
                    <td><?= e(ucfirst($r['record_type'])) ?></td>
                    <td><?= e($r['title']) ?></td>
                    <td><?= e($r['description']??'-') ?></td>
                    <td><?= e($r['doctor_name']??'-') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
