<?php
// index.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Fetch latest baby clothes from database
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 4");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = []; // Fallback if table doesn't exist yet
}

$pageTitle = "Elliana's Boutique | Cute & Organic Baby Clothes";
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="bg-yellow-50 py-16 px-6 text-center border-b border-yellow-100">
    <div class="max-w-2xl mx-auto">
        <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-3 py-1 rounded-full uppercase tracking-wider">New Summer Collection</span>
        <h1 class="text-4xl font-extrabold text-gray-900 mt-4">Gentle on Baby Skin, Bright on Style</h1>
        <p class="text-gray-600 mt-2">Discover 100% organic cotton onesies, rompers, and sleepsuits crafted for your little one's comfort.</p>
        <div class="mt-6">
            <a href="shop.php" class="px-6 py-3 bg-yellow-500 text-white font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors">Shop Collection</a>
        </div>
    </div>
</div>

<!-- Featured Products Section -->
<div class="max-w-7xl mx-auto px-6 py-12">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Baby Clothes</h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $prod): ?>
                <?php
                // Check if the current logged-in user has already bought this product
                $has_purchased = false;
                if (isset($_SESSION['user_id'])) {
                    try {
                        $checkStmt = $pdo->prepare("
                            SELECT COUNT(*) FROM order_items 
                            JOIN orders ON order_items.order_id = orders.id 
                            WHERE orders.user_id = ? AND order_items.product_id = ?
                        ");
                        $checkStmt->execute([$_SESSION['user_id'], $prod['id']]);
                        $has_purchased = ($checkStmt->fetchColumn() > 0);
                    } catch (PDOException $e) {
                        $has_purchased = false;
                    }
                }
                ?>
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden relative">
                        <?php if (!empty($prod['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="text-gray-400 text-4xl"><i class="fa-solid fa-shirt"></i></div>
                        <?php endif; ?>

                        <?php if ($has_purchased): ?>
                            <!-- Overlay Badge for Homepage -->
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                <span class="px-4 py-1.5 bg-yellow-400 text-gray-900 font-bold text-xs rounded-full shadow-lg">
                                    <i class="fa-solid fa-check mr-1"></i> Already Purchased
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="p-5 flex flex-col justify-between flex-grow">
                        <div>
                            <span class="text-xs font-semibold uppercase tracking-wider text-yellow-600"><?php echo htmlspecialchars($prod['category'] ?? 'Baby Clothes'); ?></span>
                            <h3 class="font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($prod['name']); ?></h3>
                            <p class="text-lg font-extrabold text-gray-900 mt-2"><?php echo formatPrice($prod['price']); ?></p>
                        </div>

                        <div class="mt-4">
                            <?php if ($has_purchased): ?>
                                <!-- Disabled Button if Already Bought -->
                                <button disabled class="block w-full py-2 bg-gray-100 text-gray-400 text-sm font-medium rounded-lg text-center cursor-not-allowed">
                                    Purchased
                                </button>
                            <?php else: ?>
                                <!-- Active Link to Details/Cart -->
                                <a href="product-detail.php?id=<?php echo $prod['id']; ?>" class="block text-center w-full py-2 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600 transition-colors">
                                    View Details
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm p-4 col-span-full text-center py-12 text-gray-500">
                <p>No featured products found yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>