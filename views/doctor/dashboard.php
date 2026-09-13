<?php
$page_title = 'Doctor Dashboard';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Doctor Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info"><h4 data-stat="today"><?= (int)$stats['today'] ?></h4><p>Today's Appointments</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-info"><h4 data-stat="pending"><?= (int)$stats['pending'] ?></h4><p>Pending</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info"><h4 data-stat="completed"><?= (int)$stats['completed'] ?></h4><p>Completed</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-prescription"></i></div>
        <div class="stat-info"><h4 data-stat="active_prescriptions"><?= (int)$stats['active_prescriptions'] ?></h4><p>Active Prescriptions</p></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Today's Appointments</h3>
        <a href="index.php?page=doctor_appointments" class="btn btn-sm btn-outline">View All</a>
    </div>
    <?php if (empty($today_appts)): ?>
        <div class="empty-state"><i class="fas fa-calendar"></i><p>No appointments today.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Time</th><th>Patient</th><th>Phone</th><th>Reason</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($today_appts as $a): ?>
                    <tr>
                        <td><?= e(substr($a['appointment_time'],0,5)) ?></td>
                        <td><?= e($a['patient_name']) ?></td>
                        <td><?= e($a['patient_phone'] ?? '-') ?></td>
                        <td><?= e($a['reason'] ?? '-') ?></td>
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
