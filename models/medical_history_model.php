<?php
/**
 * Medical History Model
 */
require_once __DIR__ . '/../config/database.php';

function medical_history_create($data) {
    $patient_id = (int)$data['patient_id'];
    $doctor_id  = !empty($data['doctor_id']) ? (int)$data['doctor_id'] : 'NULL';
    $type       = db_escape($data['record_type'] ?? 'diagnosis');
    $title      = db_escape($data['title']);
    $desc       = db_escape($data['description'] ?? '');
    $date       = db_escape($data['record_date'] ?? date('Y-m-d'));
    
    $sql = "INSERT INTO medical_history (patient_id, doctor_id, record_type, title, description, record_date)
            VALUES ($patient_id, $doctor_id, '$type', '$title', '$desc', '$date')";
    return db_query($sql) ? db_insert_id() : false;
}

function medical_history_update($id, $data) {
    $id = (int)$id;
    $sets = [];
    if (isset($data['record_type'])) $sets[] = "record_type = '" . db_escape($data['record_type']) . "'";
    if (isset($data['title'])) $sets[] = "title = '" . db_escape($data['title']) . "'";
    if (isset($data['description'])) $sets[] = "description = '" . db_escape($data['description']) . "'";
    if (isset($data['record_date'])) $sets[] = "record_date = '" . db_escape($data['record_date']) . "'";
    if (empty($sets)) return false;
    return db_query("UPDATE medical_history SET " . implode(', ', $sets) . " WHERE id = $id");
}

function medical_history_delete($id) {
    $id = (int)$id;
    return db_query("DELETE FROM medical_history WHERE id = $id");
}

function medical_history_find($id) {
    $id = (int)$id;
    $sql = "SELECT mh.*, p.full_name AS patient_name, d.full_name AS doctor_name
            FROM medical_history mh
            JOIN users p ON mh.patient_id = p.id
            LEFT JOIN users d ON mh.doctor_id = d.id
            WHERE mh.id = $id LIMIT 1";
    $result = db_query($sql);
    return $result ? mysqli_fetch_assoc($result) : null;
}

function medical_history_by_patient($patient_id, $limit = 100) {
    $patient_id = (int)$patient_id;
    $limit = (int)$limit;
    $sql = "SELECT mh.*, d.full_name AS doctor_name
            FROM medical_history mh
            LEFT JOIN users d ON mh.doctor_id = d.id
            WHERE mh.patient_id = $patient_id
            ORDER BY mh.record_date DESC
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

function medical_history_search($filters = [], $limit = 50) {
    $where = ["1=1"];
    if (!empty($filters['patient_id'])) $where[] = "mh.patient_id = " . (int)$filters['patient_id'];
    if (!empty($filters['doctor_id']))  $where[] = "mh.doctor_id = " . (int)$filters['doctor_id'];
    if (!empty($filters['record_type'])) $where[] = "mh.record_type = '" . db_escape($filters['record_type']) . "'";
    if (!empty($filters['keyword'])) {
        $kw = db_escape($filters['keyword']);
        $where[] = "(mh.title LIKE '%$kw%' OR mh.description LIKE '%$kw%' OR p.full_name LIKE '%$kw%')";
    }
    $where_sql = implode(' AND ', $where);
    $limit = (int)$limit;
    
    $sql = "SELECT mh.*, p.full_name AS patient_name, d.full_name AS doctor_name
            FROM medical_history mh
            JOIN users p ON mh.patient_id = p.id
            LEFT JOIN users d ON mh.doctor_id = d.id
            WHERE $where_sql
            ORDER BY mh.record_date DESC
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
