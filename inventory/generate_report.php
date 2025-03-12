<?php
require 'db_connection.php';
require('fpdf/fpdf.php');

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(190, 10, 'End of Game Report', 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);

// กำหนดความกว้างของแต่ละคอลัมน์ให้พอดีกับกระดาษ (รวมกันไม่เกิน 190 มม.)
$col_widths = [15, 35, 20, 25, 20, 20, 25, 30]; // ปรับความกว้างให้เหมาะสม

// หัวตาราง
$pdf->SetFillColor(200, 200, 200); // สีพื้นหลังหัวตาราง
$pdf->Cell($col_widths[0], 8, 'Game ID', 1, 0, 'C', true);
$pdf->Cell($col_widths[1], 8, 'Game Name', 1, 0, 'C', true);
$pdf->Cell($col_widths[2], 8, 'Quantity', 1, 0, 'C', true);
$pdf->Cell($col_widths[3], 8, 'Category', 1, 0, 'C', true);
$pdf->Cell($col_widths[4], 8, 'Date', 1, 0, 'C', true);
$pdf->Cell($col_widths[5], 8, 'Score', 1, 0, 'C', true);
$pdf->Cell($col_widths[6], 8, 'Developer', 1, 0, 'C', true);
$pdf->Cell($col_widths[7], 8, 'Platform', 1, 1, 'C', true);

// Query: ดึงข้อมูลพร้อม StockQuantity
$query = "SELECT GameID, GameName, StockQuantity, Category, ReleaseDate, Score, Developer, Platform 
          FROM games 
          ORDER BY LastUpdate ASC";

$result = $conn->query($query);

// ตรวจสอบผลลัพธ์ของ Query
if ($result === false) {
    die("Query failed: " . $conn->error);
}

$pdf->SetFont('Arial', '', 10);

while ($row = $result->fetch_assoc()) {
    // ตรวจสอบ StockQuantity ต่ำกว่า 3 เพื่อตั้งสีแดง
    if ($row['StockQuantity'] < 3) {
        $pdf->SetFillColor(255, 153, 153); // สีแดงอ่อน
    } else {
        $pdf->SetFillColor(255, 255, 255); // สีขาวปกติ
    }

    $pdf->Cell($col_widths[0], 8, $row['GameID'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[1], 8, $row['GameName'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[2], 8, $row['StockQuantity'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[3], 8, $row['Category'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[4], 8, $row['ReleaseDate'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[5], 8, $row['Score'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[6], 8, $row['Developer'], 1, 0, 'C', true);
    $pdf->Cell($col_widths[7], 8, $row['Platform'], 1, 1, 'C', true);
}

$pdf->Output('D', 'game_report.pdf'); // ดาวน์โหลดไฟล์ PDF
?>