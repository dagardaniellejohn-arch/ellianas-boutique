<?php
// admin/orders.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security check: Verify admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

// Fetch all orders with user details, shipping destination, and associated order items / products
try {
    $stmt = $pdo->query("
        SELECT o.*, u.fullname, u.email,
               GROUP_CONCAT(p.id) as product_ids,
               GROUP_CONCAT(p.name) as product_names,
               GROUP_CONCAT(oi.quantity) as product_quantities
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN products p ON oi.product_id = p.id
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

$pageTitle = "Manage Orders | Elliana's Boutique Admin";
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
                    <a href="dashboard.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-house mr-3"></i> Dashboard
                    </a>
                    <a href="analytics.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-chart-pie mr-3"></i> Analytics
                    </a>
                    <a href="products.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-shirt mr-3"></i> Products
                    </a>
                    <a href="orders.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg bg-yellow-500 text-white">
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
                <h1 class="text-xl font-bold text-gray-800">Customer Orders</h1>
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-600">Hi, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                    <div class="h-8 w-8 rounded-full bg-yellow-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 space-y-6">
                <!-- Orders Table -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">All Placed Orders</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                                    <th class="px-6 py-3">Order ID</th>
                                    <th class="px-6 py-3">Customer</th>
                                    <th class="px-6 py-3">Delivery Address</th>
                                    <th class="px-6 py-3">Purchased Products</th>
                                    <th class="px-6 py-3">Shipping & Payment</th>
                                    <th class="px-6 py-3">Total Amount</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3">Date Placed</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                <?php if (!empty($orders)): ?>
                                    <?php foreach ($orders as $ord): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 font-bold text-gray-900 align-top">#<?php echo $ord['id']; ?></td>
                                        <td class="px-6 py-4 align-top">
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($ord['fullname'] ?? 'Guest Customer'); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($ord['email'] ?? ''); ?></div>
                                        </td>
                                        <!-- Delivery Address Column -->
                                        <td class="px-6 py-4 align-top">
                                            <div class="text-xs font-medium text-gray-900">
                                                <i class="fa-solid fa-location-dot text-yellow-600 mr-1"></i> 
                                                <?php echo htmlspecialchars($ord['address'] ?? 'No address provided'); ?>
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                <?php echo htmlspecialchars($ord['city'] ?? ''); ?> <?php echo !empty($ord['zip']) ? '(' . htmlspecialchars($ord['zip']) . ')' : ''; ?>
                                            </div>
                                        </td>
                                        <!-- Purchased Products Column -->
                                        <td class="px-6 py-4 align-top">
                                            <?php if (!empty($ord['product_ids'])): ?>
                                                <?php 
                                                    $ids = explode(',', $ord['product_ids']);
                                                    $names = explode(',', $ord['product_names']);
                                                ?>
                                                <div class="space-y-1">
                                                    <?php for ($i = 0; $i < count($ids); $i++): ?>
                                                        <div class="text-xs">
                                                            <span class="font-mono font-bold text-yellow-700 bg-yellow-50 border border-yellow-200 px-1.5 py-0.5 rounded">FG-019</span>
                                                            <span class="font-medium text-gray-800 ml-1"><?php echo htmlspecialchars($names[$i] ?? 'Product'); ?></span>
                                                        </div>
                                                    <?php endfor; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-gray-400 italic">No items recorded</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 align-top">
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($ord['shipping_method'] ?? 'Standard Delivery'); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo htmlspecialchars($ord['payment_method'] ?? 'COD'); ?></div>
                                        </td>
                                        <td class="px-6 py-4 font-extrabold text-yellow-600 align-top"><?php echo formatPrice($ord['total_amount']); ?></td>
                                        <td class="px-6 py-4 align-top">
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                <?php echo htmlspecialchars($ord['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 text-xs align-top"><?php echo htmlspecialchars($ord['created_at']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">No orders have been placed yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>