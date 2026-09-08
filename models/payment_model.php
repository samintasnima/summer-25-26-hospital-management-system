<?php
/**
 * Payment Model
 */
require_once __DIR__ . '/../config/database.php';

function payment_create($data) {
    $invoice_id = (int)$data['invoice_id'];
    $patient_id = (int)$data['patient_id'];
    $amount     = (float)$data['amount'];
    $method     = db_escape($data['payment_method'] ?? 'cash');
    $ref        = db_escape($data['transaction_ref'] ?? '');
    $received_by= !empty($data['received_by']) ? (int)$data['received_by'] : 'NULL';
    $notes      = db_escape($data['notes'] ?? '');
    
    $sql = "INSERT INTO payments (invoice_id, patient_id, amount, payment_method, transaction_ref, received_by, notes)
            VALUES ($invoice_id, $patient_id, $amount, '$method', '$ref', $received_by, '$notes')";
    
    if (db_query($sql)) {
        $payment_id = db_insert_id();
        // Update invoice status
        require_once __DIR__ . '/invoice_model.php';
        invoice_recalculate_status($invoice_id);
        return $payment_id;
    }
    return false;
}

function payment_update($id, $data) {
    $id = (int)$id;
    $sets = [];
    if (isset($data['amount'])) $sets[] = "amount = " . (float)$data['amount'];
    if (isset($data['payment_method'])) $sets[] = "payment_method = '" . db_escape($data['payment_method']) . "'";
    if (isset($data['transaction_ref'])) $sets[] = "transaction_ref = '" . db_escape($data['transaction_ref']) . "'";
    if (isset($data['notes'])) $sets[] = "notes = '" . db_escape($data['notes']) . "'";
    if (empty($sets)) return false;
    $r = db_query("SELECT invoice_id FROM payments WHERE id = $id");
    $row = $r ? mysqli_fetch_assoc($r) : null;
    if (!$row) return false;
    $ok = db_query("UPDATE payments SET " . implode(', ', $sets) . " WHERE id = $id");
    if ($ok) {
        require_once __DIR__ . '/invoice_model.php';
        invoice_recalculate_status((int)$row['invoice_id']);
    }
    return $ok;
}

function payment_delete($id) {
    $id = (int)$id;
    // Get invoice_id first
    $r = db_query("SELECT invoice_id FROM payments WHERE id = $id");
    $row = mysqli_fetch_assoc($r);
    if (!$row) return false;
    
    $ok = db_query("DELETE FROM payments WHERE id = $id");
    if ($ok) {
        require_once __DIR__ . '/invoice_model.php';
        invoice_recalculate_status($row['invoice_id']);
    }
    return $ok;
}

function payment_find($id) {
    $id = (int)$id;
    $sql = "SELECT py.*, u.full_name AS patient_name, i.invoice_number
            FROM payments py
            JOIN users u ON py.patient_id = u.id
            JOIN invoices i ON py.invoice_id = i.id
            WHERE py.id = $id LIMIT 1";
    $result = db_query($sql);
    return $result ? mysqli_fetch_assoc($result) : null;
}

function payment_search($filters = [], $limit = 50) {
    $where = ["1=1"];
    if (!empty($filters['patient_id'])) $where[] = "py.patient_id = " . (int)$filters['patient_id'];
    if (!empty($filters['invoice_id'])) $where[] = "py.invoice_id = " . (int)$filters['invoice_id'];
    if (!empty($filters['from'])) $where[] = "DATE(py.payment_date) >= '" . db_escape($filters['from']) . "'";
    if (!empty($filters['to'])) $where[] = "DATE(py.payment_date) <= '" . db_escape($filters['to']) . "'";
    if (!empty($filters['keyword'])) {
        $kw = db_escape($filters['keyword']);
        $where[] = "(u.full_name LIKE '%$kw%' OR i.invoice_number LIKE '%$kw%' OR py.transaction_ref LIKE '%$kw%')";
    }
    $where_sql = implode(' AND ', $where);
    $limit = (int)$limit;
    
    $sql = "SELECT py.*, u.full_name AS patient_name, i.invoice_number
            FROM payments py
            JOIN users u ON py.patient_id = u.id
            JOIN invoices i ON py.invoice_id = i.id
            WHERE $where_sql
            ORDER BY py.payment_date DESC
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

function payment_total_for_invoice($invoice_id) {
    $invoice_id = (int)$invoice_id;
    $r = db_query("SELECT COALESCE(SUM(amount),0) AS total FROM payments WHERE invoice_id = $invoice_id");
    return (float)mysqli_fetch_assoc($r)['total'];
}
