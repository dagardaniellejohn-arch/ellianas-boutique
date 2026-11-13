<?php
// register.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "An account with this email already exists.";
            } else {
                // Hash password and insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare("INSERT INTO users (fullname, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
                
                if ($insertStmt->execute([$fullname, $email, $hashed_password])) {
                    $success = "Registration successful! You can now log in.";
                } else {
                    $error = "Failed to register. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error occurred.";
        }
    }
}

$pageTitle = "Sign Up | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-md mx-auto px-6 py-16">
    <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
        <div class="text-center mb-8">
            <div class="h-12 w-12 bg-yellow-100 text-yellow-700 rounded-2xl flex items-center justify-center mx-auto mb-3 text-xl">
                <i class="fa-solid fa-baby"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create Account</h1>
            <p class="text-sm text-gray-500 mt-1">Join Elliana's Boutique today</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-600 rounded-xl text-sm">
                <?php echo $success; ?> <a href="login.php" class="underline font-semibold">Log in here</a>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" name="fullname" required placeholder="Jane Doe" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" required placeholder="name@example.com" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="confirm_password" required placeholder="••••••••" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
            </div>

            <button type="submit" class="w-full py-3 bg-yellow-500 text-white font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors mt-2">
                Sign Up
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Already have an account? <a href="login.php" class="text-yellow-600 font-semibold hover:underline">Log in</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>