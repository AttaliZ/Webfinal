<?php
$servername = getenv('DB_SERVER') ?: '158.108.101.153';
$username = getenv('DB_USERNAME') ?: 'std6630202040';
$password = getenv('DB_PASSWORD') ?: 'nZ!4pQrt';
$dbname = getenv('DB_NAME') ?: 'it_std6630202040';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // เปิด error report

try {
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    mysqli_set_charset($conn, "utf8");
} catch (Exception $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Sorry, we're experiencing some technical difficulties. Please try again later.");
}