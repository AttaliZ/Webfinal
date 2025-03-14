<?php
include('db_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $gameName = mysqli_real_escape_string($conn, $_POST['gameName']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $releaseDate = mysqli_real_escape_string($conn, $_POST['releaseDate']);
    $score = mysqli_real_escape_string($conn, $_POST['score']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $developer = mysqli_real_escape_string($conn, $_POST['developer']);
    $platform = mysqli_real_escape_string($conn, $_POST['platform']);

    // จัดการการอัปโหลดไฟล์
    $targetDir = "uploads/"; // โฟลเดอร์ที่เก็บไฟล์
    $defaultFileName = uniqid() . '_' . basename($_FILES["gameImage"]["name"]); // สร้างชื่อไฟล์ไม่ซ้ำ
    $targetFile = $targetDir . $defaultFileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = array("jpg", "jpeg", "png", "gif");

    // ตรวจสอบประเภทไฟล์
    if (!in_array($imageFileType, $allowedTypes)) {
        echo "<p class='error-msg'>Error: Only JPG, JPEG, PNG, and GIF files are allowed.</p>";
        exit;
    }

    // อัปโหลดไฟล์
    if (move_uploaded_file($_FILES["gameImage"]["tmp_name"], $targetFile)) {
        $sql = "INSERT INTO games (GameName, Category, ReleaseDate, Score, Description, Developer, Platform, GameImage, LastUpdate)
                VALUES ('$gameName', '$category', '$releaseDate', '$score', '$description', '$developer', '$platform', '$targetFile', NOW())";

        if (mysqli_query($conn, $sql)) {
            echo "<p class='success-msg'>Game added successfully!</p>";
        } else {
            echo "<p class='error-msg'>Error: " . mysqli_error($conn) . "</p>";
            // ลบไฟล์ที่อัปโหลดแล้วหากเกิดข้อผิดพลาด
            unlink($targetFile);
        }
    } else {
        echo "<p class='error-msg'>Error: Failed to upload image.</p>";
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
        .suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.8);
    border-radius: 0 0 16px 16px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.8);
    border-radius: 0 0 16px 16px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.suggestion-item {
    padding: 10px 20px;
    color: white;
    cursor: pointer;
    transition: background 0.3s ease;
}

.suggestion-item:hover {
    background: rgba(67, 97, 238, 0.5);
}
.tooltip-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.7);
    cursor: help;
    padding: 5px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    transition: color 0.3s ease;
    z-index: 1001; /* เพิ่ม z-index ให้สูงมาก */
}

.tooltip-icon:hover {
    color: white;
}

.tooltip {
    position: absolute;
    top: calc(100% + 5px); /* ปรับระยะห่างจากช่อง */
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    font-size: 0.9rem;
    z-index: 1000; /* อยู่บนสุด */
    max-width: 250px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    display: none;
    white-space: normal;
    line-height: 1.2;
    word-break: break-word;
}

.tooltip::after {
    content: '';
    position: absolute;
    top: -5px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 5px;
    border-style: solid;
    border-color: transparent transparent rgba(0, 0, 0, 0.9) transparent;
    z-index: 1000; /* ต้องเท่ากับ tooltip */
}

/* ปรับ z-index ขององค์ประกอบอื่นให้ต่ำกว่า */
.input-group {
    position: relative;
    margin-bottom: 1rem;
    perspective: 1000px;
    z-index: 10; /* ต่ำกว่า tooltip */
}

.suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.8);
    border-radius: 0 0 16px 16px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 15; /* ต่ำกว่า tooltip แต่สูงกว่า input */
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
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
    <form method="POST" action="" class="form-grid" enctype="multipart/form-data">
        <!-- ช่อง Prompt ใหม่กับไอคอน tooltips -->
        <div class="input-group">
            <input type="text" id="gamePrompt" placeholder=" " autocomplete="off">
            <label for="gamePrompt">
                <span class="lang-en">Prompt Search</span>
                <span class="lang-th" style="display: none;">ค้นหาด้วยคำแนะนำ</span>
            </label>
            <span class="tooltip-icon" id="tooltipIcon">i</span>
            <div id="promptTooltip" class="tooltip" style="display: none;"></div>
            <div id="promptSuggestions" class="suggestions" style="display: none;"></div>
        </div>

        <div class="input-group">
            <input type="text" id="gameName" name="gameName" placeholder=" " required>
            <label for="gameName">
                <span class="lang-en">Game Name</span>
                <span class="lang-th" style="display: none;">ชื่อเกม</span>
            </label>
        </div>

        <div class="input-group">
            <input type="text" id="category" name="category" placeholder=" " required>
            <label for="category">
                <span class="lang-en">Category</span>
                <span class="lang-th" style="display: none;">หมวดหมู่</span>
            </label>
        </div>

        <div class="input-group">
            <input type="date" id="releaseDate" name="releaseDate" placeholder=" " required>
            <label for="releaseDate">
                <span class="lang-en">Release Date</span>
                <span class="lang-th" style="display: none;">วันที่วางจำหน่าย</span>
            </label>
        </div>

        <div class="input-group">
            <input type="number" id="score" name="score" step="0.1" min="0" max="10" placeholder=" " required>
            <label for="score">
                <span class="lang-en">Score (0-10)</span>
                <span class="lang-th" style="display: none;">คะแนน (0-10)</span>
            </label>
        </div>

        <div class="input-group">
            <input type="text" id="description" name="description" placeholder=" " required>
            <label for="description">
                <span class="lang-en">Description</span>
                <span class="lang-th" style="display: none;">คำอธิบาย</span>
            </label>
        </div>

        <div class="input-group">
            <input type="text" id="developer" name="developer" placeholder=" " required>
            <label for="developer">
                <span class="lang-en">Developer</span>
                <span class="lang-th" style="display: none;">ผู้พัฒนา</span>
            </label>
        </div>

        <div class="input-group">
            <input type="text" id="platform" name="platform" placeholder=" " required>
            <label for="platform">
                <span class="lang-en">Platform</span>
                <span class="lang-th" style="display: none;">แพลตฟอร์ม</span>
            </label>
        </div>

        <div class="input-group">
            <input type="file" id="gameImage" name="gameImage" accept="image/*" required>
            <label for="gameImage">
                <span class="lang-en">Upload Game Image</span>
                <span class="lang-th" style="display: none;">อัปโหลดรูปภาพเกม</span>
            </label>
        </div>

        <button type="submit" class="submit-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            <span class="lang-en">Add Game to Inventory</span>
            <span class="lang-th" style="display: none;">เพิ่มเกมเข้าคลัง</span>
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const langToggle = document.getElementById('langToggle');
    const currentLang = document.querySelector('.current-lang');
    const body = document.body;
    
    langToggle.addEventListener('click', function() {
        if (body.classList.contains('thai')) {
            body.classList.remove('thai');
            currentLang.textContent = 'EN';
            document.querySelectorAll('.lang-th').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.lang-en').forEach(el => el.style.display = 'inline-block');
        } else {
            body.classList.add('thai');
            currentLang.textContent = 'TH';
            document.querySelectorAll('.lang-en').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.lang-th').forEach(el => el.style.display = 'inline-block');
        }
    });
    
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) navbar.classList.add('scrolled');
        else navbar.classList.remove('scrolled');
    });
    
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('focus', () => input.parentElement.classList.add('focused'));
        input.addEventListener('blur', () => input.parentElement.classList.remove('focused'));
    });
    
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    mobileMenuBtn.addEventListener('click', function() {
        navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
    });
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) navLinks.style.display = 'flex';
        else navLinks.style.display = 'none';
    });

    // ข้อมูลเกมที่กำหนดล่วงหน้า
    const gameData = [
        {
            GameName: "Call of Duty",
            Category: "FPS",
            ReleaseDate: "2023-11-10",
            Score: "8.5",
            Description: "A fast-paced first-person shooter with intense multiplayer action.",
            Developer: "Activision",
            Platform: "PC, PS5, Xbox"
        },
        {
            GameName: "FIFA 23",
            Category: "Sports",
            ReleaseDate: "2022-09-30",
            Score: "7.8",
            Description: "The latest installment in the FIFA series with updated teams and gameplay.",
            Developer: "EA Sports",
            Platform: "PC, PS4, PS5, Xbox"
        },
        {
            GameName: "The Witcher 3",
            Category: "RPG",
            ReleaseDate: "2015-05-19",
            Score: "9.3",
            Description: "An open-world RPG with a rich story and expansive world.",
            Developer: "CD Projekt",
            Platform: "PC, PS4, Xbox One, Switch"
        }
    ];

    // Autocomplete functionality สำหรับช่อง Prompt
    const gamePromptInput = document.getElementById('gamePrompt');
    const promptSuggestions = document.getElementById('promptSuggestions');
    const tooltipIcon = document.getElementById('tooltipIcon');
    const tooltip = document.getElementById('promptTooltip');
    
    gamePromptInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        if (query.length < 2) {
            promptSuggestions.style.display = 'none';
            return;
        }
        
        const matches = gameData.filter(game => game.GameName.toLowerCase().includes(query));
        
        promptSuggestions.innerHTML = '';
        if (matches.length === 0) {
            promptSuggestions.style.display = 'none';
            return;
        }
        
        promptSuggestions.style.display = 'block';
        matches.forEach(game => {
            const suggestionItem = document.createElement('div');
            suggestionItem.classList.add('suggestion-item');
            suggestionItem.textContent = game.GameName;
            
            suggestionItem.addEventListener('click', function() {
                document.getElementById('gameName').value = game.GameName || '';
                document.getElementById('category').value = game.Category || '';
                document.getElementById('developer').value = game.Developer || '';
                document.getElementById('platform').value = game.Platform || '';
                document.getElementById('releaseDate').value = game.ReleaseDate || '';
                document.getElementById('score').value = game.Score || '';
                document.getElementById('description').value = game.Description || '';
                promptSuggestions.style.display = 'none';
                gamePromptInput.value = '';
            });
            
            promptSuggestions.appendChild(suggestionItem);
        });
    });

    // Tooltips functionality
    tooltipIcon.addEventListener('mouseover', function() {
        const promptList = gameData.map(game => game.GameName).join(', ');
        tooltip.textContent = `Prompts: ${promptList}`;
        tooltip.style.display = 'block';
    });

    tooltipIcon.addEventListener('mouseout', function() {
        tooltip.style.display = 'none';
    });

    document.addEventListener('click', function(e) {
        if (!gamePromptInput.contains(e.target) && !promptSuggestions.contains(e.target)) {
            promptSuggestions.style.display = 'none';
        }
        if (!tooltipIcon.contains(e.target)) {
            tooltip.style.display = 'none';
        }
    });
});
</script>
</body>
</html>