<?php
session_start();

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

    if ($username === 'admin' && $password === '1234') {
        $_SESSION['loggedIn'] = true;
        $_SESSION['username'] = $username;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $loginError = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}

if ($isLoggedIn) {
    require 'db_connection.php';

    $itemsPerPage = 6;
    $currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($currentPage - 1) * $itemsPerPage;

    $totalQuery = "SELECT COUNT(*) as total FROM games";
    $totalResult = mysqli_query($conn, $totalQuery);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $totalItems = $totalRow['total'];
    $totalPages = ceil($totalItems / $itemsPerPage);

    $query = "SELECT * FROM games ORDER BY LastUpdate DESC LIMIT $offset, $itemsPerPage";
    $result = mysqli_query($conn, $query);
    $products = [];
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
    }

    $totalProducts = $totalItems;
    $availableProducts = 0;
    $soldProducts = 0;
    $totalValue = 0;

    $statsQuery = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
        SUM(StockQuantity) as total_value
    FROM games";
    $statsResult = mysqli_query($conn, $statsQuery);
    if ($statsResult && mysqli_num_rows($statsResult) > 0) {
        $stats = mysqli_fetch_assoc($statsResult);
        $totalProducts = $stats['total'];
        $availableProducts = $stats['available'];
        $soldProducts = $stats['sold'];
        $totalValue = $stats['total_value'] ?? 0;
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
            --transition-speed: 0.3s;
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
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 1000;
            transition: all var(--transition-speed) ease;
        }

        .navbar.scrolled {
            padding: 0.7rem 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.15);
        }

        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-logo svg {
            width: 32px;
            height: 32px;
            fill: white;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 1.5rem;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: white;
            font-weight: 500;
            font-size: 1rem;
            position: relative;
            padding: 0.5rem 0;
            transition: all var(--transition-speed) ease;
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: white;
            transition: width var(--transition-speed) ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .nav-links .active {
            font-weight: 600;
        }

        .nav-links .active::after {
            width: 100%;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .add-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.6rem 1.2rem;
            background: white;
            color: var(--primary-color);
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            text-decoration: none;
        }

        .add-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .add-btn svg {
            width: 16px;
            height: 16px;
        }

        .logout-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.6rem 1.2rem;
            background: rgba(239, 71, 111, 0.2);
            border-radius: 50px;
            transition: all var(--transition-speed) ease;
        }

        .logout-link:hover {
            background: rgba(239, 71, 111, 0.3);
            transform: translateY(-3px);
        }

        .lang-switch {
            position: relative;
            background: linear-gradient(135deg, #7209b7, #3a0ca3);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            z-index: 1;
        }

        .lang-switch::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #4cc9f0, #4361ee);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
            border-radius: 50px;
        }

        .lang-switch:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        
        .lang-switch:hover::before {
            opacity: 1;
        }

        .lang-switch svg {
            width: 18px;
            height: 18px;
            fill: white;
            filter: drop-shadow(0 2px 3px rgba(0, 0, 0, 0.2));
            animation: rotateSlow 10s linear infinite;
        }
        
        @keyframes rotateSlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .current-lang {
            font-size: 0.95rem;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .lang-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: white;
            color: var(--primary-color);
            font-size: 0.7rem;
            font-weight: 700;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .lang-switch:hover .lang-badge {
            opacity: 1;
            transform: scale(1);
        }

        .mobile-menu-btn {
            display: none;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
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
            .navbar {
                padding: 1rem;
            }

            .nav-links {
                display: none;
            }

            .nav-right {
                flex-direction: column;
                width: 100%;
                gap: 0.5rem;
            }

            .nav-right a, .nav-right button {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 768px) {
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

        @media (max-width: 576px) {
            .search-filter {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        /* ======= Login Page Styling ======= */
.login-container {
    max-width: 450px;
    margin: 120px auto 50px;
    padding: 2.5rem;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3),
                0 0 30px rgba(67, 97, 238, 0.3),
                0 0 100px rgba(114, 9, 183, 0.2);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: fadeIn 0.8s ease-out;
}

/* Neon effect border */
.login-container::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, 
        #ff3399, #4361ee, #7209b7, #3a0ca3, 
        #4cc9f0, #4361ee, #ff3399);
    background-size: 400%;
    border-radius: 22px;
    z-index: -1;
    filter: blur(10px);
    opacity: 0.7;
    animation: glowingBorder 20s linear infinite;
}

@keyframes glowingBorder {
    0% { background-position: 0 0; }
    50% { background-position: 400% 0; }
    100% { background-position: 0 0; }
}

.login-header {
    text-align: center;
    margin-bottom: 2.5rem;
    position: relative;
}

.login-header h1 {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 0.8rem;
    background: linear-gradient(to right, #4cc9f0, #f72585);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    position: relative;
}

.login-header p {
    color: rgba(255, 255, 255, 0.7);
    font-size: 1rem;
}

.login-header::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    border-radius: 3px;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-control {
    position: relative;
}

.form-control input {
    width: 100%;
    padding: 1.2rem 1rem 1.2rem 3rem;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.2);
    color: white;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3),
                inset 0 2px 10px rgba(0, 0, 0, 0.2);
}

.form-control label {
    position: absolute;
    left: 3rem;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255, 255, 255, 0.7);
    pointer-events: none;
    transition: all 0.3s ease;
}

.form-control input:focus ~ label,
.form-control input:not(:placeholder-shown) ~ label {
    top: 12px;
    left: 3rem;
    font-size: 0.7rem;
    color: var(--primary-color);
    font-weight: 600;
}

.form-control i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.7;
    transition: all 0.3s ease;
}

.form-control i svg {
    width: 20px;
    height: 20px;
    stroke: white;
}

.form-control input:focus ~ i {
    opacity: 1;
}

.form-control input:focus ~ i svg {
    stroke: var(--primary-color);
}

.login-btn {
    padding: 1.2rem;
    background: linear-gradient(135deg, #4361ee, #7209b7);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 1px;
    z-index: 1;
    margin-top: 1rem;
}

.login-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #7209b7, #4361ee);
    z-index: -1;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.login-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(114, 9, 183, 0.4);
    letter-spacing: 3px;
}

.login-btn:hover::before {
    opacity: 1;
}

.login-btn:active {
    transform: translateY(-2px);
}

.error-message {
    background: var(--danger-color);
    color: white;
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
    20%, 40%, 60%, 80% { transform: translateX(5px); }
}

.error-message::before {
    content: '⚠️';
    font-size: 1.2rem;
}

.login-form p small {
    font-size: 0.8rem;
}

.login-form p small strong {
    color: #4cc9f0;
    font-weight: 600;
}

/* Login background */
.login-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -2;
}

.login-particles {
    position: absolute;
    width: 100%;
    height: 100%;
}

.particle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    pointer-events: none;
    animation: float-particle 15s infinite ease-in-out;
}

@keyframes float-particle {
    0%, 100% { transform: translate(0, 0); }
    25% { transform: translate(10px, 10px); }
    50% { transform: translate(20px, 0); }
    75% { transform: translate(10px, -10px); }
}

.login-footer {
    text-align: center;
    margin-top: 2rem;
    color: rgba(255, 255, 255, 0.5);
    font-size: 0.8rem;
}

/* Responsive design */
@media (max-width: 576px) {
    .login-container {
        max-width: 90%;
        padding: 1.5rem;
    }
    
    .login-header h1 {
        font-size: 1.8rem;
    }
    
    .form-control input {
        padding: 1rem 1rem 1rem 3rem;
        font-size: 0.9rem;
    }
}
    </style>
</head>
<body class="<?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'thai' : ''; ?>">
<?php if ($isLoggedIn): ?>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="../../HomePage.php" class="nav-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h5v2H7v-2z"/></svg>
            <span class="lang-en">INVENTORY</span>
            <span class="lang-th" style="display: none;">สินค้าคงคลัง</span>
        </a>
        <ul class="nav-links">
            <li>
                <a href="inventory.php" class="active">
                    <span class="lang-en">Store</span>
                    <span class="lang-th" style="display: none;">ร้านค้า</span>
                </a>
            </li>
            <li>
                <a href="edit_product.php">
                    <span class="lang-en">Edit Product</span>
                    <span class="lang-th" style="display: none;">แก้ไขสินค้า</span>
                </a>
            </li>
            <li>
                <a href="Stockgame.php">
                    <span class="lang-en">Show Product</span>
                    <span class="lang-th" style="display: none;">แสดงสินค้า</span>
                </a>
            </li>
        </ul>
        <div class="nav-right">
            <a href="add_product.php" class="add-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span class="lang-en">Add Product</span>
                <span class="lang-th" style="display: none;">เพิ่มสินค้า</span>
            </a>
            <a href="?logout=1" class="logout-link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span class="lang-en">Logout</span>
                <span class="lang-th" style="display: none;">ออกจากระบบ</span>
            </a>
            <button class="lang-switch" id="langSwitch">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-2.29-2.333A17.9 17.9 0 0 1 8.027 13H4.062a8.008 8.008 0 0 0 5.648 6.667zM10.03 13c.151 2.439.848 4.73 1.97 6.752A15.905 15.905 0 0 0 13.97 13h-3.94zm9.908 0h-3.965a17.9 17.9 0 0 1-1.683 6.667A8.008 8.008 0 0 0 19.938 13zM4.062 11h3.965A17.9 17.9 0 0 1 9.71 4.333 8.008 8.008 0 0 0 4.062 11zm5.969 0h3.938A15.905 15.905 0 0 0 12 4.248 15.905 15.905 0 0 0 10.03 11zm4.259-6.667A17.9 17.9 0 0 1 15.973 11h3.965a8.008 8.008 0 0 0-5.648-6.667z"/></svg>
                <span class="current-lang"><?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'TH' : 'EN'; ?></span>
                <span class="lang-badge">2</span>
            </button>
            <button class="mobile-menu-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <div class="header-title">
                <h1>
                    <span class="lang-en">Product Inventory</span>
                    <span class="lang-th" style="display: none;">สินค้าคงคลัง</span>
                </h1>
                <p>
                    <span class="lang-en">Manage your gaming products inventory</span>
                    <span class="lang-th" style="display: none;">จัดการสินค้าในร้านค้าของคุณ</span>
                </p>
            </div>
            <div class="search-filter">
                <div class="search-box">
                    <input type="text" class="search-input" id="searchInput" placeholder=" ">
                    <label for="searchInput">
                        <span class="lang-en">Search products...</span>
                        <span class="lang-th" style="display: none;">ค้นหาสินค้า...</span>
                    </label>
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
                <button class="filter-btn" id="filterBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                    <span class="lang-en">All Products</span>
                    <span class="lang-th" style="display: none;">สินค้าทั้งหมด</span>
                </button>
            </div>
        </div>

        <div class="stats-section">
            <h2 class="stats-title">
                <span class="lang-en">Inventory Statistics</span>
                <span class="lang-th" style="display: none;">สถิติสินค้าคงคลัง</span>
            </h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalProducts; ?></div>
                    <div class="stat-label">
                        <span class="lang-en">Total Products</span>
                        <span class="lang-th" style="display: none;">สินค้าทั้งหมด</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $availableProducts; ?></div>
                    <div class="stat-label">
                        <span class="lang-en">Available</span>
                        <span class="lang-th" style="display: none;">มีสินค้า</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $soldProducts; ?></div>
                    <div class="stat-label">
                        <span class="lang-en">Sold</span>
                        <span class="lang-th" style="display: none;">ขายแล้ว</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($totalValue, 0); ?></div>
                    <div class="stat-label">
                        <span class="lang-en">Total Stock</span>
                        <span class="lang-th" style="display: none;">สินค้าคงเหลือ</span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (count($products) > 0): ?>
        <div class="products-grid" id="productsGrid">
            <?php foreach ($products as $index => $product): ?>
            <div class="product-card" data-status="<?php echo $product['status']; ?>">
                <div class="product-header">
                    <div class="product-status <?php echo $product['status'] === 'available' ? 'status-available' : 'status-sold'; ?>">
                        <span class="lang-en"><?php echo $product['status'] === 'available' ? 'Available' : 'Sold'; ?></span>
                        <span class="lang-th" style="display: none;"><?php echo $product['status'] === 'available' ? 'มีสินค้า' : 'ขายแล้ว'; ?></span>
                    </div>
                    <h3 class="product-title">Game-<?php echo $product['GameID']; ?></h3>
                    <div class="product-game">Game: <?php echo $product['GameName']; ?></div>
                </div>
                <div class="product-body">
                    <div class="product-details">
                        <div class="detail-item">
                            <div class="detail-label">
                                <span class="lang-en">Category</span>
                                <span class="lang-th" style="display: none;">หมวดหมู่</span>
                            </div>
                            <div><?php echo $product['Category']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <span class="lang-en">Release Date</span>
                                <span class="lang-th" style="display: none;">วันที่เผยแพร่</span>
                            </div>
                            <div><?php echo date('d/m/Y', strtotime($product['ReleaseDate'])); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">
                                <span class="lang-en">Last Update</span>
                                <span class="lang-th" style="display: none;">อัปเดตล่าสุด</span>
                            </div>
                            <div><?php echo date('d/m/Y', strtotime($product['LastUpdate'])); ?></div>
                        </div>
                    </div>
                    <div class="product-price">
                        <span class="lang-en">Stock: <?php echo $product['StockQuantity']; ?></span>
                        <span class="lang-th" style="display: none;">สต็อก: <?php echo $product['StockQuantity']; ?></span>
                    </div>
                    <div class="product-actions">
                        <a href="edit_product.php?id=<?php echo $product['GameID']; ?>" class="action-btn edit-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            <span class="lang-en">Edit</span>
                            <span class="lang-th" style="display: none;">แก้ไข</span>
                        </a>
                        <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $product['GameID']; ?>)" class="action-btn delete-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            <span class="lang-en">Delete</span>
                            <span class="lang-th" style="display: none;">ลบ</span>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

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
        <div class="empty-state">
            <div class="empty-icon">📦</div>
            <h2>
                <span class="lang-en">No Products Found</span>
                <span class="lang-th" style="display: none;">ไม่พบสินค้า</span>
            </h2>
            <p class="empty-message">
                <span class="lang-en">Your inventory is empty. Add some products to get started!</span>
                <span class="lang-th" style="display: none;">คลังสินค้าของคุณว่างเปล่า กรุณาเพิ่มสินค้าเพื่อเริ่มต้น!</span>
            </p>
            <a href="add_product.php" class="add-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span class="lang-en">Add Product</span>
                <span class="lang-th" style="display: none;">เพิ่มสินค้า</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
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
        function confirmDelete(id) {
            if (confirm('<?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?' : 'Are you sure you want to delete this product?'; ?>')) {
                window.location.href = 'delete_product.php?id=' + id;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($isLoggedIn): ?>
            const searchInput = document.getElementById('searchInput');
            const productsGrid = document.getElementById('productsGrid');
            const productCards = productsGrid ? productsGrid.querySelectorAll('.product-card') : [];

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                productCards.forEach(card => {
                    const gameId = card.querySelector('.product-title').textContent.toLowerCase().replace('Game-', '');
                    const gameName = card.querySelector('.product-game').textContent.toLowerCase().replace('Game: ', '');
                    const category = card.querySelector('.detail-item:nth-child(1) div:nth-child(2)').textContent.toLowerCase();
                    const releaseDate = card.querySelector('.detail-item:nth-child(2) div:nth-child(2)').textContent.toLowerCase();
                    const lastUpdate = card.querySelector('.detail-item:nth-child(3) div:nth-child(2)').textContent.toLowerCase();
                    
                    if (gameId.includes(searchTerm) || gameName.includes(searchTerm) || category.includes(searchTerm) || releaseDate.includes(searchTerm) || lastUpdate.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

            const filterBtn = document.getElementById('filterBtn');
            let currentFilter = 'all';

            filterBtn.addEventListener('click', function() {
                if (currentFilter === 'all') {
                    currentFilter = 'available';
                    updateFilterButtonText('<?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'มีสินค้า' : 'Available'; ?>');
                    productCards.forEach(card => {
                        if (card.dataset.status === 'available') card.style.display = 'block';
                        else card.style.display = 'none';
                    });
                } else if (currentFilter === 'available') {
                    currentFilter = 'sold';
                    updateFilterButtonText('<?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'ขายแล้ว' : 'Sold'; ?>');
                    productCards.forEach(card => {
                        if (card.dataset.status === 'sold') card.style.display = 'block';
                        else card.style.display = 'none';
                    });
                } else {
                    currentFilter = 'all';
                    updateFilterButtonText('<?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'สินค้าทั้งหมด' : 'All Products'; ?>');
                    productCards.forEach(card => card.style.display = 'block');
                }
            });

            function updateFilterButtonText(text) {
                filterBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                    ${text}
                `;
            }

            productCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // การสลับภาษา
            const langSwitch = document.getElementById('langSwitch');
            const body = document.body;
            const currentLang = document.querySelector('.current-lang');

            // ตั้งค่าภาษาเริ่มต้นจากคุกกี้
            const initialLanguage = document.cookie.split('; ').find(row => row.startsWith('language='));
            if (initialLanguage && initialLanguage.split('=')[1] === 'thai') {
                body.classList.add('thai');
                currentLang.textContent = 'TH';
                document.querySelectorAll('.lang-en').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.lang-th').forEach(el => el.style.display = 'inline-block');
            } else {
                body.classList.remove('thai');
                currentLang.textContent = 'EN';
                document.querySelectorAll('.lang-th').forEach(el => el.style.display = 'none');
                document.querySelectorAll('.lang-en').forEach(el => el.style.display = 'inline-block');
            }

            langSwitch.addEventListener('click', function() {
                if (body.classList.contains('thai')) {
                    body.classList.remove('thai');
                    currentLang.textContent = 'EN';
                    document.querySelectorAll('.lang-th').forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.lang-en').forEach(el => el.style.display = 'inline-block');
                    document.cookie = "language=english; path=/; max-age=" + 60*60*24*30;
                } else {
                    body.classList.add('thai');
                    currentLang.textContent = 'TH';
                    document.querySelectorAll('.lang-en').forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.lang-th').forEach(el => el.style.display = 'inline-block');
                    document.cookie = "language=thai; path=/; max-age=" + 60*60*24*30;
                }
            });

            // Navbar scroll effect
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Mobile menu functionality
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');
            
            mobileMenuBtn.addEventListener('click', function() {
                navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    navLinks.style.display = 'flex';
                } else {
                    navLinks.style.display = 'none';
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>