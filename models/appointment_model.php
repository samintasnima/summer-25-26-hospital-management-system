<?php
/**
 * Appointment Model
 */
require_once __DIR__ . '/../config/database.php';

function appointment_create($data) {
    $patient_id = (int)$data['patient_id'];
    $doctor_id  = (int)$data['doctor_id'];
    $date       = db_escape($data['appointment_date']);
    $time       = db_escape($data['appointment_time']);
    $reason     = db_escape($data['reason'] ?? '');
    $status     = db_escape($data['status'] ?? 'pending');
    $created_by = isset($data['created_by']) ? (int)$data['created_by'] : 'NULL';
    
    $sql = "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status, created_by)
            VALUES ($patient_id, $doctor_id, '$date', '$time', '$reason', '$status', $created_by)";
    return db_query($sql) ? db_insert_id() : false;
}

function appointment_update($id, $data) {
    $id = (int)$id;
    $sets = [];
    $fields = ['patient_id','doctor_id','appointment_date','appointment_time','reason','status','notes'];
    foreach ($fields as $f) {
        if (isset($data[$f])) {
            if (in_array($f, ['patient_id','doctor_id'])) {
                $sets[] = "$f = " . (int)$data[$f];
            } else {
                $sets[] = "$f = '" . db_escape($data[$f]) . "'";
            }
        }
    }
    if (empty($sets)) return false;
    $sql = "UPDATE appointments SET " . implode(', ', $sets) . " WHERE id = $id";
    return db_query($sql);
}

function appointment_delete($id) {
    $id = (int)$id;
    return db_query("DELETE FROM appointments WHERE id = $id");
}

function appointment_find($id) {
    $id = (int)$id;
    $sql = "SELECT a.*, 
                   p.full_name AS patient_name, p.phone AS patient_phone, p.email AS patient_email,
                   d.full_name AS doctor_name, d.specialization
            FROM appointments a
            JOIN users p ON a.patient_id = p.id
            JOIN users d ON a.doctor_id = d.id
            WHERE a.id = $id LIMIT 1";
    $result = db_query($sql);
    return $result ? mysqli_fetch_assoc($result) : null;
}

function appointment_search($filters = [], $limit = 50, $offset = 0) {
    $where = ["1=1"];
    if (!empty($filters['patient_id'])) $where[] = "a.patient_id = " . (int)$filters['patient_id'];
    if (!empty($filters['doctor_id']))  $where[] = "a.doctor_id = " . (int)$filters['doctor_id'];
    if (!empty($filters['status']))     $where[] = "a.status = '" . db_escape($filters['status']) . "'";
    if (!empty($filters['date']))       $where[] = "a.appointment_date = '" . db_escape($filters['date']) . "'";
    if (!empty($filters['from']))       $where[] = "a.appointment_date >= '" . db_escape($filters['from']) . "'";
    if (!empty($filters['to']))         $where[] = "a.appointment_date <= '" . db_escape($filters['to']) . "'";
    if (!empty($filters['keyword'])) {
        $kw = db_escape($filters['keyword']);
        $where[] = "(p.full_name LIKE '%$kw%' OR d.full_name LIKE '%$kw%' OR a.reason LIKE '%$kw%')";
    }
    
    $where_sql = implode(' AND ', $where);
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    $sql = "SELECT a.*, 
                   p.full_name AS patient_name, p.phone AS patient_phone,
                   d.full_name AS doctor_name, d.specialization
            FROM appointments a
            JOIN users p ON a.patient_id = p.id
            JOIN users d ON a.doctor_id = d.id
            WHERE $where_sql
            ORDER BY a.appointment_date DESC, a.appointment_time ASC
            LIMIT $limit OFFSET $offset";
    
    $result = db_query($sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function appointment_count($filters = []) {
    $where = ["1=1"];
    if (!empty($filters['patient_id'])) $where[] = "a.patient_id = " . (int)$filters['patient_id'];
    if (!empty($filters['doctor_id']))  $where[] = "a.doctor_id = " . (int)$filters['doctor_id'];
    if (!empty($filters['status']))     $where[] = "a.status = '" . db_escape($filters['status']) . "'";
    if (!empty($filters['date']))       $where[] = "a.appointment_date = '" . db_escape($filters['date']) . "'";
    
    $where_sql = implode(' AND ', $where);
    $sql = "SELECT COUNT(*) AS cnt FROM appointments a WHERE $where_sql";
    $r = db_query($sql);
    return (int)mysqli_fetch_assoc($r)['cnt'];
}
