<?php
session_start();
require_once 'db_connection.php';
require_once 'tcpdf/tcpdf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// กำหนดภาษาจากพารามิเตอร์ report_lang
$report_lang = isset($_GET['report_lang']) && in_array($_GET['report_lang'], ['en', 'th']) ? $_GET['report_lang'] : 'en';

$lang = [
    'th' => [
        'title' => '(TH/EN)',
        'game_name' => 'ชื่อเกม',
        'category' => 'หมวดหมู่',
        'stock_quantity' => 'จำนวนคงเหลือ',
        'platform' => 'แพลตฟอร์ม',
        'developer' => 'ผู้พัฒนา',
        'release_date' => 'วันที่วางจำหน่าย',
        'score' => 'คะแนน',
        'status' => 'สถานะ',
        'restock' => '(ต้องเติมสต็อก)',
        'sold' => 'ขายแล้ว',
        'available' => 'พร้อมขาย'
    ],
    'en' => [
        'title' => 'Game Stock Report (TH/EN)',
        'game_name' => 'Game Name',
        'category' => 'Category',
        'stock_quantity' => 'Stock Quantity',
        'platform' => 'Platform',
        'developer' => 'Developer',
        'release_date' => 'Release Date',
        'score' => 'Score',
        'status' => 'Status',
        'restock' => '(Restock Needed)',
        'sold' => 'Sold',
        'available' => 'Available'
    ]
];

$current_lang = $report_lang;

// ดึงข้อมูลจากตาราง games
$query = "SELECT * FROM games ORDER BY LastUpdate DESC";
$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

$games = [];
while ($row = $result->fetch_assoc()) {
    $games[] = $row;
}

// สร้าง PDF ด้วย TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Stock Management System');
$pdf->SetTitle($lang[$current_lang]['title']);
$pdf->SetHeaderData('', 0, $lang[$current_lang]['title'], '');

$pdf->SetFont('freeserif', '', 10);
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$html = '<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid black; padding: 5px; text-align: center; }
    th { background-color: #f2f2f2; font-weight: bold; }
</style>';

$html .= '<h1 style="text-align:center;">' . htmlspecialchars($lang[$current_lang]['title'], ENT_QUOTES, 'UTF-8') . '</h1>';
$html .= '<table>
    <thead>
        <tr>
            <th width="20%">' . htmlspecialchars($lang[$current_lang]['game_name'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="15%">' . htmlspecialchars($lang[$current_lang]['category'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="10%">' . htmlspecialchars($lang[$current_lang]['stock_quantity'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="15%">' . htmlspecialchars($lang[$current_lang]['platform'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="15%">' . htmlspecialchars($lang[$current_lang]['developer'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="10%">' . htmlspecialchars($lang[$current_lang]['release_date'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="5%">' . htmlspecialchars($lang[$current_lang]['score'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="10%">' . htmlspecialchars($lang[$current_lang]['status'], ENT_QUOTES, 'UTF-8') . '</th>
        </tr>
    </thead>
    <tbody>';

foreach ($games as $game) {
    $quantity = intval($game['StockQuantity']);
    $color = ($quantity < 3) ? 'color:red; font-weight:bold;' : '';
    $restock = ($quantity < 3) ? ' <span style="color:red;">' . $lang[$current_lang]['restock'] . '</span>' : '';
    
    $status = $game['status'] === 'sold' ? $lang[$current_lang]['sold'] : $lang[$current_lang]['available'];
    $status_color = $game['status'] === 'sold' ? 'color:gray;' : 'color:green;';

    $html .= '<tr>
        <td width="20%" style="text-align:left;">' . htmlspecialchars($game['GameName'], ENT_QUOTES, 'UTF-8') . '</td>
        <td width="15%" style="text-align:left;">' . htmlspecialchars($game['Category'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
        <td width="10%" style="text-align:center; ' . $color . '">' . $quantity . $restock . '</td>
        <td width="15%" style="text-align:left;">' . htmlspecialchars($game['Platform'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
        <td width="15%" style="text-align:left;">' . htmlspecialchars($game['Developer'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
        <td width="10%" style="text-align:center;">' . htmlspecialchars($game['ReleaseDate'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
        <td width="5%" style="text-align:center;">' . htmlspecialchars($game['Score'] ?? '-', ENT_QUOTES, 'UTF-8') . '</td>
        <td width="10%" style="text-align:center; ' . $status_color . '">' . $status . '</td>
    </tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Output('stock_report.pdf', 'D');
exit();
?>