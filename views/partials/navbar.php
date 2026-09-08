<?php
$user = current_user();
$role = $user['role'] ?? '';
$current_page = $_GET['page'] ?? '';
?>
<nav class="navbar">
    <div class="nav-brand">
        <i class="fas fa-hospital"></i>
        <span>HMS</span>
    </div>
    <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
        <i class="fas fa-bars"></i>
    </button>
    <ul class="nav-links" id="navLinks">
        <?php if ($role === 'admin'): ?>
            <li><a href="index.php?page=admin_dashboard" class="<?= $current_page==='admin_dashboard'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="index.php?page=admin_doctors" class="<?= $current_page==='admin_doctors'?'active':'' ?>"><i class="fas fa-user-md"></i> Doctors</a></li>
            <li><a href="index.php?page=admin_patients" class="<?= $current_page==='admin_patients'?'active':'' ?>"><i class="fas fa-user-injured"></i> Patients</a></li>
            <li><a href="index.php?page=admin_receptionists" class="<?= $current_page==='admin_receptionists'?'active':'' ?>"><i class="fas fa-headset"></i> Receptionists</a></li>
            <li><a href="index.php?page=admin_revenue" class="<?= $current_page==='admin_revenue'?'active':'' ?>"><i class="fas fa-chart-line"></i> Revenue</a></li>
            <li><a href="index.php?page=admin_payments" class="<?= $current_page==='admin_payments'?'active':'' ?>"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="index.php?page=admin_notices" class="<?= $current_page==='admin_notices'?'active':'' ?>"><i class="fas fa-bullhorn"></i> Notices</a></li>
            <li><a href="index.php?page=admin_activity_logs" class="<?= $current_page==='admin_activity_logs'?'active':'' ?>"><i class="fas fa-history"></i> Logs</a></li>
        <?php elseif ($role === 'doctor'): ?>
            <li><a href="index.php?page=doctor_dashboard" class="<?= $current_page==='doctor_dashboard'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="index.php?page=doctor_appointments" class="<?= $current_page==='doctor_appointments'?'active':'' ?>"><i class="fas fa-calendar-check"></i> Appointments</a></li>
            <li><a href="index.php?page=doctor_queue" class="<?= $current_page==='doctor_queue'?'active':'' ?>"><i class="fas fa-list-ol"></i> Patient Queue</a></li>
            <li><a href="index.php?page=doctor_patients" class="<?= $current_page==='doctor_patients'?'active':'' ?>"><i class="fas fa-user-injured"></i> Patients</a></li>
            <li><a href="index.php?page=doctor_prescriptions" class="<?= $current_page==='doctor_prescriptions'?'active':'' ?>"><i class="fas fa-prescription"></i> Prescriptions</a></li>
            <li><a href="index.php?page=doctor_medical_history" class="<?= $current_page==='doctor_medical_history'?'active':'' ?>"><i class="fas fa-notes-medical"></i> History</a></li>
            <li><a href="index.php?page=doctor_availability" class="<?= $current_page==='doctor_availability'?'active':'' ?>"><i class="fas fa-clock"></i> Availability</a></li>
        <?php elseif ($role === 'patient'): ?>
            <li><a href="index.php?page=patient_dashboard" class="<?= $current_page==='patient_dashboard'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="index.php?page=patient_doctors" class="<?= $current_page==='patient_doctors'?'active':'' ?>"><i class="fas fa-user-md"></i> Doctors & Availability</a></li>
            <li><a href="index.php?page=patient_reviews" class="<?= $current_page==='patient_reviews'?'active':'' ?>"><i class="fas fa-star"></i> Review Doctors</a></li>
            <li><a href="index.php?page=patient_appointments" class="<?= $current_page==='patient_appointments'?'active':'' ?>"><i class="fas fa-calendar"></i> Appointments</a></li>
            <li><a href="index.php?page=patient_prescriptions" class="<?= $current_page==='patient_prescriptions'?'active':'' ?>"><i class="fas fa-pills"></i> Prescriptions</a></li>
            <li><a href="index.php?page=patient_medical_history" class="<?= $current_page==='patient_medical_history'?'active':'' ?>"><i class="fas fa-file-medical"></i> History</a></li>
            <li><a href="index.php?page=patient_payments" class="<?= $current_page==='patient_payments'?'active':'' ?>"><i class="fas fa-credit-card"></i> Payments</a></li>
        <?php elseif ($role === 'receptionist'): ?>
            <li><a href="index.php?page=receptionist_dashboard" class="<?= $current_page==='receptionist_dashboard'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="index.php?page=receptionist_patients" class="<?= $current_page==='receptionist_patients'?'active':'' ?>"><i class="fas fa-users"></i> Patients</a></li>
            <li><a href="index.php?page=receptionist_appointments" class="<?= $current_page==='receptionist_appointments'?'active':'' ?>"><i class="fas fa-calendar-plus"></i> Appointments</a></li>
            <li><a href="index.php?page=receptionist_queue" class="<?= $current_page==='receptionist_queue'?'active':'' ?>"><i class="fas fa-list-ol"></i> Queue</a></li>
            <li><a href="index.php?page=receptionist_queue_alerts" class="<?= $current_page==='receptionist_queue_alerts'?'active':'' ?>"><i class="fas fa-bell"></i> Queue Alerts</a></li>
            <li><a href="index.php?page=receptionist_wheelchair" class="<?= $current_page==='receptionist_wheelchair'?'active':'' ?>"><i class="fas fa-wheelchair"></i> Wheelchair</a></li>
            <li><a href="index.php?page=receptionist_invoices" class="<?= $current_page==='receptionist_invoices'?'active':'' ?>"><i class="fas fa-file-invoice-dollar"></i> Invoices</a></li>
            <li><a href="index.php?page=receptionist_payments" class="<?= $current_page==='receptionist_payments'?'active':'' ?>"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
        <?php endif; ?>
        
        <li class="nav-user">
            <span class="user-info"><i class="fas fa-user-circle"></i> <?= e($user['full_name'] ?? '') ?> <small>(<?= e(role_label($role)) ?>)</small></span>
            <a href="index.php?page=logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
    </ul>
</nav>
<main class="main-content">
