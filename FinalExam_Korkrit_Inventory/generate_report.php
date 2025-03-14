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
        'product_id' => 'รหัสสินค้า',
        'product_name' => 'ชื่อสินค้า',
        'stock_quantity' => 'จำนวนคงเหลือ',
        'unit_price' => 'ราคาต่อหน่วย',
        'image' => 'รูปภาพ',
        'last_update' => 'อัปเดตล่าสุด',
        'restock' => '(ต้องเติมสต็อก)'
    ],
    'en' => [
        'title' => 'Inventory Report (TH/EN)',
        'product_id' => 'Product ID',
        'product_name' => 'Product Name',
        'stock_quantity' => 'Stock Quantity',
        'unit_price' => 'Unit Price',
        'image' => 'Image',
        'last_update' => 'Last Update',
        'restock' => '(Restock Needed)'
    ]
];

$current_lang = $report_lang;

// ตรวจสอบว่าตารางมีอยู่หรือไม่
$checkTableQuery = "SHOW TABLES LIKE 'FinalExam_Korkrit_Pip_Inventory'";
$checkTableResult = $conn->query($checkTableQuery);

if ($checkTableResult->num_rows == 0) {
    die("Error: Table 'FinalExam_Korkrit_Pip_Inventory' does not exist in the database.");
}

// ดึงข้อมูลจากตาราง FinalExam_Korkrit_Pip_Inventory
$query = "SELECT Korkrit_Pip_ID_Product, Korkrit_Pip_Name_Product, Korkrit_Pip_Qty_Stock, Korkrit_Pip_Price_Unit, Korkrit_Pip_Img_Path, LastUpdate 
          FROM FinalExam_Korkrit_Pip_Inventory 
          ORDER BY Korkrit_Pip_ID_Product ASC";
$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// สร้าง PDF ด้วย TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Inventory Management System');
$pdf->SetTitle($lang[$current_lang]['title']);
$pdf->SetHeaderData('', 0, $lang[$current_lang]['title'], '');

$pdf->SetFont('freeserif', '', 10);
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$html = '<style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid black; padding: 5px; text-align: center; vertical-align: middle; }
    th { background-color: #f2f2f2; font-weight: bold; }
    td { min-height: 60px; height: 60px; } /* Ensure cells have enough height for images */
    img { max-width: 50px; max-height: 50px; object-fit: contain; display: block; margin: 0 auto; }
</style>';

$html .= '<h1 style="text-align:center;">' . htmlspecialchars($lang[$current_lang]['title'], ENT_QUOTES, 'UTF-8') . '</h1>';
$html .= '<table>
    <thead>
        <tr>
            <th width="10%">' . htmlspecialchars($lang[$current_lang]['product_id'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="25%">' . htmlspecialchars($lang[$current_lang]['product_name'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="10%">' . htmlspecialchars($lang[$current_lang]['stock_quantity'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="15%">' . htmlspecialchars($lang[$current_lang]['unit_price'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="20%">' . htmlspecialchars($lang[$current_lang]['image'], ENT_QUOTES, 'UTF-8') . '</th>
            <th width="20%">' . htmlspecialchars($lang[$current_lang]['last_update'], ENT_QUOTES, 'UTF-8') . '</th>
        </tr>
    </thead>
    <tbody>';

foreach ($products as $product) {
    $quantity = intval($product['Korkrit_Pip_Qty_Stock']);
    $color = ($quantity < 3) ? 'color:red; font-weight:bold;' : '';
    $restock = ($quantity < 3) ? ' <span style="color:red;">' . $lang[$current_lang]['restock'] . '</span>' : '';

    $html .= '<tr>';
    // Product ID
    $html .= '<td width="10%" style="text-align:center;">' . htmlspecialchars($product['Korkrit_Pip_ID_Product'], ENT_QUOTES, 'UTF-8') . '</td>';
    // Product Name
    $html .= '<td width="25%" style="text-align:left;">' . htmlspecialchars($product['Korkrit_Pip_Name_Product'], ENT_QUOTES, 'UTF-8') . '</td>';
    // Stock Quantity
    $html .= '<td width="10%" style="text-align:center; ' . $color . '">' . $quantity . $restock . '</td>';
    // Unit Price
    $html .= '<td width="15%" style="text-align:right;">' . number_format($product['Korkrit_Pip_Price_Unit'], 2) . '</td>';
    // Image
    $html .= '<td width="20%" style="text-align:center;">';
    if (!empty($product['Korkrit_Pip_Img_Path']) && file_exists($product['Korkrit_Pip_Img_Path'])) {
        $imagePath = realpath($product['Korkrit_Pip_Img_Path']);
        $html .= '<img src="' . $imagePath . '" alt="Product Image" style="max-width:50px; max-height:50px;" />';
    } else {
        $html .= '-';
    }
    $html .= '</td>';
    // Last Update
    $html .= '<td width="20%" style="text-align:center;">' . htmlspecialchars(date('d/m/Y H:i:s', strtotime($product['LastUpdate']))) . '</td>';
    $html .= '</tr>';
}

$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

$filename = $current_lang === 'th' ? 'inventory_report_th.pdf' : 'inventory_report_en.pdf';
$pdf->Output($filename, 'D');
exit();
?>