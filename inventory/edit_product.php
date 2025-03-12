<?php
require 'db_connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $game_id = mysqli_real_escape_string($conn, $_POST['game_id']);
    $game_name = mysqli_real_escape_string($conn, $_POST['game_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $release_date = mysqli_real_escape_string($conn, $_POST['release_date']);
    $score = mysqli_real_escape_string($conn, $_POST['score']);
    $developer = mysqli_real_escape_string($conn, $_POST['developer']);
    $platform = mysqli_real_escape_string($conn, $_POST['platform']);
    $last_update = mysqli_real_escape_string($conn, $_POST['last_update']);
    $game_image = mysqli_real_escape_string($conn, $_POST['game_image']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $stock_quantity = mysqli_real_escape_string($conn, $_POST['stock_quantity']);
    
    // Get search query if present
    $redirect_search = "";
    if (isset($_POST['search_query']) && !empty($_POST['search_query'])) {
        $redirect_search = "&search=" . urlencode($_POST['search_query']);
    }

    // Validate
    if (empty($game_id) || empty($game_name) || empty($category) || empty($release_date) || 
        empty($score) || empty($developer) || empty($platform) || empty($last_update) || 
        empty($game_image) || empty($status) || !is_numeric($stock_quantity)) {
        echo "<p class='error-msg'>Error: All fields are required and Stock Quantity must be a number.</p>";
        exit();
    }

    $score = floatval($score);
    $stock_quantity = intval($stock_quantity);

    if (!in_array($status, ['available', 'sold'])) {
        echo "<p class='error-msg'>Error: Status must be 'available' or 'sold'.</p>";
        exit();
    }

    if ($stock_quantity < 0 || $stock_quantity > 1000) {
        echo "<p class='error-msg'>Error: Stock Quantity must be between 0 and 1000.</p>";
        exit();
    }

    $update_query = "UPDATE games SET 
                     GameName = '$game_name', 
                     Category = '$category', 
                     ReleaseDate = '$release_date', 
                     Score = $score, 
                     Developer = '$developer', 
                     Platform = '$platform', 
                     LastUpdate = '$last_update', 
                     GameImage = '$game_image', 
                     status = '$status', 
                     StockQuantity = $stock_quantity
                     WHERE GameID = '$game_id'";

    if (mysqli_query($conn, $update_query)) {
        header("Location: edit_product.php?updated=true" . $redirect_search);
        exit();
    } else {
        echo "<p class='error-msg'>Error updating product: " . mysqli_error($conn) . "</p>";
        exit();
    }
}

// Search functionality
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM games WHERE 
              GameID LIKE '%$search_query%' OR 
              GameName LIKE '%$search_query%' OR 
              Category LIKE '%$search_query%' OR 
              Developer LIKE '%$search_query%' OR 
              Platform LIKE '%$search_query%'
              ORDER BY GameID ASC";
} else {
    $query = "SELECT * FROM games ORDER BY GameID ASC";
}

$result = mysqli_query($conn, $query);
if (!$result) {
    echo "<p class='error-msg'>Error fetching data: " . mysqli_error($conn) . "</p>";
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $game_id = mysqli_real_escape_string($conn, $_GET['delete']);
    
    // Get search query if present for redirect
    $redirect_search = "";
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $redirect_search = "&search=" . urlencode($_GET['search']);
    }
    
    $delete_query = "DELETE FROM games WHERE GameID = '$game_id'";
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
            --primary-color: #161a42;
            --secondary-color: #1f2463;
            --accent-color: #4cc4f0;
            --success-color: #06d6a0;
            --danger-color: #ef476f;
            --table-header: #5f647e;
            --table-row-odd: #9597b1;
            --table-row-even: #8688a8;
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
            background-color: var(--primary-color);
            color: white;
            min-height: 100vh;
            display: flex;
        }

        body.thai {
            font-family: var(--bs-font-thai);
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            padding: 2rem 1rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2rem;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .logo-icon {
            padding: 0.5rem;
            border: 2px solid white;
            border-radius: 8px;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .nav-link {
            text-decoration: none;
            color: white;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background-color: var(--secondary-color);
            font-weight: 600;
        }

        .nav-link i {
            font-size: 1.2rem;
        }

        /* Main content area */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            max-width: calc(100% - 250px);
        }

        /* Language toggle */
        .lang-toggle {
            margin-top: auto;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .lang-toggle:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        /* Search bar */
        .search-container {
            margin-bottom: 1.5rem;
            display: flex;
            max-width: 400px;
        }

        .search-input {
            flex: 1;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 8px 0 0 8px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .search-input:focus {
            outline: none;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .search-btn {
            padding: 0.8rem 1.2rem;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 0 8px 8px 0;
            cursor: pointer;
            font-weight: 600;
        }

        .search-btn:hover {
            background-color: #3ab0dc;
        }

        /* Table container */
        .table-container {
            overflow-x: auto;
            background-color: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background-color: var(--table-header);
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            white-space: nowrap;
        }

        .data-table tr:nth-child(odd) {
            background-color: var(--table-row-odd);
        }

        .data-table tr:nth-child(even) {
            background-color: var(--table-row-even);
        }

        .data-table td {
            padding: 1rem 0.8rem;
            text-align: center;
            color: white;
        }

        /* Form inputs */
        .edit-input {
            background-color: rgba(0, 0, 0, 0.2);
            border: none;
            border-radius: 8px;
            padding: 0.5rem;
            color: white;
            width: 100%;
            text-align: center;
        }

        .edit-input:focus {
            outline: none;
            background-color: rgba(0, 0, 0, 0.3);
        }

        /* Date input styling */
        .date-input {
            position: relative;
            width: 100%;
        }

        .date-input input[type="date"] {
            background-color: rgba(0, 0, 0, 0.2);
            border: none;
            border-radius: 8px;
            padding: 0.5rem;
            color: white;
            width: 100%;
            text-align: center;
        }

        .date-input input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }

        /* Status styling with indicator */
        .status-cell {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .status-indicator {
            display: inline-block;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-available {
            background-color: var(--success-color);
            box-shadow: 0 0 5px var(--success-color);
        }

        .status-sold {
            background-color: var(--danger-color);
            box-shadow: 0 0 5px var(--danger-color);
        }

        /* Update button */
        .update-btn {
            background-color: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
            width: 30px;
            height: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            line-height: 1;
        }

        .update-btn:hover {
            background-color: #05b084;
            transform: translateY(-2px);
        }

        /* Messages */
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success-message {
            background-color: var(--success-color);
            color: white;
        }

        .error-message {
            background-color: var(--danger-color);
            color: white;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                padding: 1rem 0.5rem;
            }

            .logo {
                font-size: 0;
                justify-content: center;
            }

            .nav-link span {
                display: none;
            }

            .nav-link {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 0.8rem;
            }

            .main-content {
                margin-left: 80px;
                padding: 1rem;
                max-width: calc(100% - 80px);
            }

            .lang-toggle span {
                display: none;
            }

            .lang-toggle {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .table-container {
                overflow-x: auto;
            }
            
            .data-table {
                min-width: 800px;
            }
        }

        /* Hide default dropdown arrow for select */
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 16px;
            padding-right: 28px;
        }
        /* Delete button */
.delete-btn {
    background-color: var(--danger-color);
    color: white;
    border: none;
    border-radius: 8px;
    width: 30px;
    height: 30px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    line-height: 1;
    text-decoration: none;
}

.delete-btn:hover {
    background-color: #d63f61;
    transform: translateY(-2px);
}

/* Adjust the actions cell to space buttons */
.actions-cell {
    display: flex;
    gap: 8px;
    justify-content: center;
}
    </style>
</head>
<body class="<?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'thai' : ''; ?>">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <div class="logo-icon">📋</div>
            <span>INVENTORY</span>
        </div>
        <div class="nav-links">
            <a href="inventory.php" class="nav-link">
                <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                <span>ร้านค้า</span>
                <?php else: ?>
                <span>Store</span>
                <?php endif; ?>
            </a>
            <a href="edit_product.php" class="nav-link active">
                <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                <span>แก้ไขสินค้า</span>
                <?php else: ?>
                <span>Edit Product</span>
                <?php endif; ?>
            </a>
            <a href="Stockgame.php" class="nav-link">
                <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                <span>แสดงสินค้า</span>
                <?php else: ?>
                <span>Show Product</span>
                <?php endif; ?>
            </a>
            <a href="add_product.php" class="nav-link">
                <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                <span>เพิ่มสินค้า</span>
                <?php else: ?>
                <span>Add Product</span>
                <?php endif; ?>
            </a>
        </div>
        <div class="lang-toggle" id="langToggle">
            <span><?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'TH' : 'EN'; ?></span>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if(isset($_GET['updated']) && $_GET['updated'] == 'true'): ?>
        <div class="message success-message">
            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
            <span>อัปเดตสินค้าสำเร็จแล้ว!</span>
            <?php else: ?>
            <span>Product updated successfully!</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['deleted']) && $_GET['deleted'] == 'true'): ?>
        <div class="message success-message">
            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
            <span>ลบสินค้าสำเร็จแล้ว!</span>
            <?php else: ?>
            <span>Product deleted successfully!</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Search Form -->
        <form method="GET" action="edit_product.php" class="search-container">
            <input type="text" name="search" class="search-input" 
                   placeholder="<?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'ค้นหาเกม...' : 'Search games...'; ?>" 
                   value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="search-btn">
                <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                ค้นหา
                <?php else: ?>
                Search
                <?php endif; ?>
            </button>
        </form>

        <!-- Data Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            ชื่อเกม
                            <?php else: ?>
                            GAME NAME
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            หมวดหมู่
                            <?php else: ?>
                            CATEGORY
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            วันที่เผยแพร่
                            <?php else: ?>
                            RELEASE DATE
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            คะแนน
                            <?php else: ?>
                            SCORE
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            ผู้พัฒนา
                            <?php else: ?>
                            DEVELOPER
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            แพลตฟอร์ม
                            <?php else: ?>
                            PLATFORM
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            อัปเดตล่าสุด
                            <?php else: ?>
                            LAST UPDATE
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            ที่อยู่รูปภาพ
                            <?php else: ?>
                            IMAGE PATH
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            สถานะ
                            <?php else: ?>
                            STATUS
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            สินค้าคงคลัง
                            <?php else: ?>
                            STOCK
                            <?php endif; ?>
                        </th>
                        <th>
                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                            การจัดการ
                            <?php else: ?>
                            ACTIONS
                            <?php endif; ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <form method="POST" action="edit_product.php">
                            <!-- Hidden field to maintain search query -->
                            <input type="hidden" name="search_query" value="<?php echo htmlspecialchars($search_query); ?>">
                            
                            <td><?php echo htmlspecialchars($row['GameID']); ?></td>
                            <td>
                                <input type="text" class="edit-input" name="game_name" 
                                      value="<?php echo htmlspecialchars($row['GameName']); ?>" required>
                            </td>
                            <td>
                                <input type="text" class="edit-input" name="category" 
                                      value="<?php echo htmlspecialchars($row['Category']); ?>" required>
                            </td>
                            <td class="date-input">
                                <input type="date" name="release_date" 
                                       value="<?php echo htmlspecialchars($row['ReleaseDate']); ?>" required>
                            </td>
                            <td>
                                <input type="number" step="0.1" min="0" max="10" class="edit-input" name="score" 
                                      value="<?php echo htmlspecialchars($row['Score']); ?>" required>
                            </td>
                            <td>
                                <input type="text" class="edit-input" name="developer" 
                                      value="<?php echo htmlspecialchars($row['Developer']); ?>" required>
                            </td>
                            <td>
                                <input type="text" class="edit-input" name="platform" 
                                      value="<?php echo htmlspecialchars($row['Platform']); ?>" required>
                            </td>
                            <td class="date-input">
                                <input type="date" name="last_update" 
                                       value="<?php echo htmlspecialchars($row['LastUpdate']); ?>" required>
                            </td>
                            <td>
                                <input type="text" class="edit-input" name="game_image" 
                                      value="<?php echo htmlspecialchars($row['GameImage']); ?>" required>
                            </td>
                            <td>
                                <div class="status-cell">
                                    <span class="status-indicator <?php echo ($row['status'] === 'available') ? 'status-available' : 'status-sold'; ?>"></span>
                                    <select name="status" class="edit-input" required>
                                        <option value="available" <?php echo ($row['status'] === 'available') ? 'selected' : ''; ?>>
                                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                                            มีสินค้า
                                            <?php else: ?>
                                            Available
                                            <?php endif; ?>
                                        </option>
                                        <option value="sold" <?php echo ($row['status'] === 'sold') ? 'selected' : ''; ?>>
                                            <?php if(isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai'): ?>
                                            ขายแล้ว
                                            <?php else: ?>
                                            Sold
                                            <?php endif; ?>
                                        </option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <input type="number" class="edit-input" name="stock_quantity" min="0" max="1000" 
                                      value="<?php echo htmlspecialchars($row['StockQuantity']); ?>" required>
                            </td>
                            <td class="actions-cell">
    <input type="hidden" name="game_id" value="<?php echo htmlspecialchars($row['GameID']); ?>">
    <button type="submit" name="update" class="update-btn">✓</button>
    <a href="edit_product.php?delete=<?php echo htmlspecialchars($row['GameID']); ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" 
       class="delete-btn" 
       onclick="return confirm('<?php echo isset($_COOKIE['language']) && $_COOKIE['language'] === 'thai' ? 'คุณแน่ใจหรือไม่ว่าต้องการลบสินค้านี้?' : 'Are you sure you want to delete this product?'; ?>')">
        ✗
    </a>
</td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Language Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const langToggle = document.getElementById('langToggle');
            const body = document.body;
            
            langToggle.addEventListener('click', function() {
                if (body.classList.contains('thai')) {
                    // Switch to English
                    body.classList.remove('thai');
                    langToggle.innerHTML = '<span>EN</span>';
                    
                    // Set cookie for language preference
                    document.cookie = "language=english; path=/; max-age=" + 60*60*24*30; // 30 days
                    
                    // Reload page to apply language change
                    location.reload();
                } else {
                    // Switch to Thai
                    body.classList.add('thai');
                    langToggle.innerHTML = '<span>TH</span>';
                    
                    // Set cookie for language preference
                    document.cookie = "language=thai; path=/; max-age=" + 60*60*24*30; // 30 days
                    
                    // Reload page to apply language change
                    location.reload();
                }
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
            const messages = document.querySelectorAll('.message');
            if (messages.length > 0) {
                setTimeout(() => {
                    messages.forEach(msg => {
                        msg.style.opacity = '0';
                        msg.style.height = '0';
                        msg.style.padding = '0';
                        msg.style.margin = '0';
                        msg.style.transition = 'all 0.5s ease';
                    });
                }, 3000);
            }
        });
    </script>
</body>
</html>