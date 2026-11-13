<?php
// shop.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Fetch all products from database ordered by newest first
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$pageTitle = "Shop Collection | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-6 py-12">
    <!-- Header Title -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">All Baby Clothes Collection</h1>
        <p class="text-sm text-gray-500 mt-1">Explore our full line of comfortable, organic apparel for your little ones.</p>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $prod): 
                // Check if product is sold out / purchased
                $is_sold_out = (isset($prod['is_available']) && $prod['is_available'] == 0);
            ?>
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between relative <?php echo $is_sold_out ? 'opacity-75 bg-gray-50' : ''; ?>">
                    
                    <?php if ($is_sold_out): ?>
                        <!-- Sold Out Badge Overlay -->
                        <div class="absolute top-3 right-3 z-10 bg-gray-900 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md uppercase tracking-wider">
                            Already Purchased
                        </div>
                    <?php endif; ?>

                    <?php if ($is_sold_out): ?>
                        <!-- Disabled card wrapper for purchased items -->
                        <div class="block cursor-not-allowed">
                    <?php else: ?>
                        <a href="product-detail.php?id=<?php echo $prod['id']; ?>" class="block group">
                    <?php endif; ?>

                        <!-- Product Image -->
                        <div class="h-48 bg-gray-100 flex items-center justify-center overflow-hidden relative">
                            <?php if (!empty($prod['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>" class="w-full h-full object-cover <?php echo !$is_sold_out ? 'group-hover:scale-105 transition-transform duration-300' : 'grayscale'; ?>">
                            <?php else: ?>
                                <div class="text-gray-400 text-4xl"><i class="fa-solid fa-shirt"></i></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="p-5">
                            <span class="text-xs font-semibold uppercase tracking-wider text-yellow-600"><?php echo htmlspecialchars($prod['category']); ?></span>
                            <h3 class="font-bold text-gray-900 mt-1 <?php echo !$is_sold_out ? 'group-hover:text-yellow-600 transition-colors' : ''; ?>"><?php echo htmlspecialchars($prod['name']); ?></h3>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-lg font-extrabold text-gray-900"><?php echo formatPrice($prod['price']); ?></span>
                                
                                <?php if ($is_sold_out): ?>
                                    <span class="text-xs font-medium text-gray-500 bg-gray-200 px-2.5 py-1 rounded-lg">Sold Out</span>
                                <?php else: ?>
                                    <span class="text-xs font-medium text-yellow-600 bg-yellow-50 px-2.5 py-1 rounded-lg">View Details</span>
                                <?php endif; ?>
                            </div>
                        </div>

                    <?php if ($is_sold_out): ?>
                        </div>
                    <?php else: ?>
                        </a>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full py-12 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                <p>No products found in the catalog yet. Check back soon or add some via your admin panel!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>