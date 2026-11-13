<?php
// admin/users.php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Security check: Verify admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    redirect('../login.php');
}

$error = '';
$success = '';

// Handle updating user role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $target_user_id = (int)$_POST['user_id'];
    $new_role = sanitize($_POST['role']);
    
    if (in_array($new_role, ['user', 'admin'])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$new_role, $target_user_id])) {
                $success = "User role updated successfully!";
            } else {
                $error = "Failed to update user role.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Handle deleting user
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $target_user_id = (int)$_GET['id'];
    
    // Prevent admin from deleting themselves
    if ($target_user_id === $_SESSION['user_id']) {
        $error = "You cannot delete your own active admin account.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$target_user_id]);
            redirect('users.php');
        } catch (PDOException $e) {
            $error = "Failed to delete user.";
        }
    }
}

// Fetch all registered users
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

$pageTitle = "Manage Users | Elliana's Boutique Admin";
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
                <div class="flex items-center justify-center h-16 bg-gray-950 px-4">
                    <span class="text-xl font-bold text-white tracking-wider flex items-center">
                        <i class="fa-solid fa-baby mr-2 text-yellow-400"></i> Elliana Admin
                    </span>
                </div>
                <div class="flex flex-col flex-grow px-4 py-6 space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-house mr-3"></i> Dashboard
                    </a>
                    <a href="products.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-shirt mr-3"></i> Products
                    </a>
                    <a href="users.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg bg-yellow-500 text-white">
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
                <h1 class="text-xl font-bold text-gray-800">User Management</h1>
                <div class="flex items-center space-x-3">
                    <span class="text-sm font-medium text-gray-600">Hi, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
                    <div class="h-8 w-8 rounded-full bg-yellow-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6 space-y-6">
                <?php if (!empty($error)): ?>
                    <div class="p-4 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="p-4 bg-green-50 border border-green-200 text-green-600 rounded-xl text-sm"><?php echo $success; ?></div>
                <?php endif; ?>

                <!-- Users Table -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gray-900">Registered Store Customers & Admins</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-semibold">
                                    <th class="px-6 py-3">Full Name</th>
                                    <th class="px-6 py-3">Email Address</th>
                                    <th class="px-6 py-3">Role</th>
                                    <th class="px-6 py-3">Joined Date</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $u): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-gray-900"><?php echo htmlspecialchars($u['fullname']); ?></td>
                                        <td class="px-6 py-4 text-gray-500"><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td class="px-6 py-4">
                                            <form action="users.php" method="POST" class="inline-flex items-center space-x-2">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <select name="role" class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-yellow-500 focus:border-yellow-500 bg-white">
                                                    <option value="user" <?php echo ($u['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                                    <option value="admin" <?php echo ($u['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                                <button type="submit" name="update_role" class="text-xs bg-yellow-50 text-yellow-700 hover:bg-yellow-100 px-3 py-1.5 rounded-lg font-medium transition-colors">Update</button>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 text-xs"><?php echo htmlspecialchars($u['created_at'] ?? 'N/A'); ?></td>
                                        <td class="px-6 py-4 text-right">
                                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                                <a href="users.php?action=delete&id=<?php echo $u['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="text-red-600 hover:text-red-900 font-medium text-xs">Delete</a>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs italic">Current Admin</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No users found.</td>
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