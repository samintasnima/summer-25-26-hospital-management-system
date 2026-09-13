<?php
$page_title = 'My Patients';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-user-injured"></i> My Patients</h1>

<div class="card">
    <form class="search-bar" method="GET">
        <input type="hidden" name="page" value="doctor_patients">
        <input type="text" name="q" id="tableSearch" placeholder="Search patient..." value="<?= e($_GET['q']??'') ?>">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
    </form>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Gender</th><th>Blood</th><th>DOB</th></tr></thead>
            <tbody>
            <?php if (empty($patients)): ?>
                <tr><td colspan="6" class="empty-state">No patients yet. They appear after appointments.</td></tr>
            <?php else: foreach ($patients as $p): ?>
                <tr>
                    <td><?= e($p['full_name']) ?></td>
                    <td><?= e($p['email']) ?></td>
                    <td><?= e($p['phone']??'-') ?></td>
                    <td><?= e($p['gender']??'-') ?></td>
                    <td><?= e($p['blood_group']??'-') ?></td>
                    <td><?= e($p['date_of_birth']??'-') ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
