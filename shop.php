<?php
// shop.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Capture search and category filter inputs securely
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : '';

try {
    // Fetch unique categories for the sidebar filter
    $catStmt = $pdo->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC");
    $categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

    // Build dynamic query based on search query and category filter
    $query = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (name LIKE ? OR description LIKE ? OR product_code LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($selected_category)) {
        $query .= " AND category = ?";
        $params[] = $selected_category;
    }

    $query .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

} catch (PDOException $e) {
    $products = [];
    $categories = [];
}

$pageTitle = "Shop Collection | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-6 py-10">
    <!-- Header Title & Search Bar Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 pb-6 border-b border-gray-200">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Baby Clothes Collection</h1>
            <p class="text-sm text-gray-500 mt-1">Explore our full line of comfortable, organic apparel for your little ones.</p>
        </div>

        <!-- Search Form -->
        <form action="shop.php" method="GET" class="flex items-center gap-2 w-full md:w-80">
            <?php if (!empty($selected_category)): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($selected_category); ?>">
            <?php endif; ?>
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search apparel or code..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 shadow-2xs">
            </div>
            <button type="submit" class="px-4 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-xl text-sm transition-colors shadow-2xs flex-shrink-0">
                Search
            </button>
            <?php if (!empty($search) || !empty($selected_category)): ?>
                <a href="shop.php" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-sm transition-colors flex-shrink-0" title="Reset Filters">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Main Layout Grid: Smooth Sidebar Filter + Product Catalog -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Non-Intrusive Smooth Sidebar Filter -->
        <aside class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs sticky top-28 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="font-bold text-gray-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-filter text-yellow-600"></i> Filter Categories
                    </h3>
                    <?php if (!empty($selected_category)): ?>
                        <a href="shop.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" class="text-[11px] font-semibold text-yellow-600 hover:underline">Clear</a>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col space-y-1">
                    <!-- All Collections Option -->
                    <a href="shop.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?php echo empty($selected_category) ? 'bg-yellow-500 text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                        <span>All Collections</span>
                        <i class="fa-solid fa-chevron-right text-[10px] opacity-70"></i>
                    </a>

                    <!-- Dynamic Absolute Category Options from Database -->
                    <?php foreach ($categories as $cat): ?>
                        <a href="shop.php?category=<?php echo urlencode($cat); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors <?php echo ($selected_category === $cat) ? 'bg-yellow-500 text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?>">
                            <span><?php echo htmlspecialchars($cat); ?></span>
                            <i class="fa-solid fa-chevron-right text-[10px] opacity-70"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <!-- Product Grid Area -->
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $prod): 
                        // Check if product is sold out / purchased
                        $is_sold_out = (isset($prod['is_available']) && $prod['is_available'] == 0);
                    ?>
                        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-2xs hover:shadow-md transition-shadow flex flex-col justify-between relative <?php echo $is_sold_out ? 'opacity-75 bg-gray-50' : ''; ?>">
                            
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
                                    
                                    <!-- Display Size Pre-Setting Tag -->
                                    <?php if (!empty($prod['size'])): ?>
                                        <div class="mt-1">
                                            <span class="inline-block text-[10px] font-bold bg-gray-100 text-gray-700 px-2 py-0.5 rounded">Size: <?php echo htmlspecialchars($prod['size']); ?></span>
                                        </div>
                                    <?php endif; ?>

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
                    <div class="col-span-full py-16 text-center text-gray-500 bg-white border border-gray-200 rounded-2xl">
                        <i class="fa-solid fa-box-open text-3xl text-gray-300 mb-3 block"></i>
                        <p class="font-semibold text-gray-700">No products found matching your filter or search.</p>
                        <a href="shop.php" class="inline-block mt-3 text-xs font-bold text-yellow-600 bg-yellow-50 px-4 py-2 rounded-xl border border-yellow-200 hover:bg-yellow-100 transition-colors">Clear All Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include 'includes/footer.php'; ?>