<?php
// admin/products.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security check: Verify admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle Add Product Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    
    if (empty($name) || empty($category) || empty($price)) {
        $error = "Please fill in all required product fields.";
    } else {
        try {
            // 1. Auto-generate product code (e.g., FG-001, FG-002)
            $stmt = $pdo->query("SELECT MAX(id) as max_id FROM products");
            $row = $stmt->fetch();
            $nextId = ($row && $row['max_id']) ? $row['max_id'] + 1 : 1;
            $product_code = 'FG-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

            // 2. Handle Image Upload
            $imageName = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['image']['tmp_name'];
                $fileName = $_FILES['image']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                if (in_array($fileExtension, $allowedExtensions)) {
                    $imageName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $fileName);
                    $uploadFileDir = '../uploads/';
                    if (!is_dir($uploadFileDir)) {
                        mkdir($uploadFileDir, 0755, true);
                    }
                    move_uploaded_file($fileTmpPath, $uploadFileDir . $imageName);
                }
            }

            // 3. Insert into Database with is_available = 1
            $insertStmt = $pdo->prepare("INSERT INTO products (product_code, name, category, price, image, description, is_available) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $insertStmt->execute([$product_code, $name, $category, $price, $imageName, $description]);
            
            $success = "Product successfully added with code " . $product_code . "!";
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch all products from database ordered by newest first
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$pageTitle = "Product Management | Elliana's Boutique";
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
                    <a href="products.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg bg-yellow-500 text-white">
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
                <h1 class="text-xl font-bold text-gray-800">Product Management</h1>
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-600">Store Admin</span>
                    <div class="h-8 w-8 rounded-full bg-yellow-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        A
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 space-y-6">
                
                <!-- Alerts -->
                <?php if (!empty($success)): ?>
                    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
                        <i class="fa-solid fa-circle-check mr-2"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <!-- Add New Product Form Card -->
                <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-plus-circle text-yellow-600"></i> Add New Baby Product (Auto Code: FG-XXX)
                    </h2>
                    <form action="products.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Product Name</label>
                            <input type="text" name="name" required class="w-full px-4 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" placeholder="e.g. Organic Frog Romper">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Category</label>
                            <input type="text" name="category" required class="w-full px-4 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" placeholder="e.g. Rompers, Outerwear">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Price (PHP)</label>
                            <input type="number" step="0.01" name="price" required class="w-full px-4 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none" placeholder="450.00">
                        </div>
                        
                        <!-- Expanded Full-Width Description Field -->
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Description</label>
                            <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-yellow-500 focus:outline-none resize-none" placeholder="Enter comfortable organic apparel details, sizing notes, or washing instructions..."></textarea>
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold uppercase text-gray-600 mb-1">Product Image</label>
                            <label id="dropzone-label" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-2xl cursor-pointer bg-gray-50 hover:bg-yellow-50/50 hover:border-yellow-500 transition-all group">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center">
                                    <div class="h-10 w-10 bg-yellow-100 text-yellow-700 rounded-xl flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                        <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                                    </div>
                                    <p class="mb-1 text-xs font-semibold text-gray-700"><span class="text-yellow-600">Click to upload</span> or drag and drop</p>
                                    <p id="file-name-display" class="text-[11px] text-gray-400">SVG, PNG, JPG, or WEBP (MAX. 5MB)</p>
                                </div>
                                <input type="file" name="image" id="product-image-input" accept="image/*" class="hidden" onchange="updateFileName(this)">
                            </label>
                        </div>

                        <!-- JavaScript to show selected file name -->
                        <script>
                        function updateFileName(input) {
                            const display = document.getElementById('file-name-display');
                            if (input.files && input.files[0]) {
                                display.textContent = "Selected: " + input.files[0].name;
                                display.classList.add('text-yellow-700', 'font-bold');
                            } else {
                                display.textContent = "SVG, PNG, JPG, or WEBP (MAX. 5MB)";
                                display.classList.remove('text-yellow-700', 'font-bold');
                            }
                        }
                        </script>
                        <div class="md:col-span-3 flex justify-end">
                            <button type="submit" name="add_product" class="px-6 py-2.5 bg-yellow-500 text-white font-semibold rounded-xl text-sm hover:bg-yellow-600 transition-colors shadow-sm">
                                <i class="fa-solid fa-save mr-1.5"></i> Save & Generate Product Code
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Existing Products Inventory Table -->
                <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Existing Products Inventory</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-700 text-xs font-bold uppercase tracking-wider border-b">
                                    <th class="py-3 px-6">Image / Code</th>
                                    <th class="py-3 px-6">Product Name</th>
                                    <th class="py-3 px-6">Category</th>
                                    <th class="py-3 px-6">Price</th>
                                    <th class="py-3 px-6">Status Flag</th>
                                    <th class="py-3 px-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm">
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $prod): 
                                        $is_purchased = (isset($prod['is_available']) && $prod['is_available'] == 0);
                                        $pcode = !empty($prod['product_code']) ? $prod['product_code'] : 'FG-' . str_pad($prod['id'], 3, '0', STR_PAD_LEFT);
                                    ?>
                                        <tr class="<?php echo $is_purchased ? 'bg-gray-50/80' : ''; ?>">
                                            <td class="py-4 px-6 flex items-center space-x-3">
                                                <div class="h-12 w-12 bg-gray-100 rounded-xl overflow-hidden flex items-center justify-center flex-shrink-0">
                                                    <?php if (!empty($prod['image'])): ?>
                                                        <img src="../uploads/<?php echo htmlspecialchars($prod['image']); ?>" class="h-full w-full object-cover <?php echo $is_purchased ? 'grayscale' : ''; ?>" alt="">
                                                    <?php else: ?>
                                                        <i class="fa-solid fa-shirt text-gray-400"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="py-4 px-6">
                                                <span class="font-mono text-xs font-bold text-yellow-700 bg-yellow-50 px-2.5 py-1 rounded-md"><?php echo $pcode; ?></span>
                                                <div class="font-bold text-gray-900 mt-1"><?php echo htmlspecialchars($prod['name']); ?></div>
                                            </td>
                                            <td class="py-4 px-6 text-gray-600"><?php echo htmlspecialchars($prod['category']); ?></td>
                                            <td class="py-4 px-6 font-extrabold text-gray-900"><?php echo formatPrice($prod['price']); ?></td>
                                            <td class="py-4 px-6">
                                                <?php if ($is_purchased): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gray-200 text-gray-800">
                                                        <i class="fa-solid fa-lock mr-1.5 text-[10px]"></i> Purchased / Sold Out
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700">
                                                        <i class="fa-solid fa-check mr-1.5 text-[10px]"></i> Available
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-4 px-6 text-right">
                                                <a href="delete-product.php?id=<?php echo $prod['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" class="text-red-600 hover:text-red-800 font-medium text-xs bg-red-50 px-3 py-1.5 rounded-lg transition-colors">
                                                    Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-gray-500">No products found in inventory. Use the form above to add one!</td>
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