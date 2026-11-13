<?php
// admin/dashboard.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security check: Verify admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// Handle clearing notifications
if (isset($_GET['action']) && $_GET['action'] === 'clear_notifications') {
    $_SESSION['notifications_cleared_time'] = time();
    redirect('dashboard.php');
}

// Fetch stats for dashboard
try {
    $userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    
    $revenue = $pdo->query("SELECT SUM(total_amount) FROM orders")->fetchColumn();
    $revenue = $revenue ? $revenue : 0;

    // Fetch recent purchased orders for the admin notification alert feed
    $orderStmt = $pdo->query("SELECT o.*, u.fullname FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC LIMIT 5");
    $recent_orders = $orderStmt->fetchAll();
} catch (PDOException $e) {
    $userCount = 0;
    $productCount = 0;
    $orderCount = 0;
    $revenue = 0;
    $recent_orders = [];
}

// If notifications were cleared, check if we should hide them or filter out orders placed before clear time
// (Alternatively, if you want a clean wipe of the visible list until new orders arrive, we check timestamps)
if (isset($_SESSION['notifications_cleared_time'])) {
    $cleared_time = $_SESSION['notifications_cleared_time'];
    // Filter out orders that were created before the clear action
    $recent_orders = array_filter($recent_orders, function($order) use ($cleared_time) {
        return strtotime($order['created_at']) > $cleared_time;
    });
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
                <!-- Logo Branding Header -->
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
                    <a href="dashboard.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg bg-yellow-500 text-white shadow-xs">
                        <i class="fa-solid fa-house mr-3"></i> Dashboard
                    </a>
                    <a href="analytics.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
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
                <div class="p-4 bg-gray-950 border-t border-gray-800">
                    <a href="../logout.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors">
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

                <!-- Admin Notification Feed Section -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fa-solid fa-bell text-yellow-600"></i> Recent Order Notifications & Purchases
                        </h2>
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-yellow-50 text-yellow-700 font-semibold px-3 py-1 rounded-full">Live Alerts</span>
                            <?php if (!empty($recent_orders)): ?>
                                <a href="dashboard.php?action=clear_notifications" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-3 py-1.5 rounded-xl transition-colors flex items-center gap-1.5">
                                    <i class="fa-solid fa-trash-can text-gray-500"></i> Clear All
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($recent_orders)): ?>
                        <div class="space-y-3">
                            <?php foreach ($recent_orders as $order): ?>
                                <div class="flex items-center justify-between p-3.5 bg-gray-50 border border-gray-100 rounded-xl text-sm">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-9 w-9 bg-yellow-100 text-yellow-700 rounded-lg flex items-center justify-center font-bold">
                                            <i class="fa-solid fa-bag-shopping"></i>
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900">New Order #<?php echo $order['id']; ?> placed by <span class="text-yellow-600"><?php echo htmlspecialchars($order['fullname']); ?></span></p>
                                            <p class="text-xs text-gray-500">Shipping via <?php echo htmlspecialchars($order['shipping_method']); ?> • <?php echo formatPrice($order['total_amount']); ?></p>
                                        </div>
                                    </div>
                                    <span class="text-xs font-semibold px-2.5 py-1 bg-green-50 text-green-700 rounded-lg"><?php echo htmlspecialchars($order['status']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="py-8 text-center text-gray-400 text-sm">
                            <i class="fa-regular fa-bell-slash text-2xl mb-2 block"></i>
                            No recent purchase notifications.
                        </div>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>
</body>
</html>