<?php
/**
 * Database Configuration - Procedural MySQLi
 * Hospital Management System
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // default XAMPP password is empty
define('DB_NAME', 'hospital_management');

/**
 * Get a MySQLi connection
 * @return mysqli
 */
function get_db_connection() {
    static $conn = null;
    if ($conn === null) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$conn) {
            // In production you would log this, not die
            die('Database connection failed: ' . mysqli_connect_error());
        }
        mysqli_set_charset($conn, 'utf8mb4');
    }
    return $conn;
}

/**
 * Escape string safely
 */
function db_escape($value) {
    $conn = get_db_connection();
    return mysqli_real_escape_string($conn, trim($value));
}

/**
 * Execute a query and return result
 */
function db_query($sql) {
    $conn = get_db_connection();
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        // Log error in real apps
        error_log('SQL Error: ' . mysqli_error($conn) . ' | Query: ' . $sql);
    }
    return $result;
}

/**
 * Get last insert id
 */
function db_insert_id() {
    return mysqli_insert_id(get_db_connection());
}

/**
 * Get affected rows
 */
function db_affected_rows() {
    return mysqli_affected_rows(get_db_connection());
}
