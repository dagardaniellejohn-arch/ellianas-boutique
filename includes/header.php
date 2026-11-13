<?php
// includes/header.php
if (!isset($pageTitle)) {
    $pageTitle = "Elliana's Boutique | Cute Baby Apparel";
}

// Determine active page for navigation highlight
$current_page = basename($_SERVER['PHP_SELF']);
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

    <!-- Top Navigation Bar (Matched to Sidebar Color Scheme) -->
    <nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            
            <!-- Logo & Brand -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="h-11 w-11 bg-yellow-500/10 rounded-xl flex items-center justify-center border border-yellow-500/20 group-hover:scale-105 transition-transform">
                    <img src="assets/images/Logo.png" alt="Elliana's Boutique Logo" class="h-7 w-auto object-contain" onerror="this.onerror=null; this.src='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/svgs/solid/baby.svg';">
                </div>
                <div class="flex flex-col">
                    <span class="text-lg font-extrabold tracking-tight text-white group-hover:text-yellow-400 transition-colors">Elliana's</span>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-yellow-500 -mt-1">Boutique</span>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex items-center space-x-8 text-sm font-semibold">
                <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'text-yellow-400 border-b-2 border-yellow-500 py-2' : 'text-gray-300 hover:text-yellow-400 py-2'; ?> transition-colors">
                    Home
                </a>
                <a href="shop.php" class="<?php echo ($current_page == 'shop.php' || $current_page == 'product-detail.php') ? 'text-yellow-400 border-b-2 border-yellow-500 py-2' : 'text-gray-300 hover:text-yellow-400 py-2'; ?> transition-colors">
                    Shop Collection
                </a>
                <?php if (isset($_SESSION['user_id']) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')): ?>
                    <a href="my-orders.php" class="<?php echo ($current_page == 'my-orders.php') ? 'text-yellow-400 border-b-2 border-yellow-500 py-2' : 'text-gray-300 hover:text-yellow-400 py-2'; ?> transition-colors">
                        My Purchases
                    </a>
                <?php endif; ?>
            </div>

            <!-- Right Actions (Cart & Auth) -->
            <div class="flex items-center space-x-4">
                <!-- Cart Icon -->
                <a href="cart.php" class="relative text-gray-300 hover:text-yellow-400 transition-colors p-2.5 bg-gray-800 rounded-xl hover:bg-gray-700 border border-gray-700 flex items-center justify-center">
                    <i class="fa-solid fa-cart-shopping text-base"></i>
                    <?php 
                    $cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
                    if ($cartCount > 0): 
                    ?>
                        <span class="absolute -top-1.5 -right-1.5 h-5 w-5 bg-yellow-500 text-white rounded-full text-xs font-bold flex items-center justify-center shadow-sm">
                            <?php echo $cartCount; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- User Session / Auth -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="hidden md:flex items-center space-x-3 border-l pl-4 border-gray-800">
                        <!-- Profile Badge -->
                        <a href="profile.php" class="flex items-center space-x-2.5 bg-gray-800 hover:bg-gray-700 px-3.5 py-2 rounded-xl border border-gray-700 shadow-xs transition-colors group">
                            <div class="h-7 w-7 rounded-lg bg-yellow-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                            </div>
                            <span class="text-sm font-bold text-gray-200 group-hover:text-yellow-400 transition-colors">Hi, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                        </a>

                        <!-- Admin Shortcut Button -->
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <a href="admin/dashboard.php" class="text-xs bg-yellow-500 text-white px-3.5 py-2.5 rounded-xl font-semibold hover:bg-yellow-600 transition-colors shadow-xs flex items-center gap-1.5">
                                <i class="fa-solid fa-gauge text-[10px]"></i> Admin
                            </a>
                        <?php endif; ?>

                        <!-- Logout Button with Text -->
                        <a href="logout.php" class="flex items-center gap-1.5 text-xs font-semibold text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 px-3.5 py-2.5 rounded-xl transition-colors border border-red-500/20">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                    </div>
                <?php else: ?>
                    <div class="hidden md:flex items-center space-x-2 border-l pl-4 border-gray-800">
                        <a href="login.php" class="text-sm font-semibold text-gray-300 hover:text-yellow-400 px-3 py-2">Log in</a>
                        <a href="register.php" class="text-sm font-semibold bg-yellow-500 text-white px-4 py-2.5 rounded-xl shadow-sm hover:bg-yellow-600 transition-colors">Sign up</a>
                    </div>
                <?php endif; ?>

                <!-- Mobile Menu Toggle Button -->
                <button id="mobile-menu-btn" class="md:hidden text-gray-300 hover:text-yellow-400 p-2.5 rounded-xl bg-gray-800 border border-gray-700">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-gray-950 border-b border-gray-800 px-6 py-4 space-y-3">
            <a href="index.php" class="block text-sm font-semibold py-2 <?php echo ($current_page == 'index.php') ? 'text-yellow-400' : 'text-gray-300'; ?>">Home</a>
            <a href="shop.php" class="block text-sm font-semibold py-2 <?php echo ($current_page == 'shop.php') ? 'text-yellow-400' : 'text-gray-300'; ?>">Shop Collection</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="my-orders.php" class="block text-sm font-semibold py-2 text-gray-300">My Purchases</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="block text-sm font-semibold py-2 text-yellow-400">Admin Dashboard</a>
                <?php endif; ?>
                <a href="logout.php" class="flex items-center gap-2 text-sm font-semibold py-2 text-red-400">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            <?php else: ?>
                <div class="pt-2 flex gap-2">
                    <a href="login.php" class="flex-1 text-center text-sm font-semibold py-2.5 border border-gray-700 rounded-xl text-gray-300 bg-gray-900">Log in</a>
                    <a href="register.php" class="flex-1 text-center text-sm font-semibold py-2.5 bg-yellow-500 text-white rounded-xl shadow-sm">Sign up</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Mobile Menu Script -->
    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');
        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    </script>

    <!-- Main Content Wrapper -->
    <main class="flex-grow">