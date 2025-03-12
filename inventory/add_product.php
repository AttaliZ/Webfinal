<?php
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $account_id = mysqli_real_escape_string($conn, $_POST['account_id']);
    $game_id = mysqli_real_escape_string($conn, $_POST['game_id']);
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO Accounts (account_id, game_id, user_id, username, password, details, price, status, created_at)
            VALUES ('$account_id', '$game_id', '$user_id', '$username', '$password', '$details', '$price', '$status', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo "<p class='success-msg'>Product added successfully!</p>";
    } else {
        echo "<p class='error-msg'>Error: " . mysqli_error($conn) . "</p>";
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
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
            background: #f5f5f5;
            color: #333;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body.thai {
            font-family: var(--bs-font-thai);
        }

        /* ======= Animated Background ======= */
        .animated-bg {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            background: linear-gradient(-45deg, #4361ee, #3a0ca3, #4cc9f0, #4895ef);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }

        .animated-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.8;
        }

        .glass-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            z-index: -1;
        }

        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        /* ======= Floating Elements ======= */
        .floating-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            animation: float 8s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            left: 20%;
            animation-delay: 1s;
            background: rgba(255, 255, 255, 0.08);
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            top: 20%;
            right: 15%;
            animation-delay: 2s;
            background: rgba(255, 255, 255, 0.05);
        }

        .shape:nth-child(4) {
            width: 60px;
            height: 60px;
            bottom: 15%;
            right: 10%;
            animation-delay: 3s;
            background: rgba(255, 255, 255, 0.07);
        }

        .shape:nth-child(5) {
            width: 150px;
            height: 150px;
            bottom: 30%;
            left: 5%;
            animation-delay: 4s;
            background: rgba(255, 255, 255, 0.03);
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        /* ======= Modern Navbar ======= */
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

        /* ======= Creative Form Container ======= */
        .form-container {
            max-width: 1100px;
            margin: 120px auto 50px;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3),
                        0 0 30px rgba(67, 97, 238, 0.3),
                        0 0 100px rgba(114, 9, 183, 0.2);
            display: flex;
            flex-direction: column;
            position: relative;
            animation: fadeIn 1s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Neon effect border */
        .form-container::before {
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
            border-radius: 32px;
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

        .form-header {
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            text-align: center;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-header-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.2;
            background: 
                radial-gradient(circle at 20% 20%, rgba(76, 201, 240, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 60%, rgba(114, 9, 183, 0.3) 0%, transparent 50%);
        }

        .form-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #4cc9f0, #f72585);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
        }

        .form-subtitle {
            color: rgba(255, 255, 255, 0.8);
            margin-top: 0.5rem;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
            max-width: 600px;
        }

        .form-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4361ee, #7209b7);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            position: relative;
            z-index: 1;
            margin-bottom: 1.5rem;
            transform: rotate(10deg);
        }

        .form-icon svg {
            width: 42px;
            height: 42px;
            fill: white;
        }

        .form-body {
            padding: 3rem;
            position: relative;
            overflow: hidden;
        }

        .form-body::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(76, 201, 240, 0.15) 0%, transparent 70%);
            top: -250px;
            left: -250px;
            z-index: -1;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            position: relative;
        }

        /* Futuristic form elements */
        .input-group {
            position: relative;
            margin-bottom: 1rem;
            perspective: 1000px;
        }

        .input-group label {
            position: absolute;
            left: 20px;
            top: 20px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            pointer-events: none;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 40px 20px 15px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            font-size: 1.1rem;
            background: rgba(0, 0, 0, 0.2);
            transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
            color: white;
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.2);
            transform-style: preserve-3d;
            transform: translateZ(0);
            backdrop-filter: blur(5px);
        }

        .input-group input::placeholder {
            color: transparent;
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.3), 
                        inset 0 2px 10px rgba(0, 0, 0, 0.2);
            transform: translateZ(10px);
        }

        .input-group input:focus + label,
        .input-group select:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            top: 12px;
            left: 20px;
            font-size: 0.7rem;
            color: var(--primary-color);
            font-weight: 600;
        }

        .input-group::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 10px;
            right: 10px;
            height: 10px;
            background: linear-gradient(to right, 
                rgba(76, 201, 240, 0.3), 
                rgba(114, 9, 183, 0.3));
            filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 50%;
            z-index: -1;
        }

        .input-group:hover::after {
            opacity: 1;
        }

        .input-group select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 20px;
        }

        .submit-btn {
            grid-column: 1 / -1;
            padding: 1.2rem;
            background: linear-gradient(135deg, #4361ee, #7209b7);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 1.5rem;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
            z-index: 1;
        }

        .submit-btn::before {
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

        .submit-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(114, 9, 183, 0.4);
            letter-spacing: 3px;
        }
        
        .submit-btn:hover::before {
            opacity: 1;
        }

        .submit-btn:active {
            transform: translateY(-2px);
        }

        .submit-btn svg {
            width: 24px;
            height: 24px;
        }

        /* ======= Messages ======= */
        .success-msg {
            background: var(--success-color);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
        }

        .error-msg {
            background: var(--danger-color);
            color: white;
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ======= Responsive Design ======= */
        @media screen and (max-width: 992px) {
            .form-container {
                margin: 100px 20px 50px;
            }
        }

        @media screen and (max-width: 768px) {
            .navbar {
                padding: 1rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .form-header {
                padding: 1.5rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .form-body {
                padding: 1.5rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }

        /* ======= Mobile Menu ======= */
        .mobile-menu-btn {
            display: none;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* ======= Animations and Effects ======= */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.5);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        .hover-scale {
            transition: transform var(--transition-speed) ease;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <!-- Animated Background Elements -->
    <div class="animated-bg"></div>
    <div class="glass-overlay"></div>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <!-- Modern Navbar -->
    <nav class="navbar">
        <a href="../../HomePage.php" class="nav-logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h5v2H7v-2z"/></svg>
            <span class="lang-en">INVENTORY</span>
            <span class="lang-th" style="display: none;">สินค้าคงคลัง</span>
        </a>
        <ul class="nav-links">
            <li>
                <a href="inventory.php">
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
            <li>
                <a href="swapper.php">
                    <span class="lang-en">API-DB</span>
                    <span class="lang-th" style="display: none;">การเชื่อมต่อ</span>
                </a>
            </li>
        </ul>
        <div class="nav-right">
            <a href="add_product.php" class="add-btn active">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span class="lang-en">Add Product</span>
                <span class="lang-th" style="display: none;">เพิ่มสินค้า</span>
            </a>
            <button class="lang-switch" id="langToggle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-2.29-2.333A17.9 17.9 0 0 1 8.027 13H4.062a8.008 8.008 0 0 0 5.648 6.667zM10.03 13c.151 2.439.848 4.73 1.97 6.752A15.905 15.905 0 0 0 13.97 13h-3.94zm9.908 0h-3.965a17.9 17.9 0 0 1-1.683 6.667A8.008 8.008 0 0 0 19.938 13zM4.062 11h3.965A17.9 17.9 0 0 1 9.71 4.333 8.008 8.008 0 0 0 4.062 11zm5.969 0h3.938A15.905 15.905 0 0 0 12 4.248 15.905 15.905 0 0 0 10.03 11zm4.259-6.667A17.9 17.9 0 0 1 15.973 11h3.965a8.008 8.008 0 0 0-5.648-6.667z"/></svg>
                <span class="current-lang">EN</span>
                <span class="lang-badge">2</span>
            </button>
            <button class="mobile-menu-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
    </nav>

    <!-- Creative Form Container -->
    <div class="form-container">
        <div class="form-header">
            <div class="form-header-bg"></div>
            <div class="form-icon pulse">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M2 20h20v2H2v-2zm2-8h16v5H4v-5zm16-8H4v5h16V4z"/></svg>
            </div>
            <h1 class="form-title">
                <span class="lang-en">Add New Product</span>
                <span class="lang-th" style="display: none;">เพิ่มสินค้าใหม่</span>
            </h1>
            <p class="form-subtitle">
                <span class="lang-en">Enter product details to add to our inventory system. All fields are required for successful submission.</span>
                <span class="lang-th" style="display: none;">กรอกรายละเอียดสินค้าเพื่อเพิ่มเข้าสู่ระบบคลังสินค้า จำเป็นต้องกรอกข้อมูลทุกช่อง</span>
            </p>
        </div>

        <div class="form-body">
            <form method="POST" action="" class="form-grid">
                <div class="input-group">
                    <input type="text" id="account_id" name="account_id" placeholder=" " required>
                    <label for="account_id">
                        <span class="lang-en">Account ID</span>
                        <span class="lang-th" style="display: none;">รหัสบัญชี</span>
                    </label>
                </div>

                <div class="input-group">
                    <input type="text" id="game_id" name="game_id" placeholder=" " required>
                    <label for="game_id">
                        <span class="lang-en">Game ID</span>
                        <span class="lang-th" style="display: none;">รหัสเกม</span>
                    </label>
                </div>

                <div class="input-group">
                    <input type="text" id="user_id" name="user_id" placeholder=" " required>
                    <label for="user_id">
                        <span class="lang-en">User ID</span>
                        <span class="lang-th" style="display: none;">รหัสผู้ใช้</span>
                    </label>
                </div>

                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder=" " required>
                    <label for="username">
                        <span class="lang-en">Username</span>
                        <span class="lang-th" style="display: none;">ชื่อผู้ใช้</span>
                    </label>
                </div>

                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder=" " required>
                    <label for="password">
                        <span class="lang-en">Password</span>
                        <span class="lang-th" style="display: none;">รหัสผ่าน</span>
                    </label>
                </div>

                <div class="input-group">
                    <input type="text" id="details" name="details" placeholder=" " required>
                    <label for="details">
                        <span class="lang-en">Product Details</span>
                        <span class="lang-th" style="display: none;">รายละเอียดสินค้า</span>
                    </label>
                </div>

                <div class="input-group">
                    <input type="number" id="price" name="price" step="0.01" placeholder=" " required>
                    <label for="price">
                        <span class="lang-en">Price</span>
                        <span class="lang-th" style="display: none;">ราคา</span>
                    </label>
                </div>

                <div class="input-group">
                    <select id="status" name="status" required>
                        <option value="" disabled selected></option>
                        <option value="available" class="lang-en">Available</option>
                        <option value="available" class="lang-th" style="display: none;">พร้อมขาย</option>
                        <option value="sold" class="lang-en">Sold</option>
                        <option value="sold" class="lang-th" style="display: none;">ขายแล้ว</option>
                    </select>
                    <label for="status">
                        <span class="lang-en">Status</span>
                        <span class="lang-th" style="display: none;">สถานะ</span>
                    </label>
                </div>

                <button type="submit" class="submit-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    <span class="lang-en">Add Product to Inventory</span>
                    <span class="lang-th" style="display: none;">เพิ่มสินค้าเข้าคลัง</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Language Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const langToggle = document.getElementById('langToggle');
            const currentLang = document.querySelector('.current-lang');
            const body = document.body;
            
            langToggle.addEventListener('click', function() {
                if (body.classList.contains('thai')) {
                    // Switch to English
                    body.classList.remove('thai');
                    currentLang.textContent = 'EN';
                    
                    // Hide Thai elements, show English elements
                    document.querySelectorAll('.lang-th').forEach(el => {
                        el.style.display = 'none';
                    });
                    document.querySelectorAll('.lang-en').forEach(el => {
                        el.style.display = 'inline-block';
                    });
                } else {
                    // Switch to Thai
                    body.classList.add('thai');
                    currentLang.textContent = 'TH';
                    
                    // Hide English elements, show Thai elements
                    document.querySelectorAll('.lang-en').forEach(el => {
                        el.style.display = 'none';
                    });
                    document.querySelectorAll('.lang-th').forEach(el => {
                        el.style.display = 'inline-block';
                    });
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
            
            // Form input animation
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', () => {
                    input.parentElement.classList.remove('focused');
                });
            });
            
            // Mobile menu functionality
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');
            
            mobileMenuBtn.addEventListener('click', function() {
                navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
            });
            
            // Adjust for screen size changes
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    navLinks.style.display = 'flex';
                } else {
                    navLinks.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>