<?php
/**
 * Hospital Management System - Config
 * Style aligned with full-stack library management pattern
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hospital_management');

define('APP_NAME', 'HospitalMS');
define('CURRENCY', '৳');
define('SESSION_TIMEOUT', 1800); // 30 minutes

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
    die('Database connection failed. Import database.sql first. ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
