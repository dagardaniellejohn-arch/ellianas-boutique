<?php
// product-detail.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Fetch product from database
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    $product = false;
}

// Fallback mock data if product doesn't exist in database yet
if (!$product) {
    $product = [
        "id" => 1,
        "name" => "Organic Cotton Romper",
        "price" => 24.00,
        "description" => "Ultra-soft, breathable 100% organic cotton romper designed with snap buttons for easy diaper changes.",
        "category" => "Rompers",
        "image" => ""
    ];
}

$pageTitle = $product['name'] . " | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-6 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <!-- Image View (Fixed to display uploaded product image) -->
        <div class="bg-gray-100 h-96 rounded-2xl overflow-hidden flex items-center justify-center border border-gray-200 shadow-sm">
            <?php if (!empty($product['image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="text-gray-400 text-6xl"><i class="fa-solid fa-shirt"></i></div>
            <?php endif; ?>
        </div>

        <!-- Info & Actions -->
        <div>
            <span class="text-xs font-semibold uppercase tracking-wider text-yellow-600"><?php echo htmlspecialchars($product['category'] ?? 'Baby Wear'); ?></span>
            <h1 class="text-3xl font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="text-2xl font-extrabold text-yellow-600 mt-2"><?php echo formatPrice($product['price']); ?></p>
            <p class="text-gray-600 mt-4 text-sm leading-relaxed"><?php echo htmlspecialchars($product['description']); ?></p>

            <form action="cart.php" method="POST" class="mt-6">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <!-- Size Selector -->
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Size</label>
                <select name="size" class="w-full p-3 border border-gray-300 rounded-xl mb-6 focus:ring-yellow-500 focus:border-yellow-500 text-sm bg-white">
                    <option value="0-3M">0-3 Months</option>
                    <option value="3-6M">3-6 Months</option>
                    <option value="6-12M">6-12 Months</option>
                </select>

                <button type="submit" name="add_to_cart" class="w-full py-3.5 bg-yellow-500 text-white font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors">
                    Add to Cart
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>