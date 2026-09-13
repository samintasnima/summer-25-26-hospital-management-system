<?php
$page_title = 'My Appointments';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-calendar"></i> My Appointments</h1>

<div class="card">
    <form class="search-bar" method="GET">
        <input type="hidden" name="page" value="patient_appointments">
        <select name="status">
            <option value="">All Status</option>
            <?php foreach (['pending','confirmed','completed','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i></button>
        <a href="index.php?page=patient_doctors" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Book New</a>
    </form>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Specialization</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($appointments)): ?>
                <tr><td colspan="7" class="empty-state">No appointments found.</td></tr>
            <?php else: foreach ($appointments as $a): ?>
                <tr>
                    <td><?= e($a['appointment_date']) ?></td>
                    <td><?= e(substr($a['appointment_time'],0,5)) ?></td>
                    <td><?= e($a['doctor_name']) ?></td>
                    <td><?= e($a['specialization']??'-') ?></td>
                    <td><?= e($a['reason']??'-') ?></td>
                    <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                    <td class="actions">
                        <?php if (in_array($a['status'], ['pending','confirmed'])): ?>
                        <form method="POST" style="display:inline;" data-confirm="Cancel this appointment?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="cancel">
                            <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
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
