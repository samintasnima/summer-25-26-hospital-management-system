<?php
/**
 * Queue Model - Receptionist unique feature
 */
require_once __DIR__ . '/../config/database.php';

function queue_add($data) {
    $appointment_id = !empty($data['appointment_id']) ? (int)$data['appointment_id'] : 'NULL';
    $patient_id = (int)$data['patient_id'];
    $doctor_id  = (int)$data['doctor_id'];
    $date = db_escape($data['queue_date'] ?? date('Y-m-d'));
    $priority = db_escape($data['priority'] ?? 'normal');
    
    // Get next queue number for the day + doctor
    $r = db_query("SELECT COALESCE(MAX(queue_number),0) + 1 AS next_num FROM queue WHERE queue_date = '$date' AND doctor_id = $doctor_id");
    $next = (int)mysqli_fetch_assoc($r)['next_num'];
    
    $sql = "INSERT INTO queue (appointment_id, patient_id, doctor_id, queue_number, queue_date, priority, status)
            VALUES ($appointment_id, $patient_id, $doctor_id, $next, '$date', '$priority', 'waiting')";
    return db_query($sql) ? db_insert_id() : false;
}

function queue_update_status($id, $status) {
    $id = (int)$id;
    $status = db_escape($status);
    $extra = '';
    if ($status === 'called') {
        $extra = ", called_at = NOW()";
    } elseif ($status === 'completed') {
        $extra = ", completed_at = NOW()";
    }
    return db_query("UPDATE queue SET status = '$status' $extra WHERE id = $id");
}

function queue_delete($id) {
    $id = (int)$id;
    return db_query("DELETE FROM queue WHERE id = $id");
}

function queue_find($id) {
    $id = (int)$id;
    $sql = "SELECT q.*, p.full_name AS patient_name, p.phone AS patient_phone,
                   d.full_name AS doctor_name
            FROM queue q
            JOIN users p ON q.patient_id = p.id
            JOIN users d ON q.doctor_id = d.id
            WHERE q.id = $id LIMIT 1";
    $result = db_query($sql);
    return $result ? mysqli_fetch_assoc($result) : null;
}

function queue_list($date = null, $doctor_id = null, $status = '') {
    $date = $date ? db_escape($date) : date('Y-m-d');
    $where = ["q.queue_date = '$date'"];
    if ($doctor_id) $where[] = "q.doctor_id = " . (int)$doctor_id;
    if ($status !== '') $where[] = "q.status = '" . db_escape($status) . "'";
    
    $where_sql = implode(' AND ', $where);
    
    $sql = "SELECT q.*, p.full_name AS patient_name, p.phone AS patient_phone,
                   d.full_name AS doctor_name
            FROM queue q
            JOIN users p ON q.patient_id = p.id
            JOIN users d ON q.doctor_id = d.id
            WHERE $where_sql
            ORDER BY FIELD(q.priority,'urgent','normal'), q.queue_number ASC";
    $result = db_query($sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function queue_search($keyword, $date = null) {
    $date = $date ? db_escape($date) : date('Y-m-d');
    $kw = db_escape($keyword);
    $sql = "SELECT q.*, p.full_name AS patient_name, d.full_name AS doctor_name
            FROM queue q
            JOIN users p ON q.patient_id = p.id
            JOIN users d ON q.doctor_id = d.id
            WHERE q.queue_date = '$date' AND (p.full_name LIKE '%$kw%' OR d.full_name LIKE '%$kw%')
            ORDER BY q.queue_number";
    $result = db_query($sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}
