<?php
/**
 * Prescription Model
 */
require_once __DIR__ . '/../config/database.php';

function prescription_create($data) {
    $appointment_id = !empty($data['appointment_id']) ? (int)$data['appointment_id'] : 'NULL';
    $patient_id = (int)$data['patient_id'];
    $doctor_id  = (int)$data['doctor_id'];
    $diagnosis  = db_escape($data['diagnosis'] ?? '');
    $medicines  = db_escape($data['medicines']);
    $dosage     = db_escape($data['dosage_instructions'] ?? '');
    $notes      = db_escape($data['notes'] ?? '');
    $date       = db_escape($data['prescribed_date'] ?? date('Y-m-d'));
    $status     = db_escape($data['status'] ?? 'active');
    
    $sql = "INSERT INTO prescriptions (appointment_id, patient_id, doctor_id, diagnosis, medicines, dosage_instructions, notes, prescribed_date, status)
            VALUES ($appointment_id, $patient_id, $doctor_id, '$diagnosis', '$medicines', '$dosage', '$notes', '$date', '$status')";
    return db_query($sql) ? db_insert_id() : false;
}

function prescription_update($id, $data) {
    $id = (int)$id;
    $sets = [];
    $fields = ['diagnosis','medicines','dosage_instructions','notes','status','prescribed_date'];
    foreach ($fields as $f) {
        if (isset($data[$f])) {
            $sets[] = "$f = '" . db_escape($data[$f]) . "'";
        }
    }
    if (empty($sets)) return false;
    return db_query("UPDATE prescriptions SET " . implode(', ', $sets) . " WHERE id = $id");
}

function prescription_delete($id) {
    $id = (int)$id;
    return db_query("DELETE FROM prescriptions WHERE id = $id");
}

function prescription_find($id) {
    $id = (int)$id;
    $sql = "SELECT pr.*, 
                   p.full_name AS patient_name, p.phone AS patient_phone,
                   d.full_name AS doctor_name
            FROM prescriptions pr
            JOIN users p ON pr.patient_id = p.id
            JOIN users d ON pr.doctor_id = d.id
            WHERE pr.id = $id LIMIT 1";
    $result = db_query($sql);
    return $result ? mysqli_fetch_assoc($result) : null;
}

function prescription_search($filters = [], $limit = 50) {
    $where = ["1=1"];
    if (!empty($filters['patient_id'])) $where[] = "pr.patient_id = " . (int)$filters['patient_id'];
    if (!empty($filters['doctor_id']))  $where[] = "pr.doctor_id = " . (int)$filters['doctor_id'];
    if (!empty($filters['status']))     $where[] = "pr.status = '" . db_escape($filters['status']) . "'";
    if (!empty($filters['keyword'])) {
        $kw = db_escape($filters['keyword']);
        $where[] = "(p.full_name LIKE '%$kw%' OR pr.diagnosis LIKE '%$kw%' OR pr.medicines LIKE '%$kw%')";
    }
    $where_sql = implode(' AND ', $where);
    $limit = (int)$limit;
    
    $sql = "SELECT pr.*, p.full_name AS patient_name, d.full_name AS doctor_name
            FROM prescriptions pr
            JOIN users p ON pr.patient_id = p.id
            JOIN users d ON pr.doctor_id = d.id
            WHERE $where_sql
            ORDER BY pr.prescribed_date DESC
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
