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
        "product_code" => "FG-001",
        "name" => "Organic Cotton Romper",
        "price" => 450.00,
        "description" => "Ultra-soft, breathable 100% organic cotton romper designed with snap buttons for easy diaper changes.",
        "category" => "Rompers",
        "size" => "0-3 Months",
        "image" => "",
        "is_available" => 1
    ];
}

$is_sold_out = (isset($product['is_available']) && $product['is_available'] == 0);
$pcode = !empty($product['product_code']) ? $product['product_code'] : 'FG-' . str_pad($product['id'], 3, '0', STR_PAD_LEFT);

$pageTitle = $product['name'] . " | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-6 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <!-- Image View (Fixed to display uploaded product image with sold out state) -->
        <div class="bg-gray-100 h-96 rounded-2xl overflow-hidden flex items-center justify-center border border-gray-200 shadow-sm relative">
            <?php if (!empty($product['image'])): ?>
                <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover <?php echo $is_sold_out ? 'grayscale' : ''; ?>">
            <?php else: ?>
                <div class="text-gray-400 text-6xl"><i class="fa-solid fa-shirt"></i></div>
            <?php endif; ?>

            <?php if ($is_sold_out): ?>
                <div class="absolute top-4 right-4 z-10 bg-gray-900 text-white text-xs font-bold px-3.5 py-1.5 rounded-full shadow-md uppercase tracking-wider">
                    Already Purchased
                </div>
            <?php endif; ?>
        </div>

        <!-- Info & Actions -->
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="font-mono text-xs font-bold text-yellow-700 bg-yellow-50 border border-yellow-200 px-2 py-0.5 rounded-md"><?php echo htmlspecialchars($pcode); ?></span>
                <span class="text-xs font-semibold uppercase tracking-wider text-yellow-600">• <?php echo htmlspecialchars($product['category'] ?? 'Baby Wear'); ?></span>
            </div>

            <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="text-2xl font-extrabold text-yellow-600 mt-2"><?php echo formatPrice($product['price']); ?></p>
            
            <!-- Display Assigned Size Pre-Setting -->
            <div class="mt-3">
                <span class="inline-flex items-center text-xs font-semibold bg-gray-100 text-gray-800 px-3 py-1 rounded-lg">
                    <i class="fa-solid fa-ruler-combined mr-1.5 text-gray-500"></i> Size: <?php echo htmlspecialchars($product['size'] ?? '0-3 Months'); ?>
                </span>
            </div>

            <p class="text-gray-600 mt-4 text-sm leading-relaxed"><?php echo htmlspecialchars($product['description']); ?></p>

            <?php if ($is_sold_out): ?>
                <div class="mt-6 p-4 bg-gray-100 border border-gray-200 rounded-xl text-center">
                    <p class="text-sm font-bold text-gray-700"><i class="fa-solid fa-lock text-gray-500 mr-1.5"></i> This item has already been purchased and is currently sold out.</p>
                </div>
            <?php else: ?>
                <form action="cart.php" method="POST" class="mt-6">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="size" value="<?php echo htmlspecialchars($product['size'] ?? '0-3 Months'); ?>">

                    <button type="submit" name="add_to_cart" class="w-full py-3.5 bg-yellow-500 text-white font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors flex items-center justify-center gap-2">
                        <i class="fa-solid fa-cart-shopping"></i> Add to Cart
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>