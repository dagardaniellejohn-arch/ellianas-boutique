<?php
// admin/dashboard.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security check: Verify admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// Fetch stats for dashboard
try {
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    
    $revenue = $pdo->query("SELECT SUM(total_amount) FROM orders")->fetchColumn();
    $revenue = $revenue ? $revenue : 0;
} catch (PDOException $e) {
    $userCount = 0;
    $productCount = 0;
    $orderCount = 0;
    $revenue = 0;
}

$pageTitle = "Admin Dashboard | Elliana's Boutique";
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
</head>
<body class="bg-gray-50 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-gray-900 text-gray-300">
                <div class="flex items-center justify-center h-16 bg-gray-950 px-4">
                    <span class="text-xl font-bold text-white tracking-wider flex items-center">
                        <i class="fa-solid fa-baby mr-2 text-yellow-400"></i> Elliana Admin
                    </span>
                </div>
                <div class="flex flex-col flex-grow px-4 py-6 space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg bg-yellow-500 text-white">
                        <i class="fa-solid fa-house mr-3"></i> Dashboard
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
                <h1 class="text-xl font-bold text-gray-800">Dashboard Overview</h1>
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-600">Hi, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                    <div class="h-8 w-8 rounded-full bg-yellow-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 space-y-6">
                <!-- Welcome Banner -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-yellow-900">Welcome to Elliana's Boutique Admin Panel</h2>
                        <p class="text-sm text-yellow-700 mt-1">Manage your inventory, customers, and store metrics right from here.</p>
                    </div>
                    <a href="products.php" class="hidden sm:inline-block px-4 py-2.5 bg-yellow-500 text-white text-sm font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors">
                        Add New Product
                    </a>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Products Metric -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Products</p>
                                <p class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo $productCount; ?></p>
                            </div>
                            <div class="h-12 w-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center text-lg">
                                <i class="fa-solid fa-shirt"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Orders Metric -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Orders</p>
                                <p class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo $orderCount; ?></p>
                            </div>
                            <div class="h-12 w-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center text-lg">
                                <i class="fa-solid fa-box-archive"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Users Metric -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Users</p>
                                <p class="text-2xl font-extrabold text-gray-900 mt-1"><?php echo $userCount; ?></p>
                            </div>
                            <div class="h-12 w-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center text-lg">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Metric -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Revenue</p>
                                <p class="text-2xl font-extrabold text-yellow-600 mt-1"><?php echo formatPrice($revenue); ?></p>
                            </div>
                            <div class="h-12 w-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center text-lg">
                                <i class="fa-solid fa-wallet"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>