<?php
// admin/products.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security check: Verify admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

$error = '';
$success = '';

// Handle adding new product with image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = sanitize($_POST['name']);
    $price = (float)$_POST['price'];
    $category = sanitize($_POST['category']);
    $description = sanitize($_POST['description']);
    $imageName = '';

    // Handle Image Upload
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['product_image']['tmp_name'];
        $fileName = $_FILES['product_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Allowed extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            // Create unique filename to prevent overwriting
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../uploads/';
            
            // Ensure uploads folder exists
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $dest_path = $uploadFileDir . $newFileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imageName = $newFileName;
            } else {
                $error = "Error moving the uploaded file.";
            }
        } else {
            $error = "Invalid file type. Please upload JPG, JPEG, PNG, or WEBP images.";
        }
    }

    if (empty($error)) {
        if (empty($name) || $price <= 0 || empty($category)) {
            $error = "Please provide a valid product name, price, and category.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, price, category, description, image, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                if ($stmt->execute([$name, $price, $category, $description, $imageName])) {
                    $success = "Product added successfully!";
                } else {
                    $error = "Failed to add product to database.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Handle deleting product
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $prod_id = (int)$_GET['id'];
    try {
        // Fetch image to delete from server folder
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$prod_id]);
        $prod = $stmt->fetch();
        if ($prod && !empty($prod['image']) && file_exists('../uploads/' . $prod['image'])) {
            unlink('../uploads/' . $prod['image']);
        }

        $delStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $delStmt->execute([$prod_id]);
        redirect('products.php');
    } catch (PDOException $e) {
        $error = "Failed to delete product.";
    }
}

// Fetch all products
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$pageTitle = "Manage Products | Elliana's Boutique Admin";
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
<body class="bg-gray-100 font-sans antialiased">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar Navigation -->
        <aside class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-gray-900 text-gray-300">
                <div class="flex items-center justify-center h-16 bg-gray-950 px-4">
                    <span class="text-xl font-bold text-white tracking-wider flex items-center">
                        <i class="fa-solid fa-baby mr-2 text-yellow-400"></i> Elliana Admin
                    </span>
                </div>
                <div class="flex flex-col flex-grow px-4 py-6 space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-house mr-3"></i> Dashboard
                    </a>
                    <a href="products.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg bg-yellow-500 text-white">
                        <i class="fa-solid fa-shirt mr-3"></i> Products
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
                    <span class="text-sm font-medium text-gray-600"><?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                    <div class="h-8 w-8 rounded-full bg-yellow-500 text-white flex items-center justify-center font-bold text-sm">
                        <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 space-y-8">
                <?php if (!empty($error)): ?>
                    <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="p-4 bg-green-50 border border-green-200 text-green-600 rounded-xl text-sm"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Add New Product Form (Added enctype for file uploads) -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Add New Baby Clothing Item</h2>
                    <form action="products.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product Name</label>
                            <input type="text" name="name" required placeholder="e.g., Organic Romper" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price (₱)</label>
                            <input type="number" step="0.01" name="price" required placeholder="0.00" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <input type="text" name="category" required placeholder="e.g., Rompers" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-yellow-500 focus:border-yellow-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" rows="2" placeholder="Product details..." class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-yellow-500 focus:border-yellow-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Product Image</label>
                            <input type="file" name="product_image" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
                        </div>
                        <div class="sm:col-span-3">
                            <button type="submit" name="add_product" class="px-6 py-2.5 bg-yellow-500 text-white text-sm font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors">
                                Save Product
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Products Table -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Existing Products</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                                    <th class="px-6 py-3">Image</th>
                                    <th class="px-6 py-3">Product Name</th>
                                    <th class="px-6 py-3">Category</th>
                                    <th class="px-6 py-3">Price</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $prod): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <?php if (!empty($prod['image'])): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($prod['image']); ?>" alt="Product" class="h-12 w-12 object-cover rounded-lg border border-gray-200">
                                            <?php else: ?>
                                                <div class="h-12 w-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-xs">No img</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($prod['name']); ?></td>
                                        <td class="px-6 py-4 text-gray-500"><?php echo htmlspecialchars($prod['category']); ?></td>
                                        <td class="px-6 py-4 font-semibold text-yellow-600"><?php echo formatPrice($prod['price']); ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="products.php?action=delete&id=<?php echo $prod['id']; ?>" onclick="return confirm('Delete this product?');" class="text-red-600 hover:text-red-900 font-medium">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No products found.</td>
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