<?php
// checkout.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login before checking out
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// RESTRICT ADMIN FROM PURCHASING
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $_SESSION['error'] = "Administrators cannot place orders or purchase items from the store front.";
    redirect('admin/dashboard.php');
}

// If cart is empty, redirect back to shop
if (empty($_SESSION['cart'])) {
    redirect('shop.php');
}

$error = '';
$order_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize($_POST['fullname'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $zip = sanitize($_POST['zip'] ?? '');
    $shipping_method = sanitize($_POST['shipping_method'] ?? 'Standard Shipping');
    $payment_method = sanitize($_POST['payment_method'] ?? 'GCash');

    // Set shipping fee based on selection
    $shipping_fee = ($shipping_method === 'J&T Express') ? 150.00 : 80.00;

    if (empty($fullname) || empty($email) || empty($address) || empty($city) || empty($zip)) {
        $error = "Please fill in all shipping details.";
    } else {
        try {
            // Calculate items subtotal
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            $total_amount = $subtotal + $shipping_fee;

            $user_id = $_SESSION['user_id'];

            // Begin transaction
            $pdo->beginTransaction();

            // 1. Insert into orders table
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_method, shipping_fee, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, 'Purchased', NOW())");
            $stmt->execute([$user_id, $total_amount, $shipping_method, $shipping_fee, $payment_method]);
            $order_id = $pdo->lastInsertId();

            // 2. Prepare statements for order items and marking package unavailable globally
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $availabilityStmt = $pdo->prepare("UPDATE products SET is_available = 0 WHERE id = ?");
            
            foreach ($_SESSION['cart'] as $item) {
                // Save order item record
                $itemStmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
                
                // Globally lock the package bundle so it shows as sold out for everyone
                $availabilityStmt->execute([$item['id']]);
            }

            // Commit transaction
            $pdo->commit();

            // Clear the cart
            $_SESSION['cart'] = [];
            $order_success = true;

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Checkout error: " . $e->getMessage();
        }
    }
}

// Calculate subtotal for review display
$current_subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $current_subtotal += $item['price'] * $item['quantity'];
}

$pageTitle = "Checkout | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Checkout</h1>

    <?php if ($order_success): ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center shadow-sm">
            <div class="h-16 w-16 bg-green-50 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-check"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Order Placed Successfully!</h2>
            <p class="text-sm text-gray-500 mt-2">Thank you for shopping with Elliana's Boutique. We are packing your order with care.</p>
            <div class="mt-6">
                <a href="index.php" class="px-6 py-3 bg-yellow-500 text-white font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors">Return to Homepage</a>
            </div>
        </div>
    <?php else: ?>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="checkout.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Left 2 Columns: Shipping & Payment Info -->
            <div class="md:col-span-2 space-y-6">
                <!-- Shipping Details -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-gray-900">1. Shipping Information</h2>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?>" required class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" name="email" required placeholder="name@example.com" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Street Address / Barangay</label>
                        <input type="text" name="address" required placeholder="House No., Street, Barangay" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">City / Municipality</label>
                            <input type="text" name="city" required placeholder="e.g., Quezon City" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Postal / Zip Code</label>
                            <input type="text" name="zip" required placeholder="1100" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                    </div>
                </div>

                <!-- Shipping Options -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-gray-900">2. Choose Shipping Courier</h2>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-yellow-500 transition-colors">
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="shipping_method" value="J&T Express" checked class="text-yellow-500 focus:ring-yellow-500">
                                <div>
                                    <p class="font-bold text-gray-900 text-sm">J&T Express Courier</p>
                                    <p class="text-xs text-gray-500">1-3 business days (Express tracking)</p>
                                </div>
                            </div>
                            <span class="font-semibold text-gray-900 text-sm">₱150.00</span>
                        </label>
                    </div>
                </div>

                <!-- Payment Gateways -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
                    <h2 class="text-lg font-bold text-gray-900">3. Select Payment Method</h2>
                    <div class="space-y-3">
                        <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-yellow-500 transition-colors">
                            <input type="radio" name="payment_method" value="GCash" checked class="text-yellow-500 focus:ring-yellow-500">
                            <div>
                                <p class="font-bold text-gray-900 text-sm">GCash E-Wallet</p>
                                <p class="text-xs text-gray-500">Scan QR code or direct mobile wallet transfer</p>
                            </div>
                        </label>

                        <label class="flex items-center space-x-3 p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-yellow-500 transition-colors">
                            <input type="radio" name="payment_method" value="Credit/Debit Card" class="text-yellow-500 focus:ring-yellow-500">
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Credit or Debit Card (Maya / Visa / Mastercard)</p>
                                <p class="text-xs text-gray-500">Instant online secure card processing</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary Box -->
            <div>
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm sticky top-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 border-b pb-3">Order Summary</h2>
                    
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="flex justify-between text-xs text-gray-600">
                                <span><?php echo htmlspecialchars($item['name']); ?> (<?php echo $item['size']; ?>) x <?php echo $item['quantity']; ?></span>
                                <span class="font-medium text-gray-900"><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t pt-3 space-y-2 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($current_subtotal); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping Fee</span>
                            <span>Calculated at checkout</span>
                        </div>
                        <div class="flex justify-between text-base font-extrabold text-gray-900 pt-2 border-t">
                            <span>Total Payable</span>
                            <span class="text-yellow-600"><?php echo formatPrice($current_subtotal); ?>+</span>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3.5 bg-yellow-500 text-white font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors mt-4">
                        Place Order Now
                    </button>
                </div>
            </div>
        </form>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>