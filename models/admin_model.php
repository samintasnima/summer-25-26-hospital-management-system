<?php
/**
 * Admin Model - revenue, stats, etc.
 */
require_once __DIR__ . '/../config/database.php';

function admin_dashboard_stats() {
    $stats = [];
    
    $r = db_query("SELECT COUNT(*) AS c FROM users WHERE role = 'doctor' AND status='active'");
    $stats['doctors'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM users WHERE role = 'patient' AND status='active'");
    $stats['patients'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM users WHERE role = 'receptionist' AND status='active'");
    $stats['receptionists'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM appointments WHERE appointment_date = CURDATE()");
    $stats['today_appointments'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM appointments WHERE status = 'pending'");
    $stats['pending_appointments'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE DATE(payment_date) = CURDATE()");
    $stats['today_revenue'] = (float)mysqli_fetch_assoc($r)['total'];
    
    $r = db_query("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE MONTH(payment_date) = MONTH(CURDATE()) AND YEAR(payment_date) = YEAR(CURDATE())");
    $stats['month_revenue'] = (float)mysqli_fetch_assoc($r)['total'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM invoices WHERE status = 'unpaid'");
    $stats['unpaid_invoices'] = (int)mysqli_fetch_assoc($r)['c'];
    
    return $stats;
}

function admin_revenue_report($from = null, $to = null) {
    $where = "1=1";
    if ($from) {
        $from = db_escape($from);
        $where .= " AND DATE(p.payment_date) >= '$from'";
    }
    if ($to) {
        $to = db_escape($to);
        $where .= " AND DATE(p.payment_date) <= '$to'";
    }
    
    $sql = "SELECT DATE(p.payment_date) AS pay_date, 
                   COUNT(*) AS transactions,
                   SUM(p.amount) AS total_amount,
                   p.payment_method
            FROM payments p
            WHERE $where
            GROUP BY DATE(p.payment_date), p.payment_method
            ORDER BY pay_date DESC";
    
    $result = db_query($sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function admin_revenue_summary($from = null, $to = null) {
    $where = "1=1";
    if ($from) {
        $from = db_escape($from);
        $where .= " AND DATE(payment_date) >= '$from'";
    }
    if ($to) {
        $to = db_escape($to);
        $where .= " AND DATE(payment_date) <= '$to'";
    }
    $r = db_query("SELECT COALESCE(SUM(amount),0) AS total, COUNT(*) AS cnt FROM payments WHERE $where");
    return mysqli_fetch_assoc($r);
}
