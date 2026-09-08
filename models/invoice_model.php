<?php
/**
 * Invoice Model
 */
require_once __DIR__ . '/../config/database.php';

function invoice_create($data) {
    $invoice_number = db_escape($data['invoice_number'] ?? generate_invoice_number());
    $patient_id = (int)$data['patient_id'];
    $appointment_id = !empty($data['appointment_id']) ? (int)$data['appointment_id'] : 'NULL';
    $amount = (float)$data['amount'];
    $tax = (float)($data['tax'] ?? 0);
    $discount = (float)($data['discount'] ?? 0);
    $total = $amount + $tax - $discount;
    $desc = db_escape($data['description'] ?? '');
    $status = db_escape($data['status'] ?? 'unpaid');
    $due = !empty($data['due_date']) ? "'" . db_escape($data['due_date']) . "'" : 'NULL';
    $created_by = !empty($data['created_by']) ? (int)$data['created_by'] : 'NULL';
    
    $sql = "INSERT INTO invoices (invoice_number, patient_id, appointment_id, amount, tax, discount, total_amount, description, status, due_date, created_by)
            VALUES ('$invoice_number', $patient_id, $appointment_id, $amount, $tax, $discount, $total, '$desc', '$status', $due, $created_by)";
    return db_query($sql) ? db_insert_id() : false;
}

function invoice_update($id, $data) {
    $id = (int)$id;
    $sets = [];
    if (isset($data['amount'])) {
        $amount = (float)$data['amount'];
        $tax = isset($data['tax']) ? (float)$data['tax'] : null;
        $discount = isset($data['discount']) ? (float)$data['discount'] : null;
        
        // Need current values if not provided
        $curr = invoice_find($id);
        if ($tax === null) $tax = (float)$curr['tax'];
        if ($discount === null) $discount = (float)$curr['discount'];
        $total = $amount + $tax - $discount;
        
        $sets[] = "amount = $amount";
        $sets[] = "tax = $tax";
        $sets[] = "discount = $discount";
        $sets[] = "total_amount = $total";
    }
    if (isset($data['description'])) $sets[] = "description = '" . db_escape($data['description']) . "'";
    if (isset($data['status'])) $sets[] = "status = '" . db_escape($data['status']) . "'";
    if (isset($data['due_date'])) $sets[] = "due_date = '" . db_escape($data['due_date']) . "'";
    
    if (empty($sets)) return false;
    return db_query("UPDATE invoices SET " . implode(', ', $sets) . " WHERE id = $id");
}

function invoice_delete($id) {
    $id = (int)$id;
    // Also delete related payments? For simplicity yes
    db_query("DELETE FROM payments WHERE invoice_id = $id");
    return db_query("DELETE FROM invoices WHERE id = $id");
}

function invoice_find($id) {
    $id = (int)$id;
    $sql = "SELECT i.*, u.full_name AS patient_name, u.phone AS patient_phone, u.email AS patient_email
            FROM invoices i
            JOIN users u ON i.patient_id = u.id
            WHERE i.id = $id LIMIT 1";
    $result = db_query($sql);
    return $result ? mysqli_fetch_assoc($result) : null;
}

function invoice_recalculate_status($invoice_id) {
    $invoice_id = (int)$invoice_id;
    $inv = invoice_find($invoice_id);
    if (!$inv) return false;
    
    require_once __DIR__ . '/payment_model.php';
    $paid = payment_total_for_invoice($invoice_id);
    $total = (float)$inv['total_amount'];
    
    if ($paid <= 0) {
        $status = 'unpaid';
    } elseif ($paid >= $total) {
        $status = 'paid';
    } else {
        $status = 'partial';
    }
    
    return db_query("UPDATE invoices SET status = '$status' WHERE id = $invoice_id");
}

function invoice_search($filters = [], $limit = 50) {
    $where = ["1=1"];
    if (!empty($filters['patient_id'])) $where[] = "i.patient_id = " . (int)$filters['patient_id'];
    if (!empty($filters['status'])) $where[] = "i.status = '" . db_escape($filters['status']) . "'";
    if (!empty($filters['keyword'])) {
        $kw = db_escape($filters['keyword']);
        $where[] = "(i.invoice_number LIKE '%$kw%' OR u.full_name LIKE '%$kw%')";
    }
    $where_sql = implode(' AND ', $where);
    $limit = (int)$limit;
    
    $sql = "SELECT i.*, u.full_name AS patient_name
            FROM invoices i
            JOIN users u ON i.patient_id = u.id
            WHERE $where_sql
            ORDER BY i.created_at DESC
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
