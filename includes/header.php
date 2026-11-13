<?php
// includes/header.php
if (!isset($pageTitle)) {
    $pageTitle = "Elliana's Boutique | Cute Baby Apparel";
}
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
<body class="bg-gray-50 font-sans antialiased flex flex-col min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <!-- Logo -->
            <!-- Logo -->
            <a href="index.php" class="text-xl font-bold text-gray-900 flex items-center">
                <img src="assets/images/Logo.png" alt="Elliana's Boutique Logo" class="h-10 w-auto mr-2 object-contain">
                Elliana's Boutique
            </a>

            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-6 text-sm font-medium text-gray-600">
                <a href="index.php" class="hover:text-yellow-600 transition-colors">Home</a>
                <a href="shop.php" class="hover:text-yellow-600 transition-colors">Shop</a>
                <a href="my-orders.php" class="text-gray-600 hover:text-yellow-600 font-medium text-sm transition-colors">My Purchases</a>
            </div>

            <!-- Right Actions (Cart & Auth) -->
            <div class="flex items-center space-x-4">
                <a href="cart.php" class="relative text-gray-700 hover:text-yellow-600 transition-colors p-2">
                    <i class="fa-solid fa-cart-shopping text-lg"></i>
                    <?php 
                    $cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
                    if ($cartCount > 0): 
                    ?>
                        <span class="absolute top-0 right-0 h-5 w-5 bg-yellow-500 text-white rounded-full text-xs font-bold flex items-center justify-center">
                            <?php echo $cartCount; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center space-x-3 border-l pl-4 border-gray-200">
                        <span class="text-sm font-medium text-gray-700">Hi, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin/dashboard.php" class="text-xs bg-gray-900 text-white px-3 py-1.5 rounded-lg font-medium hover:bg-gray-800 transition-colors">Admin</a>
                        <?php endif; ?>
                        <a href="logout.php" class="text-sm text-red-600 hover:text-red-800 font-medium">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="flex items-center space-x-2 border-l pl-4 border-gray-200">
                        <a href="login.php" class="text-sm font-medium text-gray-700 hover:text-yellow-600 px-3 py-2">Log in</a>
                        <a href="register.php" class="text-sm font-medium bg-yellow-500 text-white px-4 py-2 rounded-xl shadow-sm hover:bg-yellow-600 transition-colors">Sign up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content Wrapper -->
    <main class="flex-grow">