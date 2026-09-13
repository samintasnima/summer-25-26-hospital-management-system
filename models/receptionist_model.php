<?php
/**
 * Receptionist Model - mostly wrappers + dashboard stats
 */
require_once __DIR__ . '/../config/database.php';

function receptionist_dashboard_stats() {
    $stats = [];
    
    $r = db_query("SELECT COUNT(*) AS c FROM appointments WHERE appointment_date = CURDATE()");
    $stats['today_appointments'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM appointments WHERE status = 'pending'");
    $stats['pending'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM queue WHERE queue_date = CURDATE() AND status = 'waiting'");
    $stats['waiting_queue'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM invoices WHERE status = 'unpaid'");
    $stats['unpaid_invoices'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE DATE(payment_date) = CURDATE()");
    $stats['today_collection'] = (float)mysqli_fetch_assoc($r)['total'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM users WHERE role = 'patient' AND DATE(created_at) = CURDATE()");
    $stats['new_patients_today'] = (int)mysqli_fetch_assoc($r)['c'];
    
    return $stats;
}
