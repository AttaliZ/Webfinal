<?php
require 'db_connection.php';

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query ดึงข้อมูลจากตาราง games รวม StockQuantity
$query = "SELECT GameID, GameName, StockQuantity, Category, ReleaseDate, Score, Description, Developer, Platform, LastUpdate, GameImage 
          FROM games 
          ORDER BY LastUpdate ASC"; // เรียงตามวันที่อัปเดตล่าสุด

$result = $conn->query($query);

// ตรวจสอบผลลัพธ์ของ Query
if ($result === false) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Show Product</title>
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
            overflow-x: hidden;
        }

        body.thai {
            font-family: var(--bs-font-thai);
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* ======= Animated Background ======= */
        .animated-bg {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
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
    margin: 0;
}

.nav-links a {
    text-decoration: none;
    color: white;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-links a:hover, 
.nav-links a.active {
    background: rgba(255, 255, 255, 0.1);
}

.nav-right {
    display: flex;
    align-items: center;
}

.add-btn {
    background: white;
    color: #4361ee;
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

.add-btn svg {
    width: 16px;
    height: 16px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .navbar {
        flex-wrap: wrap;
        gap: 1rem;
        padding: 1rem;
    }

    .nav-links {
        order: 3;
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
    }
}

.add-btn {
    background: white;
    color: #4361ee;
    padding: 0.8rem 1.5rem;
    border-radius: 50px;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.add-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    background: #f8f9fa;
}

.add-btn svg {
    width: 16px;
    height: 16px;
}

/* Language Toggle Button */
.lang-switch {
    position: relative;
    background: linear-gradient(135deg, #7209b7, #3a0ca3);
    color: white;
    padding: 0.8rem 1.5rem;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.lang-switch:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    background: linear-gradient(135deg, #4cc9f0, #4361ee);
}

.lang-switch svg {
    width: 18px;
    height: 18px;
    fill: white;
}

.current-lang {
    font-size: 0.95rem;
    letter-spacing: 1px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .navbar {
        padding: 1rem;
    }
}

@media (max-width: 768px) {
    .navbar {
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.5rem;
        text-align: center;
    }
    
    .nav-logo {
        margin-bottom: 0.5rem;
    }
    
    .nav-links {
        order: 3;
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    
    .nav-right {
        flex-wrap: wrap;
        justify-content: center;
    }
}

        .add-btn {
            background: white;
            color: var(--primary-color);
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .add-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            background: #f8f9fa;
        }

        .add-btn svg {
            width: 16px;
            height: 16px;
        }

        /* Language Toggle Button */
        .lang-switch {
            position: relative;
            background: linear-gradient(135deg, #7209b7, #3a0ca3);
            color: white;
            padding: 0.8rem 1.5rem;
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

        /* ======= Container ======= */
        .container {
            max-width: 90%;
            margin: 120px auto 50px;
            padding: 2.5rem;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3),
                        0 10px 30px rgba(67, 97, 238, 0.2);
            animation: fadeIn 0.8s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            position: relative;
        }

        /* Container glow effect */
        .container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, 
                #4361ee, #7209b7, #3a0ca3, 
                #4cc9f0, #4361ee);
            background-size: 400%;
            border-radius: 22px;
            z-index: -1;
            filter: blur(10px);
            opacity: 0.5;
            animation: glowingBorder 20s linear infinite;
        }

        @keyframes glowingBorder {
            0% { background-position: 0 0; }
            50% { background-position: 400% 0; }
            100% { background-position: 0 0; }
        }

        .container-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2.5rem;
            position: relative;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4361ee, #7209b7);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            transform: rotate(10deg);
        }

        .header-icon svg {
            width: 32px;
            height: 32px;
            fill: white;
        }

        .container h2 {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(to right, #4cc9f0, #f72585);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            margin: 0;
            position: relative;
            z-index: 1;
            letter-spacing: 1px;
        }

        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 0.5rem;
            font-size: 1.1rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ======= Table ======= */
        .table-container {
            margin-bottom: 2.5rem;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 15px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: transparent;
        }

        th, td {
            padding: 18px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: rgba(67, 97, 238, 0.3);
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        th::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        tr {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
        }

        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.02);
        }

        tr:hover {
            background: rgba(67, 97, 238, 0.1);
            transform: translateX(5px);
        }

        /* Enhanced low stock warning */
        tr.low-stock {
            position: relative;
            animation: red-pulse 1.5s infinite !important;
        }

        @keyframes red-pulse {
            0%, 100% {
                background: linear-gradient(90deg, 
                    rgba(239, 71, 111, 0.3), 
                    rgba(239, 71, 111, 0.1)
                );
            }
            50% {
                background: linear-gradient(90deg, 
                    rgba(239, 71, 111, 0.6), 
                    rgba(239, 71, 111, 0.4)
                );
            }
        }

        tr.low-stock td:nth-child(3) {
            font-weight: bold;
            color: white;
            position: relative;
        }

        tr.low-stock td:nth-child(3)::before {
            content: '⚠️ Low';
            position: absolute;
            top: -12px;
            right: 10px;
            background: var(--danger-color);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 700;
            box-shadow: 0 3px 10px rgba(239, 71, 111, 0.5);
            animation: blink 1s infinite;
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Game Image styling */
        td img {
            max-width: 80px;
            max-height: 80px;
            border-radius: 10px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            object-fit: cover;
        }

        td img:hover {
            transform: scale(2.5);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            z-index: 10;
            position: relative;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        /* ======= Buttons ======= */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 2.5rem;
        }

        .btn {
            padding: 14px 28px;
            border-radius: 50px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            z-index: 1;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            transition: opacity 0.4s ease;
            opacity: 0;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef476f, #d90429);
            color: white;
            box-shadow: 0 10px 25px rgba(239, 71, 111, 0.3);
        }

        .btn-danger::before {
            background: linear-gradient(135deg, #d90429, #ef476f);
        }

        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            letter-spacing: 3px;
        }

        .btn:hover::before {
            opacity: 1;
        }

        .btn:active {
            transform: translateY(-2px);
        }

        .btn svg {
            width: 20px;
            height: 20px;
        }

        /* ======= Empty State ======= */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: fadeIn 1s ease;
        }

        .empty-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        .empty-state h3 {
            margin: 1rem 0;
            font-size: 1.8rem;
            background: linear-gradient(to right, #4cc9f0, #f72585);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .empty-state p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
        }

        /* ======= Animations ======= */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                text-shadow: 0 0 0 0 rgba(255, 255, 255, 0.5);
            }
            70% {
                transform: scale(1);
                text-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
            }
            100% {
                transform: scale(0.95);
                text-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        /* ======= Responsive Design ======= */
        @media (max-width: 992px) {
            .container {
                padding: 2rem;
                max-width: 95%;
            }
            
            .navbar {
                padding: 1rem;
            }
            
            .container-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .header-icon {
                margin-right: 0;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-wrap: wrap;
                gap: 1rem;
                padding: 1rem;
            }

            .nav-links {
                order: 3;
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
                gap: 0.5rem;
            }
            
            .nav-right {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
            }

            .table-wrapper {
                overflow-x: auto;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            table {
                min-width: 850px;
            }
            
            .container {
                margin-top: 180px;
            }
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

   <!-- Navbar -->
<nav class="navbar">
    <a href="../../HomePage.php" class="nav-logo">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 3h16a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h5v2H7v-2z"/></svg>
        <span class="lang-en">INVENTORY</span>
        <span class="lang-th" style="display: none;">สินค้าคงคลัง</span>
    </a>
    <ul class="nav-links">
        <li><a href="inventory.php">
            <span class="lang-en">Store</span>
            <span class="lang-th" style="display: none;">ร้านค้า</span>
        </a></li>
        <li><a href="edit_product.php">
            <span class="lang-en">Edit Product</span>
            <span class="lang-th" style="display: none;">แก้ไขสินค้า</span>
        </a></li>
        <li><a href="Stockgame.php" class="active">
            <span class="lang-en">Show Product</span>
            <span class="lang-th" style="display: none;">แสดงสินค้า</span>
        </a></li>
        <li><a href="swapper.php">
            <span class="lang-en">API-DB</span>
            <span class="lang-th" style="display: none;">การเชื่อมต่อ</span>
        </a></li>
    </ul>
    <div class="nav-right">
        <a href="add_product.php" class="add-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span class="lang-en">Add Product</span>
            <span class="lang-th" style="display: none;">เพิ่มสินค้า</span>
        </a>
        <button class="lang-switch" id="langToggle">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-2.29-2.333A17.9 17.9 0 0 1 8.027 13H4.062a8.008 8.008 0 0 0 5.648 6.667zM10.03 13c.151 2.439.848 4.73 1.97 6.752A15.905 15.905 0 0 0 13.97 13h-3.94zm9.908 0h-3.965a17.9 17.9 0 0 1-1.683 6.667A8.008 8.008 0 0 0 19.938 13zM4.062 11h3.965A17.9 17.9 0 0 1 9.71 4.333 8.008 8.008 0 0 0 4.062 11zm5.969 0h3.938A15.905 15.905 0 0 0 12 4.248 15.905 15.905 0 0 0 10.03 11zm4.259-6.667A17.9 17.9 0 0 1 15.973 11h3.965a8.008 8.008 0 0 0-5.648-6.667z"/></svg>
            <span class="current-lang">EN</span>
            <span class="lang-badge">2</span>
        </button>
    </div>
</nav>
    
    <!-- Main Content -->
    <div class="container">
        <div class="container-header">
            <div class="header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 3h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm1 2v14h16V5H4zm2 2h4v3H6V7zm0 5h4v3H6v-3zm0 5h4v3H6v-3zm6-10h6v3h-6V7zm0 5h6v3h-6v-3zm0 5h6v3h-6v-3z"></path></svg>
            </div>
            <div>
                <h2>
                    <span class="lang-en">STOCK DASHBOARD</span>
                    <span class="lang-th" style="display: none;">แดชบอร์ดสินค้าคงคลัง</span>
                </h2>
                <p class="subtitle">
                    <span class="lang-en">Monitor inventory levels and product details</span>
                    <span class="lang-th" style="display: none;">ตรวจสอบระดับสินค้าคงคลังและรายละเอียดผลิตภัณฑ์</span>
                </p>
            </div>
        </div>
        
        <div class="table-container">
            <div class="table-wrapper">
                <?php
                if ($result && $result->num_rows > 0) {
                    echo "<table>
                            <thead>
                                <tr>
                                    <th>
                                        <span class='lang-en'>Game ID</span>
                                        <span class='lang-th' style='display: none;'>รหัสเกม</span>
                                    </th>
                                    <th>
                                        <span class='lang-en'>Game Name</span>
                                        <span class='lang-th' style='display: none;'>ชื่อเกม</span>
                                    </th>
                                    <th>
                                        <span class='lang-en'>Stock Quantity</span>
                                        <span class='lang-th' style='display: none;'>จำนวนคงเหลือ</span>
                                    </th>
                                    <th>
                                        <span class='lang-en'>Category</span>
                                        <span class='lang-th' style='display: none;'>หมวดหมู่</span>
                                    </th>
                                    <th>
                                        <span class='lang-en'>Release Date</span>
                                        <span class='lang-th' style='display: none;'>วันที่วางจำหน่าย</span>
                                    </th>
                                    <th>
                                        <span class='lang-en'>Score</span>
                                        <span class='lang-th' style='display: none;'>คะแนน</span>
                                    </th>
                                    <th>
                                        <span class='lang-en'>Developer</span>
                                        <span class='lang-th' style='display: none;'>ผู้พัฒนา</span>
                                    </th>
                                    <th>
                                        <span class='lang-en'>Platform</span>
                                        <span class='lang-th' style='display: none;'>แพลตฟอร์ม</span>
                                    </th>
                                    <th>
                                        <span class='lang-en'>Last Update</span>
                                        <span class='lang-th' style='display: none;'>อัปเดตล่าสุด</span>
                                    </th>
                                    <th>
                                        <span class='lang-en'>Game Image</span>
                                        <span class='lang-th' style='display: none;'>รูปภาพเกม</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>";

                    while ($row = $result->fetch_assoc()) {
                        // ตรวจสอบ StockQuantity ต่ำกว่า 3 เพื่อตั้ง class low-stock
                        $rowClass = ($row['StockQuantity'] < 3) ? "low-stock" : "";
                        
                        // จำกัดความยาวของ Description ให้อ่านง่ายในตาราง
                        $shortDesc = substr($row['Description'], 0, 100);
                        if (strlen($row['Description']) > 100) {
                            $shortDesc .= '...';
                        }
                        
                        echo "<tr class='{$rowClass}'>
                                <td>{$row['GameID']}</td>
                                <td>{$row['GameName']}</td>
                                <td>{$row['StockQuantity']}</td>
                                <td>{$row['Category']}</td>
                                <td>{$row['ReleaseDate']}</td>
                                <td>{$row['Score']}</td>
                                <td>{$row['Developer']}</td>
                                <td>{$row['Platform']}</td>
                                <td>{$row['LastUpdate']}</td>
                                <td><img src='{$row['GameImage']}' alt='{$row['GameName']}'></td>
                            </tr>";
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<div class='empty-state'>
                            <div class='empty-icon'>🎮</div>
                            <h3>
                                <span class='lang-en'>No Game Data Available</span>
                                <span class='lang-th' style='display: none;'>ไม่มีข้อมูลเกมที่พร้อมใช้งาน</span>
                            </h3>
                            <p>
                                <span class='lang-en'>There are no games in the database.</span>
                                <span class='lang-th' style='display: none;'>ไม่มีเกมในฐานข้อมูล</span>
                            </p>
                          </div>";
                }
                ?>
            </div>
        </div>
        
        <div class="action-buttons">
            <form action="generate_report.php" method="post">
                <button type="submit" class="btn btn-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span class="lang-en">Download PDF Report</span>
                    <span class="lang-th" style="display: none;">ดาวน์โหลดรายงาน PDF</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Language Toggle Functionality
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

            // Enhanced Low Stock Animation
            const lowStockRows = document.querySelectorAll('tr.low-stock');
            lowStockRows.forEach(row => {
                // Add pulsing border effect
                setInterval(() => {
                    row.style.boxShadow = '0 0 15px rgba(239, 71, 111, 0.7)';
                    setTimeout(() => {
                        row.style.boxShadow = 'none';
                    }, 500);
                }, 1000);
            });
            
            // Image hover effect improvement
            const gameImages = document.querySelectorAll('td img');
            gameImages.forEach(img => {
                img.addEventListener('mouseover', function() {
                    this.style.zIndex = '100';
                });
                img.addEventListener('mouseout', function() {
                    this.style.zIndex = '1';
                });
            });
        });
    </script>
</body>
</html>