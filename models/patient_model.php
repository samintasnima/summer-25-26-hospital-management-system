<?php
/**
 * Patient Model
 */
require_once __DIR__ . '/../config/database.php';

function patient_get_profile($user_id) {
    $user_id = (int)$user_id;
    $sql = "SELECT u.*, p.blood_group, p.emergency_contact, p.allergies, p.id AS patient_profile_id
            FROM users u
            LEFT JOIN patients p ON p.user_id = u.id
            WHERE u.id = $user_id AND u.role = 'patient' LIMIT 1";
    $result = db_query($sql);
    return $result ? mysqli_fetch_assoc($result) : null;
}

function patient_create_profile($user_id, $data = []) {
    $user_id = (int)$user_id;
    $bg = db_escape($data['blood_group'] ?? '');
    $ec = db_escape($data['emergency_contact'] ?? '');
    $all = db_escape($data['allergies'] ?? '');
    $sql = "INSERT INTO patients (user_id, blood_group, emergency_contact, allergies)
            VALUES ($user_id, '$bg', '$ec', '$all')";
    return db_query($sql) ? db_insert_id() : false;
}

function patient_update_profile($user_id, $data) {
    $user_id = (int)$user_id;
    // Update users table
    $user_data = [];
    foreach (['full_name','phone','address','gender','date_of_birth'] as $f) {
        if (isset($data[$f])) $user_data[$f] = $data[$f];
    }
    if (!empty($user_data)) {
        require_once __DIR__ . '/user_model.php';
        user_update($user_id, $user_data);
    }
    // Update patients table
    $exists = db_query("SELECT id FROM patients WHERE user_id = $user_id");
    if (mysqli_num_rows($exists) > 0) {
        $sets = [];
        if (isset($data['blood_group'])) $sets[] = "blood_group = '" . db_escape($data['blood_group']) . "'";
        if (isset($data['emergency_contact'])) $sets[] = "emergency_contact = '" . db_escape($data['emergency_contact']) . "'";
        if (isset($data['allergies'])) $sets[] = "allergies = '" . db_escape($data['allergies']) . "'";
        if (!empty($sets)) {
            db_query("UPDATE patients SET " . implode(', ', $sets) . " WHERE user_id = $user_id");
        }
    } else {
        patient_create_profile($user_id, $data);
    }
    return true;
}

function patient_search($keyword = '', $limit = 50) {
    $where = ["u.role = 'patient'"];
    if ($keyword !== '') {
        $kw = db_escape($keyword);
        $where[] = "(u.username LIKE '%$kw%' OR u.full_name LIKE '%$kw%' OR u.email LIKE '%$kw%' OR u.phone LIKE '%$kw%')";
    }
    $where_sql = implode(' AND ', $where);
    $limit = (int)$limit;
    $sql = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.gender, u.date_of_birth, u.status,
                   p.blood_group, p.emergency_contact
            FROM users u
            LEFT JOIN patients p ON p.user_id = u.id
            WHERE $where_sql
            ORDER BY u.full_name
            LIMIT $limit";
    $result = db_query($sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function patient_dashboard_stats($patient_id) {
    $patient_id = (int)$patient_id;
    $stats = [];
    
    $r = db_query("SELECT COUNT(*) AS c FROM appointments WHERE patient_id = $patient_id AND status IN ('pending','confirmed')");
    $stats['upcoming'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM prescriptions WHERE patient_id = $patient_id AND status = 'active'");
    $stats['active_rx'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM invoices WHERE patient_id = $patient_id AND status IN ('unpaid','partial')");
    $stats['unpaid'] = (int)mysqli_fetch_assoc($r)['c'];
    
    $r = db_query("SELECT COUNT(*) AS c FROM medical_history WHERE patient_id = $patient_id");
    $stats['history_records'] = (int)mysqli_fetch_assoc($r)['c'];
    
    return $stats;
}
