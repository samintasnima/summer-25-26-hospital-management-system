<?php
$page_title = 'My Appointments';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-calendar-check"></i> Appointments</h1>

<div class="card">
    <form class="search-bar" method="GET">
        <input type="hidden" name="page" value="doctor_appointments">
        <select name="status">
            <option value="">All Status</option>
            <?php foreach (['pending','confirmed','completed','cancelled','no-show'] as $s): ?>
                <option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date" value="<?= e($_GET['date']??'') ?>">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i></button>
    </form>
    
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Phone</th><th>Reason</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($appointments)): ?>
                <tr><td colspan="7" class="empty-state">No appointments found.</td></tr>
            <?php else: foreach ($appointments as $a): ?>
                <tr>
                    <td><?= e($a['appointment_date']) ?></td>
                    <td><?= e(substr($a['appointment_time'],0,5)) ?></td>
                    <td><?= e($a['patient_name']) ?></td>
                    <td><?= e($a['patient_phone']??'-') ?></td>
                    <td><?= e($a['reason']??'-') ?></td>
                    <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                    <td class="actions">
                        <?php if (in_array($a['status'], ['pending','confirmed'])): ?>
                        <form method="POST" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="form_action" value="update_status">
                            <input type="hidden" name="appointment_id" value="<?= (int)$a['id'] ?>">
                            <select name="status" class="form-control" style="width:auto;display:inline-block;padding:2px 6px;font-size:0.8rem;">
                                <option value="confirmed">Confirm</option>
                                <option value="completed">Complete</option>
                                <option value="cancelled">Cancel</option>
                                <option value="no-show">No-show</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
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
