<?php
// my-orders.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require customer to be logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch orders belonging to the logged-in customer
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

$pageTitle = "My Purchases | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-6 py-12">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Order History</h1>
        <p class="text-sm text-gray-500 mt-1">Track the status of your baby clothes purchases and view past receipts.</p>
    </div>

    <?php if (!empty($orders)): ?>
        <div class="space-y-6">
            <?php foreach ($orders as $order): ?>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b pb-4 gap-2">
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase">Order ID</span>
                            <h3 class="text-lg font-bold text-gray-900">#<?php echo $order['id']; ?></h3>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div>
                                <span class="text-xs text-gray-500 block">Date Placed</span>
                                <span class="text-sm font-medium text-gray-800"><?php echo htmlspecialchars($order['created_at']); ?></span>
                            </div>
                            <!-- PUT IT HERE -->
                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fa-solid fa-check-circle mr-1"></i> Purchased
                            </span>
                        </div>
                    </div>

                    <!-- Fetch individual items for this order -->
                    <?php
                    try {
                        $itemStmt = $pdo->prepare("
                            SELECT order_items.*, products.name, products.image 
                            FROM order_items 
                            JOIN products ON order_items.product_id = products.id 
                            WHERE order_items.order_id = ?
                        ");
                        $itemStmt->execute([$order['id']]);
                        $orderItems = $itemStmt->fetchAll();
                    } catch (PDOException $e) {
                        $orderItems = [];
                    }
                    ?>

                    <div class="divide-y divide-gray-100">
                        <?php foreach ($orderItems as $item): ?>
                            <div class="py-3 flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="h-12 w-12 bg-gray-100 rounded-xl overflow-hidden flex items-center justify-center flex-shrink-0">
                                        <?php if (!empty($item['image'])): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Item" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i class="fa-solid fa-shirt text-gray-400"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($item['name']); ?></p>
                                        <p class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                                    </div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t pt-4 flex flex-col sm:flex-row justify-between items-start sm:items-center text-sm gap-2">
                        <div class="text-gray-500 text-xs">
                            <span>Courier: <strong class="text-gray-800"><?php echo htmlspecialchars($order['shipping_method'] ?? 'Standard'); ?></strong></span> | 
                            <span>Payment: <strong class="text-gray-800"><?php echo htmlspecialchars($order['payment_method'] ?? 'COD'); ?></strong></span>
                        </div>
                        <div class="text-right">
                            <span class="text-gray-500 text-xs mr-2">Total Amount Paid:</span>
                            <span class="text-lg font-extrabold text-yellow-600"><?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center shadow-sm">
            <div class="h-16 w-16 bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">No orders found</h2>
            <p class="text-sm text-gray-500 mt-1">You haven't placed any orders yet. Start shopping our cute collection!</p>
            <div class="mt-6">
                <a href="shop.php" class="px-6 py-3 bg-yellow-500 text-white font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors">Browse Shop</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>