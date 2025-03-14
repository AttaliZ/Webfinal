<?php
include('db_connection.php');

if (isset($_GET['query'])) {
    $query = mysqli_real_escape_string($conn, $_GET['query']);
    
    // ค้นหาเกมที่มีชื่อคล้ายกับคำที่ผู้ใช้กรอก
    $sql = "SELECT GameName, Category, Developer, Platform, ReleaseDate, Score, Description 
            FROM games 
            WHERE GameName LIKE '%$query%' 
            LIMIT 5"; // จำกัดผลลัพธ์ 5 รายการ
    
    $result = mysqli_query($conn, $sql);
    
    $games = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $games[] = $row;
    }
    
    // ส่งผลลัพธ์กลับในรูปแบบ JSON
    echo json_encode($games);
}

mysqli_close($conn);
?>