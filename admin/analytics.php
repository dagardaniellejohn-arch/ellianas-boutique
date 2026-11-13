<?php
// admin/analytics.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security check: Verify admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// Fetch analytics aggregated data aligned with products (Stocks, Categories, Sizes, Age Ranges)
try {
    // 1. Line Graph: Monthly Revenue for the current year
    $revenueStmt = $pdo->query("
        SELECT MONTH(created_at) as month, SUM(total_amount) as monthly_total 
        FROM orders 
        GROUP BY MONTH(created_at) 
        ORDER BY month ASC
    ");
    $monthlyData = array_fill(1, 12, 0);
    $totalAnnualRevenue = 0;
    while ($row = $revenueStmt->fetch()) {
        $monthlyData[(int)$row['month']] = (float)$row['monthly_total'];
        $totalAnnualRevenue += (float)$row['monthly_total'];
    }

    // 2. Doughnut Chart: Category Distribution
    $categoryStmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM products 
        WHERE category IS NOT NULL AND category != '' 
        GROUP BY category
    ");
    $categoryData = $categoryStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $totalActiveProducts = array_sum($categoryData);

    // 3. Doughnut Chart: Age Range / Apparel Size Distribution
    $ageStmt = $pdo->query("
        SELECT size as age_range, COUNT(*) as count 
        FROM products 
        WHERE size IS NOT NULL AND size != '' 
        GROUP BY size
    ");
    $ageData = $ageStmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // 4. Bar Graph: Top Selling Products (Including Size and Stock details)
    $topProductsStmt = $pdo->query("
        SELECT p.name, p.size, p.stock, SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        GROUP BY p.id, p.name, p.size, p.stock
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $topProducts = $topProductsStmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Stock Levels Overview across Inventory
    $stockStmt = $pdo->query("
        SELECT name, stock, size 
        FROM products 
        ORDER BY stock ASC 
        LIMIT 5
    ");
    $lowStockProducts = $stockStmt->fetchAll(PDO::FETCH_ASSOC);

    // Total Low Stock Count
    $lowStockCountStmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= 5");
    $lowStockCount = $lowStockCountStmt->fetchColumn();

} catch (PDOException $e) {
    $monthlyData = array_fill(1, 12, 0);
    $categoryData = [];
    $ageData = [];
    $topProducts = [];
    $lowStockProducts = [];
    $totalAnnualRevenue = 0;
    $totalActiveProducts = 0;
    $lowStockCount = 0;
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
<body class="bg-gray-50/50 font-sans antialiased text-gray-800">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-gray-900 text-gray-300">
                <div class="flex items-center justify-center h-20 bg-gray-950 px-4 border-b border-gray-800">
                    <a href="dashboard.php" class="flex items-center gap-2.5 group">
                        <div class="h-10 w-10 bg-yellow-500/10 rounded-xl flex items-center justify-center border border-yellow-500/20 group-hover:scale-105 transition-transform">
                            <img src="../assets/images/Logo.png" alt="Elliana's Logo" class="h-6 w-auto object-contain" onerror="this.onerror=null; this.src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/baby.svg';">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base font-extrabold text-white tracking-wide group-hover:text-yellow-400 transition-colors">Elliana Admin</span>
                            <span class="text-[10px] font-semibold uppercase tracking-widest text-yellow-500 -mt-1">Management</span>
                        </div>
                    </a>
                </div>
                <div class="flex flex-col flex-grow px-4 py-6 space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-house mr-3"></i> Dashboard
                    </a>
                    <a href="analytics.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl bg-yellow-500 text-white shadow-xs">
                        <i class="fa-solid fa-chart-pie mr-3"></i> Analytics
                    </a>
                    <a href="products.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-shirt mr-3"></i> Products
                    </a>
                    <a href="orders.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-box-archive mr-3"></i> Orders
                    </a>
                    <a href="users.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-users mr-3"></i> Users
                    </a>
                    <a href="../index.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-xl hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-store mr-3"></i> View Store
                    </a>
                </div>
                <div class="p-4 bg-gray-950">
                    <a href="../logout.php" class="flex items-center px-4 py-2 text-sm font-medium rounded-xl text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
                        <i class="fa-solid fa-right-from-bracket mr-3"></i> Logout
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 h-full overflow-hidden">
            <header class="flex items-center justify-between h-20 px-8 bg-white border-b border-gray-200 shadow-2xs">
                <div>
                    <h1 class="text-xl font-extrabold text-gray-900">Store Performance & Analytics</h1>
                    <p class="text-xs text-gray-400 mt-0.5">Real-time financial breakdown, category share, age demographic metrics, and inventory monitoring.</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-600">Hi, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                    <div class="h-10 w-10 rounded-full bg-yellow-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50/50 p-8 space-y-8">
                
                <!-- KPI Summary Cards Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Card 1: Annual Revenue -->
                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Revenue Generated</p>
                            <h3 class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo formatPrice($totalAnnualRevenue); ?></h3>
                            <span class="inline-flex items-center text-[11px] font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full mt-2">
                                <i class="fa-solid fa-arrow-trend-up mr-1"></i> Active YTD Flow
                            </span>
                        </div>
                        <div class="h-14 w-14 rounded-2xl bg-yellow-50 text-yellow-600 flex items-center justify-center text-2xl border border-yellow-100">
                            <i class="fa-solid fa-wallet"></i>
                        </div>
                    </div>

                    <!-- Card 2: Total Active Products -->
                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Catalog Items</p>
                            <h3 class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo (int)$totalActiveProducts; ?> Products</h3>
                            <span class="inline-flex items-center text-[11px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full mt-2">
                                <i class="fa-solid fa-shirt mr-1"></i> Categorized Stock
                            </span>
                        </div>
                        <div class="h-14 w-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl border border-blue-100">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                    </div>

                    <!-- Card 3: Stock Warnings -->
                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Low Stock Warnings</p>
                            <h3 class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo (int)$lowStockCount; ?> Items</h3>
                            <span class="inline-flex items-center text-[11px] font-semibold <?php echo $lowStockCount > 0 ? 'text-red-600 bg-red-50' : 'text-green-600 bg-green-50'; ?> px-2 py-0.5 rounded-full mt-2">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> <?php echo $lowStockCount > 0 ? 'Needs Restock' : 'All Levels Safe'; ?>
                            </span>
                        </div>
                        <div class="h-14 w-14 rounded-2xl <?php echo $lowStockCount > 0 ? 'bg-red-50 text-red-600 border border-red-100' : 'bg-green-50 text-green-600 border border-green-100'; ?> flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Line Chart (Revenue) & Doughnut Chart (Category Distribution) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Line Graph: Monthly Revenue -->
                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <i class="fa-solid fa-chart-line text-yellow-600"></i> Monthly Revenue Overview
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">Tracking actual earnings performance throughout the year.</p>
                            </div>
                        </div>
                        <div class="relative h-72">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Doughnut Chart: Product Categories Distribution -->
                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <i class="fa-solid fa-tags text-yellow-600"></i> Inventory Breakdown by Category
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">Proportion of items across various boutique collections.</p>
                            </div>
                        </div>
                        <div class="relative h-72 flex justify-center">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Age Range Distribution & Top Selling Bar Chart -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Doughnut Chart: Target Age Range / Size Breakdown -->
                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <i class="fa-solid fa-baby text-yellow-600"></i> Catalog Share by Target Age Range
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">Distribution of products across infant and toddler sizes.</p>
                            </div>
                        </div>
                        <div class="relative h-72 flex justify-center">
                            <canvas id="ageRangeChart"></canvas>
                        </div>
                    </div>

                    <!-- Top Selling Bar Chart -->
                    <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs">
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                            <div>
                                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <i class="fa-solid fa-chart-column text-yellow-600"></i> Top-Selling Products Demand
                                </h3>
                                <p class="text-xs text-gray-400 mt-0.5">Most purchased product models factoring sizes.</p>
                            </div>
                        </div>
                        <div class="relative h-72">
                            <canvas id="productChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row: Stock Alert Widget -->
                <div class="bg-white border border-gray-200/80 rounded-2xl p-6 shadow-2xs">
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-boxes-stacked text-yellow-600"></i> Low Stock Inventory Watch
                        </h3>
                        <a href="products.php" class="text-xs font-bold text-yellow-600 hover:underline">Manage Inventory &rarr;</a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
                        <?php if (!empty($lowStockProducts)): ?>
                            <?php foreach ($lowStockProducts as $item): ?>
                                <div class="flex items-center justify-between p-3.5 bg-gray-50/80 rounded-xl border border-gray-100 text-xs">
                                    <div>
                                        <span class="font-bold text-gray-900 block truncate max-w-[130px]" title="<?php echo htmlspecialchars($item['name']); ?>"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <span class="text-gray-400 text-[11px]">Size: <?php echo htmlspecialchars($item['size'] ?? 'Standard'); ?></span>
                                    </div>
                                    <span class="px-2.5 py-1 font-extrabold rounded-lg <?php echo ($item['stock'] <= 5) ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-yellow-50 text-yellow-700 border border-yellow-200'; ?>">
                                        <?php echo (int)$item['stock']; ?> left
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-full text-center py-6 text-gray-400 text-xs">
                                <i class="fa-regular fa-face-smile text-xl mb-1 text-yellow-500 block"></i>
                                All inventory stock levels are healthy!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Chart.js Script Initializer -->
    <script>
        // Global Chart Defaults for Modern Aesthetics
        Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
        Chart.defaults.color = '#6b7280';

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
                    backgroundColor: 'rgba(202, 138, 4, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ca8a04',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        cornerRadius: 10
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f3f4f6' },
                        ticks: { font: { size: 11 } }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });

        // 2. Doughnut Chart: Categories Distribution
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($categoryData)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($categoryData)); ?>,
                    backgroundColor: ['#eab308', '#3b82f6', '#10b981', '#f97316', '#8b5cf6', '#ec4899'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 }, padding: 15 }
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 12,
                        cornerRadius: 10
                    }
                },
                cutout: '70%'
            }
        });

        // 3. Doughnut Chart: Age Range Distribution
        const ageCtx = document.getElementById('ageRangeChart').getContext('2d');
        new Chart(ageCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($ageData)); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($ageData)); ?>,
                    backgroundColor: ['#f59e0b', '#06b6d4', '#84cc16', '#a855f7', '#f43f5e', '#6366f1'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { 
                        position: 'bottom',
                        labels: { boxWidth: 12, font: { size: 11 }, padding: 15 }
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 12,
                        cornerRadius: 10
                    }
                },
                cutout: '70%'
            }
        });

        // 4. Bar Chart: Top Products Demand with Legend Box Mapping
        // 3. Bar Chart: Top Products Demand with Legend Box Mapping
        const productCtx = document.getElementById('productChart').getContext('2d');
        const topProductsRaw = <?php echo json_encode($topProducts); ?>;
        
        // Use clean X-axis labels to prevent text overlap
        const productLabels = topProductsRaw.map((p, index) => 'Product ' + (index + 1));
        const fullProductNames = topProductsRaw.map(p => p.name + ' (' + (p.size || 'Standard') + ')'); // Fixed .app to .map
        const productSales = topProductsRaw.map(p => p.total_sold);

        const productColors = [
            'rgba(234, 179, 8, 0.85)',
            'rgba(59, 130, 246, 0.85)',
            'rgba(16, 185, 129, 0.85)',
            'rgba(139, 92, 246, 0.85)',
            'rgba(249, 115, 22, 0.85)'
        ];

        const productBorderColors = [
            'rgb(202, 138, 4)',
            'rgb(37, 99, 235)',
            'rgb(5, 150, 105)',
            'rgb(124, 58, 237)',
            'rgb(234, 88, 12)'
        ];

        new Chart(productCtx, {
            type: 'bar',
            data: {
                labels: productLabels,
                datasets: [{
                    label: 'Units Sold',
                    data: productSales,
                    backgroundColor: productColors,
                    borderColor: productBorderColors,
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 12,
                            font: { size: 11, weight: '600' },
                            generateLabels: function(chart) {
                                return fullProductNames.map((name, index) => ({
                                    text: 'P' + (index + 1) + ': ' + name,
                                    fillStyle: productColors[index % productColors.length],
                                    strokeStyle: productBorderColors[index % productBorderColors.length],
                                    lineWidth: 1,
                                    hidden: false,
                                    index: index
                                }));
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            title: function(context) {
                                return fullProductNames[context[0].dataIndex];
                            },
                            label: function(context) {
                                return ' Units Sold: ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { color: '#f3f4f6' },
                        ticks: { font: { size: 11 }, precision: 0 }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: 'bold' } }
                    }
                }
            }
        });
    </script>
</body>
</html>