<?php
/**
 * Front Controller / Router - Hospital Management System
 * MVC Structure
 */
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/config/database.php';

// Simple routing
$page = $_GET['page'] ?? 'home';

// Public pages
$public_pages = ['login', 'register', 'logout'];

// Map page to controller function
$routes = [
    // Auth
    'login'    => ['auth_controller.php', 'auth_login'],
    'register' => ['auth_controller.php', 'auth_register'],
    'logout'   => ['auth_controller.php', 'auth_logout'],
    
    // Admin
    'admin_dashboard'     => ['admin_controller.php', 'admin_dashboard'],
    'admin_users'         => ['admin_controller.php', 'admin_users'],
    'admin_doctors'       => ['admin_controller.php', 'admin_doctors'],
    'admin_patients'      => ['admin_controller.php', 'admin_patients'],
    'admin_receptionists' => ['admin_controller.php', 'admin_receptionists'],
    'admin_payments'      => ['admin_controller.php', 'admin_payments'],
    'admin_revenue'       => ['admin_controller.php', 'admin_revenue'],
    'admin_notices'       => ['admin_controller.php', 'admin_notices'],
    'admin_activity_logs' => ['admin_controller.php', 'admin_activity_logs'],
    
    // Doctor
    'doctor_dashboard'        => ['doctor_controller.php', 'doctor_dashboard'],
    'doctor_appointments'     => ['doctor_controller.php', 'doctor_appointments'],
    'doctor_queue'            => ['doctor_controller.php', 'doctor_queue'],
    'doctor_patients'         => ['doctor_controller.php', 'doctor_patients'],
    'doctor_prescriptions'    => ['doctor_controller.php', 'doctor_prescriptions'],
    'doctor_medical_history'  => ['doctor_controller.php', 'doctor_medical_history'],
    'doctor_availability'     => ['doctor_controller.php', 'doctor_availability'],
    
    // Patient
    'patient_dashboard'       => ['patient_controller.php', 'patient_dashboard'],
    'patient_doctors'         => ['patient_controller.php', 'patient_doctors'],
    'patient_reviews'         => ['patient_controller.php', 'patient_reviews'],
    'patient_appointments'    => ['patient_controller.php', 'patient_appointments'],
    'patient_prescriptions'   => ['patient_controller.php', 'patient_prescriptions'],
    'patient_medical_history' => ['patient_controller.php', 'patient_medical_history'],
    'patient_payments'        => ['patient_controller.php', 'patient_payments'],
    
    // Receptionist
    'receptionist_dashboard'    => ['receptionist_controller.php', 'receptionist_dashboard'],
    'receptionist_patients'     => ['receptionist_controller.php', 'receptionist_patients'],
    'receptionist_appointments' => ['receptionist_controller.php', 'receptionist_appointments'],
    'receptionist_invoices'     => ['receptionist_controller.php', 'receptionist_invoices'],
    'receptionist_payments'     => ['receptionist_controller.php', 'receptionist_payments'],
    'receptionist_queue'        => ['receptionist_controller.php', 'receptionist_queue'],
    'receptionist_queue_alerts'=> ['receptionist_controller.php', 'receptionist_queue_alerts'],
    'receptionist_wheelchair'  => ['receptionist_controller.php', 'receptionist_wheelchair'],
    
    // AJAX
    'ajax' => ['ajax_controller.php', null], // handled inside
];

// Home redirect
if ($page === 'home') {
    if (is_logged_in()) {
        $role = current_user()['role'];
        redirect("index.php?page={$role}_dashboard");
    } else {
        redirect('index.php?page=login');
    }
}

// Security: protect non-public pages
if (!in_array($page, $public_pages) && $page !== 'ajax') {
    require_login();
}

// Route
if ($page === 'ajax') {
    require __DIR__ . '/controllers/ajax_controller.php';
    exit;
}

if (isset($routes[$page])) {
    list($controller_file, $function) = $routes[$page];
    require_once __DIR__ . '/controllers/' . $controller_file;
    if ($function && function_exists($function)) {
        call_user_func($function);
    } else {
        http_response_code(404);
        echo "Page not found.";
    }
} else {
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1><p><a href='index.php'>Go Home</a></p>";
}
