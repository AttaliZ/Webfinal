<?php
session_start(); // เริ่ม session เพื่อตรวจสอบการล็อกอิน

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    // หากไม่ได้ล็อกอิน ให้ redirect ไปหน้า login
    header('Location: inventory.php');
    exit;
}

// รวมไฟล์การเชื่อมต่อฐานข้อมูล
require 'db_connection.php';

// ตรวจสอบว่ามี parameter id หรือไม่
if (isset($_GET['id'])) {
    // ป้องกัน SQL Injection ด้วย mysqli_real_escape_string
    $gameId = mysqli_real_escape_string($conn, $_GET['id']);

    // สร้างคำสั่ง SQL สำหรับลบข้อมูล
    $deleteQuery = "DELETE FROM games WHERE GameID = '$gameId'";

    // 执行删除操作
    if (mysqli_query($conn, $deleteQuery)) {
        // หากลบสำเร็จ ให้ redirect กลับไปที่หน้า inventory พร้อม parameter deleted=true
        header('Location: inventory.php?deleted=true');
        exit;
    } else {
        // หากเกิดข้อผิดพลาด ให้แสดงข้อความ
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    // หากไม่มี parameter id ให้แสดงข้อความเตือน
    echo "No ID provided for deletion.";
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>