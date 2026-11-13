<?php
// cart.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login before buying/adding to cart
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

// RESTRICT ADMIN FROM PURCHASING
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Redirect admin back to their dashboard or show an error
    $_SESSION['error'] = "Administrators cannot place orders or purchase items from the store front.";
    redirect('admin/dashboard.php');
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ... rest of your cart code continues below ...

// Handle adding product to cart from product-detail.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $size = sanitize($_POST['size']);
    
    // Create a unique cart key combining ID and size
    $cart_key = $product_id . '_' . $size;

    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key]['quantity'] += 1;
    } else {
        // Fetch product details for the cart
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $prod = $stmt->fetch();
            
            if ($prod) {
                $_SESSION['cart'][$cart_key] = [
                    'id' => $prod['id'],
                    'name' => $prod['name'],
                    'price' => $prod['price'],
                    'size' => $size,
                    'quantity' => 1,
                    'image' => $prod['image'] ?? ''
                ];
            }
        } catch (PDOException $e) {
            // Fallback mock item if DB fails
            $_SESSION['cart'][$cart_key] = [
                'id' => $product_id,
                'name' => 'Organic Cotton Romper',
                'price' => 24.00,
                'size' => $size,
                'quantity' => 1,
                'image' => ''
            ];
        }
    }
    redirect('cart.php');
}

// Handle removing or clearing items
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['key'])) {
    $key = $_GET['key'];
    unset($_SESSION['cart'][$key]);
    redirect('cart.php');
}

$pageTitle = "Shopping Cart | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-6 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Your Shopping Cart</h1>

    <?php if (!empty($_SESSION['cart'])): ?>
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden mb-6">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold border-b border-gray-200">
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">Size</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4">Quantity</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                    <?php 
                    $subtotal = 0;
                    foreach ($_SESSION['cart'] as $key => $item): 
                        $itemTotal = $item['price'] * $item['quantity'];
                        $subtotal += $itemTotal;
                    ?>
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900 flex items-center space-x-3">
                            <div class="h-12 w-12 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center text-gray-400 flex-shrink-0">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($item['image']); ?>" alt="Item" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fa-solid fa-shirt"></i>
                                <?php endif; ?>
                            </div>
                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-500"><?php echo htmlspecialchars($item['size']); ?></td>
                        <td class="px-6 py-4 text-gray-900 font-semibold"><?php echo formatPrice($item['price']); ?></td>
                        <td class="px-6 py-4 text-gray-600"><?php echo $item['quantity']; ?></td>
                        <td class="px-6 py-4 text-right">
                            <a href="cart.php?action=remove&key=<?php echo urlencode($key); ?>" class="text-red-600 hover:text-red-900 text-sm font-medium">Remove</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Cart Summary & Checkout -->
        <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500">Subtotal Amount:</p>
                <p class="text-2xl font-extrabold text-yellow-600"><?php echo formatPrice($subtotal); ?></p>
            </div>
            <div class="flex space-x-4 w-full sm:w-auto">
                <a href="shop.php" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-xl text-center hover:bg-gray-50 transition-colors">Continue Shopping</a>
                <a href="checkout.php" class="px-6 py-3 bg-yellow-500 text-white font-medium rounded-xl text-center shadow hover:bg-yellow-600 transition-colors">Proceed to Checkout</a>
            </div>
        </div>

    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center shadow-sm">
            <div class="h-16 w-16 bg-yellow-50 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Your cart is empty</h2>
            <p class="text-sm text-gray-500 mt-1">Looks like you haven't added any baby clothes to your cart yet.</p>
            <div class="mt-6">
                <a href="shop.php" class="px-6 py-3 bg-yellow-500 text-white font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors">Start Shopping</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>