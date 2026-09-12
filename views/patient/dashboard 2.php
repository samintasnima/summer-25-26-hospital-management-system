<?php
$page_title = 'Patient Dashboard';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-tachometer-alt"></i> My Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-calendar"></i></div>
        <div class="stat-info"><h4 data-stat="upcoming"><?= (int)$stats['upcoming'] ?></h4><p>Upcoming Appointments</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-pills"></i></div>
        <div class="stat-info"><h4 data-stat="active_rx"><?= (int)$stats['active_rx'] ?></h4><p>Active Prescriptions</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-info"><h4 data-stat="unpaid"><?= (int)$stats['unpaid'] ?></h4><p>Unpaid Invoices</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-file-medical"></i></div>
        <div class="stat-info"><h4 data-stat="history_records"><?= (int)$stats['history_records'] ?></h4><p>Medical Records</p></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Upcoming Appointments</h3>
        <a href="index.php?page=patient_doctors" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Book New</a>
    </div>
    <?php 
    $list = array_merge($upcoming ?? [], $confirmed ?? []);
    if (empty($list)): ?>
        <div class="empty-state"><i class="fas fa-calendar"></i><p>No upcoming appointments. <a href="index.php?page=patient_doctors">Book one</a></p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Date</th><th>Time</th><th>Doctor</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($list as $a): ?>
                    <tr>
                        <td><?= e($a['appointment_date']) ?></td>
                        <td><?= e(substr($a['appointment_time'],0,5)) ?></td>
                        <td><?= e($a['doctor_name']) ?></td>
                        <td><span class="badge badge-<?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  if (window.refreshDashboardStats) window.refreshDashboardStats();
});
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
