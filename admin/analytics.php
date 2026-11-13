<?php
// admin/analytics.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security check: Verify admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// Fetch analytics aggregated data
try {
    // 1. Line Graph: Monthly Revenue for the current year
    $revenueStmt = $pdo->query("
        SELECT MONTH(created_at) as month, SUM(total_amount) as monthly_total 
        FROM orders 
        GROUP BY MONTH(created_at) 
        ORDER BY month ASC
    ");
    $monthlyData = array_fill(1, 12, 0);
    while ($row = $revenueStmt->fetch()) {
        $monthlyData[(int)$row['month']] = (float)$row['monthly_total'];
    }

    // 2. Pie / Doughnut Chart: Most Bought Price Range Trends
    // Categorizing order item prices into brackets
    $priceRangeStmt = $pdo->query("
        SELECT 
            CASE 
                WHEN price < 500 THEN 'Under ₱500 (Budget)'
                WHEN price BETWEEN 500 AND 999 THEN '₱500 - ₱999 (Mid-Range)'
                WHEN price BETWEEN 1000 AND 1999 THEN '₱1,000 - ₱1,999 (Premium)'
                ELSE '₱2,000+ (Luxury)'
            END as price_range,
            SUM(quantity) as total_units
        FROM order_items
        GROUP BY price_range
        ORDER BY total_units DESC
    ");
    $priceTrends = $priceRangeStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 3. Bar Graph: Top Selling Products by Quantity Sold
    $topProductsStmt = $pdo->query("
        SELECT p.name, SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        GROUP BY p.id, p.name
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $monthlyData = array_fill(1, 12, 0);
    $priceTrends = [];
    $topProducts = [];
}

$pageTitle = "Store Analytics | Elliana's Boutique Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-gray-900 text-gray-300">
                <div class="flex items-center justify-center h-20 bg-gray-950 px-4 border-b border-gray-800">
                    <a href="dashboard.php" class="flex items-center gap-2.5 group">
                        <div class="h-10 w-10 bg-yellow-500/10 rounded-xl flex items-center justify-center border border-yellow-500/20">
                            <img src="../assets/images/Logo.png" alt="Elliana's Logo" class="h-6 w-auto object-contain" onerror="this.onerror=null; this.src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/baby.svg';">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-extrabold text-white tracking-wide">Elliana Admin</span>
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-yellow-500 -mt-1">Management</span>
                        </div>
                    </a>
                </div>
                <div class="flex flex-col flex-grow px-4 py-6 space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-house mr-3"></i> Dashboard
                    </a>
                    <a href="analytics.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg bg-yellow-500 text-white shadow-xs">
                        <i class="fa-solid fa-chart-pie mr-3"></i> Analytics
                    </a>
                    <a href="products.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-shirt mr-3"></i> Products
                    </a>
                    <a href="orders.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-box-archive mr-3"></i> Orders
                    </a>
                    <a href="users.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-users mr-3"></i> Users
                    </a>
                    <a href="../index.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-store mr-3"></i> View Store
                    </a>
                </div>
                <div class="p-4 bg-gray-950">
                    <a href="../logout.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                        <i class="fa-solid fa-right-from-bracket mr-3"></i> Logout
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 h-full overflow-hidden">
            <header class="flex items-center justify-between h-16 px-6 bg-white border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-800">Store Performance & Analytics</h1>
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-600">Hi, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                    <div class="h-8 w-8 rounded-full bg-yellow-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 space-y-6">
                
                <!-- Top Row: Line Chart (Revenue) & Doughnut Chart (Price Range Trends) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Line Graph: Monthly Revenue -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-chart-line text-yellow-600"></i> Monthly Revenue Overview (₱)
                        </h3>
                        <div class="relative h-72">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Doughnut Chart: Most Bought Price Range Trends -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-tags text-yellow-600"></i> Customer Trends: Most Bought Price Ranges
                        </h3>
                        <div class="relative h-72 flex justify-center">
                            <canvas id="priceTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row: Bar Chart for Top Selling Products with Color Variants -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-chart-column text-yellow-600"></i> Top-Selling Products Demand
                    </h3>
                    <div class="relative h-72">
                        <canvas id="productChart"></canvas>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Chart.js Script Initializer -->
    <script>
        // 1. Line Chart: Revenue
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Revenue (₱)',
                    data: <?php echo json_encode(array_values($monthlyData)); ?>,
                    borderColor: '#ca8a04',
                    backgroundColor: 'rgba(202, 138, 4, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // 2. Doughnut Chart: Price Range Trends
        const priceCtx = document.getElementById('priceTrendChart').getContext('2d');
        new Chart(priceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($priceTrends)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($priceTrends)); ?>,
                    backgroundColor: ['#eab308', '#3b82f6', '#10b981', '#f97316', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // 3. Bar Chart: Top Products Demand with Multi-Color Variants
        const productCtx = document.getElementById('productChart').getContext('2d');
        new Chart(productCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($topProducts, 'name')); ?>,
                datasets: [{
                    label: 'Units Sold',
                    data: <?php echo json_encode(array_column($topProducts, 'total_sold')); ?>,
                    backgroundColor: [
                        'rgba(234, 179, 8, 0.8)',  // Amber
                        'rgba(59, 130, 246, 0.8)', // Blue
                        'rgba(16, 185, 129, 0.8)', // Emerald
                        'rgba(139, 92, 246, 0.8)', // Violet
                        'rgba(249, 115, 22, 0.8)'  // Orange
                    ],
                    borderColor: [
                        'rgb(202, 138, 4)',
                        'rgb(37, 99, 235)',
                        'rgb(5, 150, 105)',
                        'rgb(124, 58, 237)',
                        'rgb(234, 88, 12)'
                    ],
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>