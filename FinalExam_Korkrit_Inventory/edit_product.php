<?php
require 'db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check database connection
if (!$conn) {
    die("<p class='error-msg'>Database connection failed: " . mysqli_connect_error() . "</p>");
}

// Initialize $search_query with an empty string
$search_query = '';

// Handle bulk update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_all'])) {
    $product_ids = $_POST['product_id'];
    $product_names = $_POST['product_name'];
    $qty_stocks = $_POST['qty_stock'];
    $price_units = $_POST['price_unit'];
    $img_paths = $_POST['current_image'];

    // Get search query for redirect
    $redirect_search = "";
    if (isset($_POST['search_query']) && !empty($_POST['search_query'])) {
        $redirect_search = "&search=" . urlencode($_POST['search_query']);
    }

    // Begin transaction for bulk update
    mysqli_begin_transaction($conn);

    try {
        foreach ($product_ids as $index => $product_id) {
            $product_id = mysqli_real_escape_string($conn, $product_id);
            $product_name = mysqli_real_escape_string($conn, $product_names[$index]);
            $qty_stock = intval($qty_stocks[$index]);
            $price_unit = floatval($price_units[$index]);
            $current_image = mysqli_real_escape_string($conn, $img_paths[$index]);

            // Validate required fields
            if (empty($product_id) || empty($product_name) || !is_numeric($qty_stock) || !is_numeric($price_unit)) {
                throw new Exception("All fields are required and Qty Stock/Price Unit must be numbers for Product ID: $product_id");
            }

            if ($qty_stock < 0 || $qty_stock > 1000) {
                throw new Exception("Qty Stock must be between 0 and 1000 for Product ID: $product_id");
            }

            if ($price_unit < 0) {
                throw new Exception("Price Unit cannot be negative for Product ID: $product_id");
            }

            // Handle image upload for this row
            $new_img_path = $current_image;
            if (isset($_FILES['game_image']['name'][$index]) && $_FILES['game_image']['error'][$index] == UPLOAD_ERR_OK) {
                $file_name = $_FILES['game_image']['name'][$index];
                $file_tmp = $_FILES['game_image']['tmp_name'][$index];
                $file_size = $_FILES['game_image']['size'][$index];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
                $max_size = 5 * 1024 * 1024; // 5MB

                if (!in_array($file_ext, $allowed_ext)) {
                    throw new Exception("Only JPG, JPEG, PNG, and GIF files are allowed for Product ID: $product_id");
                }

                if ($file_size > $max_size) {
                    throw new Exception("File size exceeds 5MB limit for Product ID: $product_id");
                }

                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $new_file_name = uniqid() . '.' . $file_ext;
                $new_img_path = $upload_dir . $new_file_name;

                if (!move_uploaded_file($file_tmp, $new_img_path)) {
                    throw new Exception("Failed to upload image for Product ID: $product_id");
                }

                if (file_exists($current_image) && $current_image !== $new_img_path) {
                    unlink($current_image);
                }
            }

            // Update query
            $update_query = "UPDATE FinalExam_Korkrit_Pip_Inventory SET 
                            Korkrit_Pip_Name_Product = '$product_name', 
                            Korkrit_Pip_Qty_Stock = $qty_stock, 
                            Korkrit_Pip_Price_Unit = $price_unit, 
                            Korkrit_Pip_Img_Path = '$new_img_path'
                            WHERE Korkrit_Pip_ID_Product = '$product_id'";

            if (!mysqli_query($conn, $update_query)) {
                throw new Exception("Error updating Product ID: $product_id - " . mysqli_error($conn));
            }
        }

        // Commit transaction
        mysqli_commit($conn);
        header("Location: edit_product.php?updated=true" . $redirect_search);
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<p class='error-msg'>Error: " . $e->getMessage() . "</p>";
        exit();
    }
}

// Search functionality
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM FinalExam_Korkrit_Pip_Inventory WHERE 
              Korkrit_Pip_ID_Product LIKE '%$search_query%' OR 
              Korkrit_Pip_Name_Product LIKE '%$search_query%'
              ORDER BY Korkrit_Pip_ID_Product ASC";
} else {
    $query = "SELECT * FROM FinalExam_Korkrit_Pip_Inventory ORDER BY Korkrit_Pip_ID_Product ASC";
}

$result = mysqli_query($conn, $query);
if (!$result) {
    echo "<p class='error-msg'>Error fetching data: " . mysqli_error($conn) . "</p>";
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $redirect_search = "";
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $redirect_search = "&search=" . urlencode($_GET['search']);
    }
    
    $delete_query = "DELETE FROM FinalExam_Korkrit_Pip_Inventory WHERE Korkrit_Pip_ID_Product = '$product_id'";
    if (mysqli_query($conn, $delete_query)) {
        header("Location: edit_product.php?deleted=true" . $redirect_search);
        exit();
    } else {
        echo "<p class='error-msg'>Error deleting record: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Products</title>
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

        /* ======= Main Container ======= */
        .main-container {
            max-width: 1400px;
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
        .main-container::before {
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

        .header {
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

        .header-bg {
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

        .main-title {
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

        .subtitle {
            color: rgba(255, 255, 255, 0.8);
            margin-top: 0.5rem;
            font-size: 1.1rem;
            position: relative;
            z-index: 1;
            max-width: 600px;
        }

        .header-icon {
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

        .header-icon svg {
            width: 42px;
            height: 42px;
            fill: white;
        }

        .main-content {
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Search and Messages */
        .search-container {
            display: flex;
            margin-bottom: 1.5rem;
            max-width: 500px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .search-input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1rem;
            backdrop-filter: blur(5px);
        }

        .search-input:focus {
            outline: none;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .search-btn {
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #4361ee, #7209b7);
            color: white;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-btn:hover {
            background: linear-gradient(135deg, #3a53d9, #6108a3);
        }

        /* Messages */
        .success-msg, .error-msg {
            padding: 1rem 2rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.5s ease-out;
            max-width: 500px;
        }

        .success-msg {
            background: linear-gradient(135deg, #06d6a0, #05a37a);
            box-shadow: 0 5px 15px rgba(6, 214, 160, 0.3);
        }

        .error-msg {
            background: linear-gradient(135deg, #ef476f, #d23c5e);
            box-shadow: 0 5px 15px rgba(239, 71, 111, 0.3);
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

        /* Table Styling */
        .table-container {
            overflow-x: auto;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            color: white;
        }

        .data-table th {
            background: rgba(0, 0, 0, 0.4);
            padding: 1rem 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .data-table th:first-child {
            border-top-left-radius: 10px;
        }

        .data-table th:last-child {
            border-top-right-radius: 10px;
        }

        .data-table tr {
            transition: all 0.3s ease;
        }

        .data-table tr:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .data-table tr:not(:last-child) {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .data-table td {
            padding: 1rem 0.8rem;
            vertical-align: middle;
        }

        /* Form Element Styling */
        .edit-input, select {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 0.7rem;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-align: center;
        }

        .edit-input:focus, select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(76, 201, 240, 0.3);
            background: rgba(0, 0, 0, 0.3);
        }

        .edit-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 16px;
            padding-right: 30px;
        }

        input[type="date"] {
            text-align: center;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        /* Status Styling */
        .status-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-available {
            background-color: var(--success-color);
            box-shadow: 0 0 8px var(--success-color);
        }

        .status-sold {
            background-color: var(--danger-color);
            box-shadow: 0 0 8px var(--danger-color);
        }

        /* Action Buttons */
        .actions-cell {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .update-btn, .delete-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .update-btn {
            background: linear-gradient(135deg, #06d6a0, #05a37a);
        }

        .delete-btn {
            background: linear-gradient(135deg, #ef476f, #d23c5e);
            text-decoration: none;
        }

        .update-btn:hover, .delete-btn:hover {
            transform: translateY(-3px) scale(1.1);
        }

        .update-btn:hover {
            box-shadow: 0 6px 15px rgba(6, 214, 160, 0.4);
        }

        .delete-btn:hover {
            box-shadow: 0 6px 15px rgba(239, 71, 111, 0.4);
        }

        /* Responsive Design */
        @media screen and (max-width: 1200px) {
            .main-container {
                margin: 100px 20px 50px;
            }
        }

        @media screen and (max-width: 992px) {
            .navbar {
                padding: 1rem;
            }

            .table-container {
                overflow-x: auto;
            }

            .data-table {
                width: 100%;
                min-width: 1000px; /* Ensures visibility on smaller screens */
            }
        }

        @media screen and (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .header {
                padding: 1.5rem;
            }

            .main-title {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .main-content {
                padding: 1.5rem;
            }
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Animations */
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

    <!-- Main Container -->
    <div class="main-container">
        <div class="header">
            <div class="header-bg"></div>
            <div class="header-icon pulse">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 14v3h3v2h-3.001L19 22h-2l-.001-3H14v-2h3v-3h2zm1.243-10.243a6 6 0 0 1 .236 8.236l-1.414-1.414a4 4 0 0 0-.192-5.452A3.998 3.998 0 0 0 13.5 4.501l-.082-.01L12.034 8l-1.418-.036-1.098-2.195-1.418.036-.08 1.598-1.597.08-.036 1.417L8.582 10l-2.195 1.098-.036 1.418 1.598.08.08 1.597 1.417.036L10.946 16l1.37-.034 1.563-3.126.047.016a6 6 0 0 1-3.51 3.443l-1.4-1.932a4 4 0 0 0 1.408-1.7l-.089-.025-5.02.251-1.884-3.767L8.326 5.6a3.996 3.996 0 0 0-3.337-1.462A3.998 3.998 0 0 0 1.9 8.401a3.998 3.998 0 0 0 4.366 3.866l.836 2.342a6 6 0 0 1-7.143-5.747 6 6 0 0 1 7.785-5.762A6 6 0 0 1 14.5 4.001l.037-.001a5.964 5.964 0 0 1 4.229 1.757l1.477-1.28z"/></svg>
            </div>
            <h1 class="main-title">
                <span class="lang-en">Edit Products</span>
                <span class="lang-th" style="display: none;">แก้ไขสินค้า</span>
            </h1>
            <p class="subtitle">
                <span class="lang-en">Update your product information, manage stock levels, and adjust product status from this dashboard.</span>
                <span class="lang-th" style="display: none;">อัปเดตข้อมูลสินค้า, จัดการระดับสต็อก และปรับสถานะสินค้าจากแดชบอร์ดนี้</span>
            </p>
        </div>

        <div class="main-content">
    <?php if(isset($_GET['updated']) && $_GET['updated'] == 'true'): ?>
    <div class="success-msg">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        <span class="lang-en">Products updated successfully!</span>
        <span class="lang-th" style="display: none;">อัปเดตสินค้าสำเร็จแล้ว!</span>
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['deleted']) && $_GET['deleted'] == 'true'): ?>
    <div class="success-msg">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        <span class="lang-en">Product deleted successfully!</span>
        <span class="lang-th" style="display: none;">ลบสินค้าสำเร็จแล้ว!</span>
    </div>
    <?php endif; ?>

    <!-- Search Form -->
    <form method="GET" action="edit_product.php" class="search-container">
    <input type="text" name="search" class="search-input" 
       placeholder="Search games..." 
       value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" class="search-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <span class="lang-en">Search</span>
            <span class="lang-th" style="display: none;">ค้นหา</span>
        </button>
    </form>

            <!-- Single Form for All Rows -->
            <form method="POST" action="edit_product.php" enctype="multipart/form-data" id="bulkEditForm">
    <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($search_query); ?>">
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th><span class="lang-en">PRODUCT NAME</span><span class="lang-th" style="display: none;">ชื่อสินค้า</span></th>
                    <th><span class="lang-en">QUANTITY IN STOCK</span><span class="lang-th" style="display: none;">จำนวนคงเหลือ</span></th>
                    <th><span class="lang-en">UNIT PRICE</span><span class="lang-th" style="display: none;">ราคาต่อหน่วย</span></th>
                    <th><span class="lang-en">IMAGE PATH</span><span class="lang-th" style="display: none;">ที่อยู่รูปภาพ</span></th>
                    <th><span class="lang-en">ACTIONS</span><span class="lang-th" style="display: none;">การจัดการ</span></th>
                </tr>
            </thead>
            <tbody>
            <?php $index = 0; while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['Korkrit_Pip_ID_Product']); ?></td>
                    <td>
                        <input type="text" class="edit-input" name="product_name[<?php echo $index; ?>]" 
                               value="<?php echo htmlspecialchars($row['Korkrit_Pip_Name_Product']); ?>" required>
                    </td>
                    <td>
                        <input type="number" class="edit-input" name="qty_stock[<?php echo $index; ?>]" min="0" max="1000" 
                               value="<?php echo htmlspecialchars($row['Korkrit_Pip_Qty_Stock']); ?>" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="edit-input" name="price_unit[<?php echo $index; ?>]" min="0" 
                               value="<?php echo htmlspecialchars($row['Korkrit_Pip_Price_Unit']); ?>" required>
                    </td>
                    <td>
                        <input type="file" name="game_image[<?php echo $index; ?>]" class="edit-input" accept="image/*">
                        <input type="hidden" name="current_image[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['Korkrit_Pip_Img_Path'] ?? ''); ?>">
                    </td>
                    <td class="actions-cell">
                        <input type="hidden" name="product_id[<?php echo $index; ?>]" value="<?php echo htmlspecialchars($row['Korkrit_Pip_ID_Product']); ?>">
                        <a href="edit_product.php?delete=<?php echo htmlspecialchars($row['Korkrit_Pip_ID_Product']); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
                           class="delete-btn" title="Delete"
                           onclick="return confirm('Are you sure you want to delete this product?')">
                            ✗
                        </a>
                    </td>
                </tr>
            <?php $index++; endwhile; ?>
            </tbody>
        </table>
    </div>
    <!-- Update All Button -->
    <div style="margin-top: 20px; text-align: right;">
        <button type="submit" name="update_all" class="update-btn" style="width: auto; padding: 10px 20px; border-radius: 15px;">
            <span class="lang-en">Update All</span>
            <span class="lang-th" style="display: none;">อัปเดตทั้งหมด</span>
        </button>
    </div>
</form>
    <script>
        // Language Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const langToggle = document.getElementById('langToggle');
            const currentLang = document.querySelector('.current-lang');
            const body = document.body;
            
            langToggle.addEventListener('click', function() {
                const isThaiActive = document.querySelector('.lang-th').style.display !== 'none';
                
                // Switch languages
                document.querySelectorAll('.lang-en').forEach(el => {
                    el.style.display = isThaiActive ? 'inline-block' : 'none';
                });
                
                document.querySelectorAll('.lang-th').forEach(el => {
                    el.style.display = isThaiActive ? 'none' : 'inline-block';
                });
                
                // Update language badge
                currentLang.textContent = isThaiActive ? 'EN' : 'TH';
                
                // Toggle thai class on body
                if (isThaiActive) {
                    body.classList.remove('thai');
                } else {
                    body.classList.add('thai');
                }
                
                // Set cookie for language preference
                document.cookie = "language=" + (isThaiActive ? 'english' : 'thai') + "; path=/; max-age=" + 60*60*24*30; // 30 days
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
            document.addEventListener('DOMContentLoaded', function() {

    // Bulk update confirmation
    const bulkEditForm = document.getElementById('bulkEditForm');
    bulkEditForm.addEventListener('submit', function(e) {
        const isThaiActive = document.querySelector('.lang-th').style.display !== 'none';
        if (!confirm(isThaiActive ? 'คุณแน่ใจหรือไม่ว่าต้องการอัปเดตสินค้าทั้งหมด?' : 'Are you sure you want to update all products?')) {
            e.preventDefault();
        }
    });
});
            
            // Update status indicators when status select changes
            document.querySelectorAll('select[name="status"]').forEach(select => {
                const statusIndicator = select.previousElementSibling;
                
                select.addEventListener('change', function() {
                    statusIndicator.className = 'status-indicator ' + 
                        (this.value === 'available' ? 'status-available' : 'status-sold');
                });
            });
            
            // Auto-hide success messages
            const messages = document.querySelectorAll('.success-msg, .error-msg');
            if (messages.length > 0) {
                setTimeout(() => {
                    messages.forEach(msg => {
                        msg.style.opacity = '0';
                        msg.style.transform = 'translateY(-20px)';
                        msg.style.transition = 'all 0.5s ease';
                    });
                    
                    setTimeout(() => {
                        messages.forEach(msg => {
                            msg.style.display = 'none';
                        });
                    }, 500);
                }, 3000);
            }
            
            // Mobile menu functionality
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const navLinks = document.querySelector('.nav-links');
            
            mobileMenuBtn.addEventListener('click', function() {
                if (navLinks.style.display === 'flex' || 
                    window.getComputedStyle(navLinks).display === 'flex') {
                    navLinks.style.display = 'none';
                } else {
                    navLinks.style.display = 'flex';
                    navLinks.style.position = 'absolute';
                    navLinks.style.top = '100%';
                    navLinks.style.left = '0';
                    navLinks.style.width = '100%';
                    navLinks.style.background = 'rgba(0, 0, 0, 0.8)';
                    navLinks.style.flexDirection = 'column';
                    navLinks.style.padding = '1rem';
                    navLinks.style.borderTop = '1px solid rgba(255, 255, 255, 0.1)';
                }
            });
            
            // Adjust for screen size changes
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    navLinks.style.display = 'flex';
                    navLinks.style.position = 'static';
                    navLinks.style.width = 'auto';
                    navLinks.style.background = 'transparent';
                    navLinks.style.flexDirection = 'row';
                    navLinks.style.padding = '0';
                    navLinks.style.borderTop = 'none';
                } else if (navLinks.style.display === 'flex') {
                    navLinks.style.position = 'absolute';
                    navLinks.style.top = '100%';
                    navLinks.style.left = '0';
                    navLinks.style.width = '100%';
                    navLinks.style.background = 'rgba(0, 0, 0, 0.8)';
                    navLinks.style.flexDirection = 'column';
                    navLinks.style.padding = '1rem';
                    navLinks.style.borderTop = '1px solid rgba(255, 255, 255, 0.1)';
                }
            });
            
            // Fix for select options with language spans
            document.querySelectorAll('select').forEach(select => {
                select.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const langElements = selectedOption.querySelectorAll('span');
                    
                    if (langElements.length > 0) {
                        const isThaiActive = document.querySelector('.lang-th').style.display !== 'none';
                        
                        langElements.forEach(el => {
                            if ((isThaiActive && el.classList.contains('lang-th')) ||
                                (!isThaiActive && el.classList.contains('lang-en'))) {
                                el.style.display = 'inline-block';
                            } else {
                                el.style.display = 'none';
                            }
                        });
                    }
                });
            });
            
            // Initialize based on cookie
            const cookieLanguage = document.cookie
                .split('; ')
                .find(row => row.startsWith('language='))
                ?.split('=')[1];
                
            if (cookieLanguage === 'thai') {
                document.querySelectorAll('.lang-en').forEach(el => {
                    el.style.display = 'none';
                });
                
                document.querySelectorAll('.lang-th').forEach(el => {
                    el.style.display = 'inline-block';
                });
                
                currentLang.textContent = 'TH';
                body.classList.add('thai');
            }
        });
    </script>
</body>
</html>