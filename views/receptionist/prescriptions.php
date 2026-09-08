<?php
$page_title = 'My Prescriptions';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-pills"></i> My Prescriptions</h1>

<div class="card">
    <form class="search-bar" method="GET">
        <input type="hidden" name="page" value="patient_prescriptions">
        <input type="text" name="q" placeholder="Search..." value="<?= e($_GET['q']??'') ?>">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
    </form>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Doctor</th><th>Diagnosis</th><th>Medicines</th><th>Instructions</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (empty($prescriptions)): ?>
                <tr><td colspan="6" class="empty-state">No prescriptions yet.</td></tr>
            <?php else: foreach ($prescriptions as $pr): ?>
                <tr>
                    <td><?= e($pr['prescribed_date']) ?></td>
                    <td><?= e($pr['doctor_name']) ?></td>
                    <td><?= e($pr['diagnosis']??'-') ?></td>
                    <td><?= nl2br(e($pr['medicines'])) ?></td>
                    <td><?= e($pr['dosage_instructions']??'-') ?></td>
                    <td><span class="badge badge-<?= e($pr['status']) ?>"><?= e($pr['status']) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
