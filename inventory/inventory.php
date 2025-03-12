<?php
session_start(); // เริ่ม session

// ตรวจสอบการ login
$isLoggedIn = isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true;

// จัดการการ logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// จัดการการ login
if (isset($_POST['login_submit'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // ตรวจสอบความถูกต้องของ username/password
    if ($username === 'admin' && $password === '1234') {
        $_SESSION['loggedIn'] = true;
        $_SESSION['username'] = $username;
        
        // Redirect เพื่อป้องกันการส่งฟอร์มซ้ำ
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $loginError = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}

// หากยังไม่ได้ login จะไม่ดึงข้อมูลจากฐานข้อมูล
if ($isLoggedIn) {
    // เชื่อมต่อฐานข้อมูล
    $servername = getenv('DB_SERVER') ?: '158.108.101.153';
    $username = getenv('DB_USERNAME') ?: 'std6630202015';
    $password = getenv('DB_PASSWORD') ?: 'g3#Vjp8L';
    $dbname = getenv('DB_NAME') ?: 'it_std6630202015';

    // สร้างการเชื่อมต่อ
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    mysqli_set_charset($conn, "utf8");

    // การตั้งค่าการแบ่งหน้า
    $itemsPerPage = 6; // จำนวนรายการต่อหน้า
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;

    // คำนวณจำนวนหน้าทั้งหมด
    $totalQuery = "SELECT COUNT(*) as total FROM Accounts";
    $totalResult = mysqli_query($conn, $totalQuery);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalItems = $totalRow['total'];
    $totalPages = ceil($totalItems / $itemsPerPage);

    // ดึงข้อมูลสินค้าตามการแบ่งหน้า
    $query = "SELECT * FROM Accounts ORDER BY created_at DESC LIMIT $offset, $itemsPerPage";
    $result = mysqli_query($conn, $query);

    // เก็บข้อมูลในตัวแปร
    $products = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }

    // คำนวณสถิติ
    $totalProducts = $totalItems;
    $availableProducts = 0;
    $soldProducts = 0;
    $totalValue = 0;

    $statsQuery = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
        SUM(price) as total_value
    FROM Accounts";

    $statsResult = mysqli_query($conn, $statsQuery);
    if ($statsResult && mysqli_num_rows($statsResult) > 0) {
        $stats = mysqli_fetch_assoc($statsResult);
        $totalProducts = $stats['total'];
        $availableProducts = $stats['available'];
        $soldProducts = $stats['sold'];
        $totalValue = $stats['total_value'];
    }

    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #7209b7;
            --success-color: #06d6a0;
            --danger-color: #ef476f;
            --warning-color: #ffd166;
            --info-color: #118ab2;
            --dark-color: #073b4c;
            --light-color: #f8f9fa;
            --bs-font-sans-serif: 'Poppins', sans-serif;
            --bs-font-thai: 'Prompt', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background: linear-gradient(-45deg, #4361ee, #3a0ca3, #4cc9f0, #4895ef);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            color: #fff;
            min-height: 100vh;
            position: relative;
        }

        body.thai {
            font-family: var(--bs-font-thai);
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* ======= Navbar ======= */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
        }

        .nav-logo svg {
            width: 28px;
            height: 28px;
            fill: white;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 1.5rem;
        }

        .nav-links a {
            text-decoration: none;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover, .nav-links a.active {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
        }

        .add-btn {
            background: white;
            color: var(--primary-color);
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .lang-switch {
            background: linear-gradient(135deg, #7209b7, #3a0ca3);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .lang-switch:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .lang-switch svg {
            width: 18px;
            height: 18px;
            fill: white;
        }

        /* ======= Login Form ======= */
        .login-container {
            max-width: 400px;
            margin: 150px auto;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.8s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #4cc9f0, #f72585);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.7);
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-control {
            position: relative;
        }

        .form-control label {
            position: absolute;
            left: 45px;
            top: 17px;
            color: rgba(255, 255, 255, 0.7);
            pointer-events: none;
            transition: all 0.3s ease;
        }

        .form-control input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3);
        }

        .form-control input:focus + label,
        .form-control input:not(:placeholder-shown) + label {
            transform: translateY(-24px) translateX(-30px);
            font-size: 0.8rem;
            color: var(--primary-color);
        }

        .form-control i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .form-control i svg {
            width: 18px;
            height: 18px;
            stroke: rgba(255, 255, 255, 0.7);
        }

        .login-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .error-message {
            background: rgba(239, 71, 111, 0.2);
            color: var(--danger-color);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        /* ======= User Menu ======= */
        .user-menu {
            position: relative;
        }

        .user-button {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .user-icon {
            width: 32px;
            height: 32px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .user-name {
            font-weight: 500;
        }

        .logout-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: rgba(239, 71, 111, 0.2);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .logout-link:hover {
            background: rgba(239, 71, 111, 0.3);
            transform: translateY(-3px);
        }

        /* ======= Main Container ======= */
        .container {
            max-width: 1200px;
            margin: 120px auto 50px;
            padding: 0 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
        }

        .header-title h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .header-title p {
            color: rgba(255, 255, 255, 0.8);
        }

        .search-filter {
            display: flex;
            gap: 1rem;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            border-radius: 8px;
            width: 250px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            width: 300px;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .filter-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* ======= Products Grid ======= */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .product-card {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .product-header {
            position: relative;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .product-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-available {
            background: rgba(6, 214, 160, 0.2);
            color: var(--success-color);
        }

        .status-sold {
            background: rgba(239, 71, 111, 0.2);
            color: var(--danger-color);
        }

        .product-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .product-game {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .product-body {
            padding: 1.5rem;
        }

        .product-details {
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
        }

        .detail-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .product-price {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            margin: 1.5rem 0;
        }

        .product-actions {
            display: flex;
            gap: 1rem;
        }

        .action-btn {
            flex: 1;
            padding: 0.8rem;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .edit-btn {
            background: rgba(76, 201, 240, 0.1);
            color: var(--info-color);
            border: 1px solid rgba(76, 201, 240, 0.3);
        }

        .edit-btn:hover {
            background: rgba(76, 201, 240, 0.2);
        }

        .delete-btn {
            background: rgba(239, 71, 111, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(239, 71, 111, 0.3);
        }

        .delete-btn:hover {
            background: rgba(239, 71, 111, 0.2);
        }

        /* ======= Pagination ======= */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 3rem;
        }

        .page-item {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .page-item:hover {
            background: rgba(0, 0, 0, 0.3);
            transform: translateY(-3px);
        }

        .page-item.active {
            background: var(--primary-color);
            font-weight: 600;
        }

        .page-item.disabled {
            opacity: 0.5;
            pointer-events: none;
        }

        /* ======= Stats Section ======= */
        .stats-section {
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .stats-title {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        /* ======= Empty State ======= */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.7;
        }

        .empty-message {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1.5rem;
        }

        /* ======= Responsive Design ======= */
        @media (max-width: 992px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
            }

            .search-filter {
                width: 100%;
            }

            .search-box {
                flex: 1;
            }

            .search-input {
                width: 100%;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .nav-links {
                order: 3;
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 576px) {
            .search-filter {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php if ($isLoggedIn): ?>
    <!-- Navbar - แสดงเฉพาะเมื่อล็อกอินแล้วเท่านั้น -->
    <nav class="navbar">
        <a href="../../HomePage.php" class="nav-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h5v2H7v-2z"/></svg>
            <span>INVENTORY</span>
        </a>
        <ul class="nav-links">
            <li><a href="inventory.php" class="active">Store</a></li>
            <li><a href="edit_product.php">Edit Product</a></li>
            <li><a href="Stockgame.php">Show Product</a></li>
            <li><a href="swapper.php">API-DB</a></li>
        </ul>
        <div class="nav-right" style="display: flex; gap: 1rem;">
            <a href="add_product.php" class="add-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Product
            </a>
            <div class="user-menu">
                <button class="user-button">
                    <div class="user-icon"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
                    <div class="user-name"><?php echo $_SESSION['username']; ?></div>
                </button>
            </div>
            <a href="?logout=1" class="logout-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Logout
            </a>
        </div>
    </nav>

    <!-- Main Content เมื่อล็อกอินแล้ว -->
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="header-title">
                <h1>Product Inventory</h1>
                <p>Manage your gaming accounts inventory</p>
            </div>
            <div class="search-filter">
                <div class="search-box">
                    <input type="text" class="search-input" id="searchInput" placeholder="Search products...">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <button class="filter-btn" id="filterBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                    All Products
                </button>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <h2 class="stats-title">Inventory Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalProducts; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $availableProducts; ?></div>
                    <div class="stat-label">Available</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $soldProducts; ?></div>
                    <div class="stat-label">Sold</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">฿<?php echo number_format($totalValue, 2); ?></div>
                    <div class="stat-label">Total Value</div>
                </div>
            </div>
        </div>

        <?php if (count($products) > 0): ?>
        <!-- Products Grid -->
        <div class="products-grid" id="productsGrid">
            <?php foreach ($products as $index => $product): ?>
            <div class="product-card" data-status="<?php echo $product['status']; ?>">
                <div class="product-header">
                    <div class="product-status <?php echo $product['status'] === 'available' ? 'status-available' : 'status-sold'; ?>">
                        <?php echo $product['status'] === 'available' ? 'Available' : 'Sold'; ?>
                    </div>
                    <h3 class="product-title">ACC-<?php echo $product['account_id']; ?></h3>
                    <div class="product-game">Game: <?php echo $product['game_id']; ?></div>
                </div>
                <div class="product-body">
                    <div class="product-details">
                        <div class="detail-item">
                            <div class="detail-label">Username</div>
                            <div><?php echo $product['username']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Details</div>
                            <div><?php echo substr($product['details'], 0, 30); ?><?php echo strlen($product['details']) > 30 ? '...' : ''; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Date Added</div>
                            <div><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="product-price">
                        ฿<?php echo number_format($product['price'], 2); ?>
                    </div>
                    <div class="product-actions">
                        <a href="edit_product.php?id=<?php echo $product['account_id']; ?>" class="action-btn edit-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            Edit
                        </a>
                        <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $product['account_id']; ?>)" class="action-btn delete-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            Delete
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?>" class="page-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </a>
            <?php else: ?>
                <span class="page-item disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </span>
            <?php endif; ?>
            
            <?php
            // Show pagination numbers
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $startPage + 4);
            
            if ($endPage - $startPage < 4 && $totalPages > 4) {
                $startPage = max(1, $endPage - 4);
            }
            
            for ($i = $startPage; $i <= $endPage; $i++):
            ?>
                <a href="?page=<?php echo $i; ?>" class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>" class="page-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </a>
            <?php else: ?>
                <span class="page-item disabled">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h2>No Products Found</h2>
            <p class="empty-message">Your inventory is empty. Add some products to get started!</p>
            <a href="add_product.php" class="add-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add Product
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Login Form -->
    <div class="login-container">
        <div class="login-header">
            <h1>Inventory Login</h1>
            <p>Please enter your credentials to access inventory</p>
        </div>

        <?php if (isset($loginError)): ?>
        <div class="error-message"><?php echo $loginError; ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="">
            <div class="form-control">
                <input type="text" id="username" name="username" placeholder=" " required>
                <label for="username">Username</label>
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </i>
            </div>
            <div class="form-control">
                <input type="password" id="password" name="password" placeholder=" " required>
                <label for="password">Password</label>
                <i>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </i>
            </div>
            <button type="submit" name="login_submit" class="login-btn">Login</button>
            <p style="text-align: center; margin-top: 1rem; color: rgba(255, 255, 255, 0.7);">
                <small>Demo credentials: username <strong>admin</strong>, password <strong>1234</strong></small>
            </p>
        </form>
    </div>
    <?php endif; ?>

    <script>
        // ฟังก์ชันยืนยันการลบสินค้า
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this product?')) {
                window.location.href = 'delete_product.php?id=' + id;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($isLoggedIn): ?>
            // ฟังก์ชันค้นหาสินค้า
            const searchInput = document.getElementById('searchInput');
            const productsGrid = document.getElementById('productsGrid');
            const productCards = productsGrid.querySelectorAll('.product-card');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                productCards.forEach(card => {
                    const gameId = card.querySelector('.product-game').textContent.toLowerCase();
                    const productTitle = card.querySelector('.product-title').textContent.toLowerCase();
                    const productDetails = card.querySelectorAll('.detail-item');
                    let username = '';
                    let details = '';
                    
                    productDetails.forEach(detail => {
                        const label = detail.querySelector('.detail-label').textContent.toLowerCase();
                        if (label.includes('username')) {
                            username = detail.textContent.toLowerCase();
                        } else if (label.includes('details')) {
                            details = detail.textContent.toLowerCase();
                        }
                    });
                    
                    if (gameId.includes(searchTerm) || 
                        productTitle.includes(searchTerm) || 
                        username.includes(searchTerm) || 
                        details.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
            
            // ฟังก์ชันกรองสินค้า
            const filterBtn = document.getElementById('filterBtn');
            let currentFilter = 'all';
            
            filterBtn.addEventListener('click', function() {
                if (currentFilter === 'all') {
                    // แสดงเฉพาะสินค้าที่พร้อมขาย
                    currentFilter = 'available';
                    updateFilterButtonText('Available');
                    
                    productCards.forEach(card => {
                        if (card.dataset.status === 'available') {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                } else if (currentFilter === 'available') {
                    // แสดงเฉพาะสินค้าที่ขายแล้ว
                    currentFilter = 'sold';
                    updateFilterButtonText('Sold');
                    
                    productCards.forEach(card => {
                        if (card.dataset.status === 'sold') {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                } else {
                    // แสดงสินค้าทั้งหมด
                    currentFilter = 'all';
                    updateFilterButtonText('All Products');
                    
                    productCards.forEach(card => {
                        card.style.display = 'block';
                    });
                }
            });
            
            function updateFilterButtonText(text) {
                filterBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                    ${text}
                `;
            }
            
            // เพิ่มแอนิเมชันให้กับการ์ดสินค้า
            productCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>