<?php
/**
 * Doctor Model - availability, patients under doctor, etc.
 */
require_once __DIR__ . '/../config/database.php';

function doctor_get_availability($doctor_id) {
    $doctor_id = (int)$doctor_id;
    $result = db_query("SELECT * FROM doctor_availability WHERE doctor_id = $doctor_id ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function doctor_add_availability($data) {
    $doctor_id = (int)$data['doctor_id'];
    $day = db_escape($data['day_of_week']);
    $start = db_escape($data['start_time']);
    $end = db_escape($data['end_time']);
    $max = (int)($data['max_patients'] ?? 20);
    $available = isset($data['is_available']) ? (int)$data['is_available'] : 1;
    
    $sql = "INSERT INTO doctor_availability (doctor_id, day_of_week, start_time, end_time, max_patients, is_available)
            VALUES ($doctor_id, '$day', '$start', '$end', $max, $available)";
    return db_query($sql) ? db_insert_id() : false;
}

function doctor_update_availability($id, $data) {
    $id = (int)$id;
    $sets = [];
    if (isset($data['day_of_week'])) $sets[] = "day_of_week = '" . db_escape($data['day_of_week']) . "'";
    if (isset($data['start_time'])) $sets[] = "start_time = '" . db_escape($data['start_time']) . "'";
    if (isset($data['end_time'])) $sets[] = "end_time = '" . db_escape($data['end_time']) . "'";
    if (isset($data['max_patients'])) $sets[] = "max_patients = " . (int)$data['max_patients'];
    if (isset($data['is_available'])) $sets[] = "is_available = " . (int)$data['is_available'];
    if (empty($sets)) return false;
    $sql = "UPDATE doctor_availability SET " . implode(', ', $sets) . " WHERE id = $id";
    return db_query($sql);
}

function doctor_delete_availability($id, $doctor_id) {
    $id = (int)$id;
    $doctor_id = (int)$doctor_id;
    return db_query("DELETE FROM doctor_availability WHERE id = $id AND doctor_id = $doctor_id");
}

function doctor_get_appointments($doctor_id, $status = '', $date = '') {
    $doctor_id = (int)$doctor_id;
    $where = ["a.doctor_id = $doctor_id"];
    if ($status !== '') {
        $status = db_escape($status);
        $where[] = "a.status = '$status'";
    }
    if ($date !== '') {
        $date = db_escape($date);
        $where[] = "a.appointment_date = '$date'";
    }
    $where_sql = implode(' AND ', $where);
    
    $sql = "SELECT a.*, u.full_name AS patient_name, u.phone AS patient_phone, u.email AS patient_email
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            WHERE $where_sql
            ORDER BY a.appointment_date DESC, a.appointment_time ASC";
    $result = db_query($sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function doctor_get_patients($doctor_id) {
    $doctor_id = (int)$doctor_id;
    // Patients who have had appointments with this doctor
    $sql = "SELECT DISTINCT u.id, u.full_name, u.email, u.phone, u.gender, u.date_of_birth,
                   p.blood_group, p.allergies
            FROM appointments a
            JOIN users u ON a.patient_id = u.id
            LEFT JOIN patients p ON p.user_id = u.id
            WHERE a.doctor_id = $doctor_id
            ORDER BY u.full_name";
    $result = db_query($sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function doctor_dashboard_stats($doctor_id) {
    $doctor_id = (int)$doctor_id;
    $stats = [];
    
    $r = db_query("SELECT COUNT(*) AS c FROM appointments WHERE doctor_id = $doctor_id AND appointment_date = CURDATE()");
    $stats['today'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM appointments WHERE doctor_id = $doctor_id AND status = 'pending'");
    $stats['pending'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM appointments WHERE doctor_id = $doctor_id AND status = 'completed'");
    $stats['completed'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM prescriptions WHERE doctor_id = $doctor_id AND status = 'active'");
    $stats['active_prescriptions'] = (int)mysqli_fetch_assoc($r)['c'];
    
    return $stats;
}
