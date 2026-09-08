<?php
/**
 * User Model - Procedural
 */
require_once __DIR__ . '/../config/database.php';

function user_find_by_id($id) {
    $id = (int)$id;
    $result = db_query("SELECT * FROM users WHERE id = $id LIMIT 1");
    return $result ? mysqli_fetch_assoc($result) : null;
}

function user_find_by_username($username) {
    $username = db_escape($username);
    $result = db_query("SELECT * FROM users WHERE username = '$username' LIMIT 1");
    return $result ? mysqli_fetch_assoc($result) : null;
}

function user_find_by_email($email) {
    $email = db_escape($email);
    $result = db_query("SELECT * FROM users WHERE email = '$email' LIMIT 1");
    return $result ? mysqli_fetch_assoc($result) : null;
}

function user_create($data) {
    $username = db_escape($data['username']);
    $email    = db_escape($data['email']);
    $password = db_escape($data['password']); // already hashed
    $full_name= db_escape($data['full_name']);
    $role     = db_escape($data['role']);
    $phone    = db_escape($data['phone'] ?? '');
    $address  = db_escape($data['address'] ?? '');
    $gender   = db_escape($data['gender'] ?? '');
    $dob      = !empty($data['date_of_birth']) ? "'" . db_escape($data['date_of_birth']) . "'" : 'NULL';
    $spec     = db_escape($data['specialization'] ?? '');
    $status   = db_escape($data['status'] ?? 'active');

    $sql = "INSERT INTO users (username, email, password, full_name, role, phone, address, gender, date_of_birth, specialization, status)
            VALUES ('$username', '$email', '$password', '$full_name', '$role', '$phone', '$address', '$gender', $dob, '$spec', '$status')";
    
    if (db_query($sql)) {
        return db_insert_id();
    }
    return false;
}

function user_update($id, $data) {
    $id = (int)$id;
    $sets = [];
    $allowed = ['username','email','full_name','phone','address','gender','date_of_birth','specialization','status','role'];
    
    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            if ($field === 'date_of_birth' && empty($data[$field])) {
                $sets[] = "date_of_birth = NULL";
            } else {
                $sets[] = "$field = '" . db_escape($data[$field]) . "'";
            }
        }
    }
    if (isset($data['password']) && !empty($data['password'])) {
        $sets[] = "password = '" . db_escape($data['password']) . "'";
    }
    if (empty($sets)) return false;

    $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = $id";
    return db_query($sql);
}

function user_delete($id) {
    $id = (int)$id;
    // Soft delete preferred, but requirement allows hard delete
    return db_query("DELETE FROM users WHERE id = $id AND role != 'admin'");
}

function user_search($keyword = '', $role = '', $limit = 50, $offset = 0) {
    $where = ["1=1"];
    if ($keyword !== '') {
        $kw = db_escape($keyword);
        $where[] = "(username LIKE '%$kw%' OR email LIKE '%$kw%' OR full_name LIKE '%$kw%' OR phone LIKE '%$kw%')";
    }
    if ($role !== '') {
        $role = db_escape($role);
        $where[] = "role = '$role'";
    }
    $where_sql = implode(' AND ', $where);
    $limit = (int)$limit;
    $offset = (int)$offset;
    $sql = "SELECT id, username, email, full_name, role, phone, specialization, status, created_at 
            FROM users WHERE $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $result = db_query($sql);
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}

function user_count($keyword = '', $role = '') {
    $where = ["1=1"];
    if ($keyword !== '') {
        $kw = db_escape($keyword);
        $where[] = "(username LIKE '%$kw%' OR email LIKE '%$kw%' OR full_name LIKE '%$kw%')";
    }
    if ($role !== '') {
        $role = db_escape($role);
        $where[] = "role = '$role'";
    }
    $where_sql = implode(' AND ', $where);
    $result = db_query("SELECT COUNT(*) AS cnt FROM users WHERE $where_sql");
    $row = mysqli_fetch_assoc($result);
    return (int)($row['cnt'] ?? 0);
}

function user_get_doctors($active_only = true) {
    $status = $active_only ? "AND status = 'active'" : '';
    $result = db_query("SELECT id, full_name, specialization, phone, email FROM users WHERE role = 'doctor' $status ORDER BY full_name");
    $rows = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
    }
    return $rows;
}
