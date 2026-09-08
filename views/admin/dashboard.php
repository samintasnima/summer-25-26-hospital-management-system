<?php
$page_title = 'Admin Dashboard';
require __DIR__ . '/../partials/header.php';
require __DIR__ . '/../partials/navbar.php';
?>
<h1 class="page-title"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-user-md"></i></div>
        <div class="stat-info"><h4 data-stat="doctors"><?= (int)$stats['doctors'] ?></h4><p>Doctors</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-user-injured"></i></div>
        <div class="stat-info"><h4 data-stat="patients"><?= (int)$stats['patients'] ?></h4><p>Patients</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-headset"></i></div>
        <div class="stat-info"><h4 data-stat="receptionists"><?= (int)$stats['receptionists'] ?></h4><p>Receptionists</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info"><h4 data-stat="today_appointments"><?= (int)$stats['today_appointments'] ?></h4><p>Today's Appointments</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="fas fa-clock"></i></div>
        <div class="stat-info"><h4 data-stat="pending_appointments"><?= (int)$stats['pending_appointments'] ?></h4><p>Pending</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-coins"></i></div>
        <div class="stat-info"><h4 data-stat="today_revenue"><?= format_money($stats['today_revenue']) ?></h4><p>Today's Revenue</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info"><h4 data-stat="month_revenue"><?= format_money($stats['month_revenue']) ?></h4><p>This Month</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-file-invoice"></i></div>
        <div class="stat-info"><h4 data-stat="unpaid_invoices"><?= (int)$stats['unpaid_invoices'] ?></h4><p>Unpaid Invoices</p></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3><i class="fas fa-bullhorn"></i> Active Notices</h3></div>
    <?php if (empty($notices)): ?>
        <div class="empty-state"><i class="fas fa-inbox"></i><p>No active notices.</p></div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Title</th><th>Priority</th><th>Target</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($notices as $n): ?>
                    <tr>
                        <td><?= e($n['title']) ?></td>
                        <td><span class="badge badge-<?= e($n['priority']) ?>"><?= e($n['priority']) ?></span></td>
                        <td><?= e($n['target_role']) ?></td>
                        <td><?= e(date('d M Y', strtotime($n['created_at']))) ?></td>
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
