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

// Handle updating user details (Name, Email, Role)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $target_user_id = (int)$_POST['user_id'];
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $new_role = sanitize($_POST['role']);
    
    if (empty($fullname) || empty($email)) {
        $error = "Full name and email cannot be empty.";
    } elseif (!in_array($new_role, ['user', 'admin'])) {
        $error = "Invalid role selected.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, role = ? WHERE id = ?");
            if ($stmt->execute([$fullname, $email, $new_role, $target_user_id])) {
                $success = "User details updated successfully!";
            } else {
                $error = "Failed to update user details.";
            }
        } catch (PDOException $e) {
            $error = "Database error (Email might already be in use): " . $e->getMessage();
        }
    }
}

// Handle resetting user password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $target_user_id = (int)$_POST['user_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || empty($confirm_password)) {
        $error = "Password fields cannot be empty.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashed_password, $target_user_id])) {
                $success = "User password successfully reset!";
            } else {
                $error = "Failed to reset password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Handle deleting user
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $target_user_id = (int)$_GET['id'];
    
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
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <a href="products.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-shirt mr-3"></i> Products
                    </a>
                    <a href="orders.php" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg hover:bg-gray-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-box-archive mr-3"></i> Orders
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
                <h1 class="text-xl font-bold text-gray-800">User Management & Security</h1>
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
                                    <th class="px-6 py-3">Account ID</th>
                                    <th class="px-6 py-3">User Details (Name & Email)</th>
                                    <th class="px-6 py-3">Role</th>
                                    <th class="px-6 py-3">Reset Password</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $u): ?>
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 font-mono font-bold text-xs text-yellow-700 align-top">
                                            <?php echo 'EB-USR-' . str_pad($u['id'], 4, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        
                                        <!-- Editable User Details -->
                                        <td class="px-6 py-4 align-top">
                                            <form action="users.php" method="POST" class="space-y-2">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <div>
                                                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($u['fullname']); ?>" required class="w-full text-xs font-semibold text-gray-900 bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 focus:bg-white focus:ring-yellow-500 focus:border-yellow-500">
                                                </div>
                                                <div>
                                                    <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" required class="w-full text-xs text-gray-500 bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 focus:bg-white focus:ring-yellow-500 focus:border-yellow-500">
                                                </div>
                                                <button type="submit" name="update_user" class="text-xs bg-yellow-50 text-yellow-700 hover:bg-yellow-100 px-3 py-1.5 rounded-lg font-medium transition-colors">
                                                    Save Details
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Role Update -->
                                        <td class="px-6 py-4 align-top">
                                            <form action="users.php" method="POST" class="space-y-2">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <input type="hidden" name="fullname" value="<?php echo htmlspecialchars($u['fullname']); ?>">
                                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($u['email']); ?>">
                                                <select name="role" class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-yellow-500 focus:border-yellow-500 bg-white">
                                                    <option value="user" <?php echo ($u['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                                                    <option value="admin" <?php echo ($u['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                                <button type="submit" name="update_user" class="block text-xs bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 py-1.5 rounded-lg font-medium transition-colors">Update Role</button>
                                            </form>
                                        </td>

                                        <!-- Password Reset Form -->
                                        <td class="px-6 py-4 align-top">
                                            <form action="users.php" method="POST" class="space-y-2" onsubmit="return confirm('Are you sure you want to reset this user\'s password?');">
                                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                <input type="password" name="new_password" placeholder="New Password" required class="w-full text-xs bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 focus:bg-white">
                                                <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full text-xs bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 focus:bg-white">
                                                <button type="submit" name="reset_password" class="text-xs bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-1.5 rounded-lg font-medium transition-colors">
                                                    Reset Password
                                                </button>
                                            </form>
                                        </td>

                                        <!-- Delete Action -->
                                        <td class="px-6 py-4 text-right align-top">
                                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                                <a href="users.php?action=delete&id=<?php echo $u['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?');" class="inline-block text-red-600 hover:text-red-900 font-medium text-xs pt-1">Delete Account</a>
                                            <?php else: ?>
                                                <span class="block text-gray-400 text-xs italic pt-1">Current Admin</span>
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