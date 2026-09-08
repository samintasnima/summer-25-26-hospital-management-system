<?php
/**
 * Activity Log Model - Admin unique feature
 */
require_once __DIR__ . '/../config/database.php';

function log_search($filters = [], $limit = 100, $offset = 0) {
    $where = ["1=1"];
    if (!empty($filters['user_id'])) $where[] = "l.user_id = " . (int)$filters['user_id'];
    if (!empty($filters['action'])) $where[] = "l.action LIKE '%" . db_escape($filters['action']) . "%'";
    if (!empty($filters['from'])) $where[] = "DATE(l.created_at) >= '" . db_escape($filters['from']) . "'";
    if (!empty($filters['to'])) $where[] = "DATE(l.created_at) <= '" . db_escape($filters['to']) . "'";
    if (!empty($filters['keyword'])) {
        $kw = db_escape($filters['keyword']);
        $where[] = "(l.action LIKE '%$kw%' OR l.details LIKE '%$kw%' OR u.full_name LIKE '%$kw%' OR u.username LIKE '%$kw%')";
    }
    $where_sql = implode(' AND ', $where);
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    $sql = "SELECT l.*, u.full_name, u.username, u.role
            FROM activity_logs l
            LEFT JOIN users u ON l.user_id = u.id
            WHERE $where_sql
            ORDER BY l.created_at DESC
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

function log_count($filters = []) {
    $where = ["1=1"];
    if (!empty($filters['user_id'])) $where[] = "user_id = " . (int)$filters['user_id'];
    if (!empty($filters['from'])) $where[] = "DATE(created_at) >= '" . db_escape($filters['from']) . "'";
    if (!empty($filters['to'])) $where[] = "DATE(created_at) <= '" . db_escape($filters['to']) . "'";
    $where_sql = implode(' AND ', $where);
    $r = db_query("SELECT COUNT(*) AS cnt FROM activity_logs WHERE $where_sql");
    return (int)mysqli_fetch_assoc($r)['cnt'];
}

function log_clear_old($days = 90) {
    $days = (int)$days;
    return db_query("DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL $days DAY)");
}
