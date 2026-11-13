<?php
// login.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('index.php');
                }
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error occurred.";
        }
    }
}

$pageTitle = "Log In | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-md mx-auto px-6 py-16">
    <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
        <div class="text-center mb-8">
            <div class="h-12 w-12 bg-yellow-100 text-yellow-700 rounded-2xl flex items-center justify-center mx-auto mb-3 text-xl">
                <i class="fa-solid fa-baby"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Welcome Back</h1>
            <p class="text-sm text-gray-500 mt-1">Log in to your Elliana's Boutique account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" required placeholder="name@example.com" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="mt-1 block w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-yellow-500 focus:border-yellow-500">
            </div>

            <button type="submit" class="w-full py-3 bg-yellow-500 text-white font-medium rounded-xl shadow hover:bg-yellow-600 transition-colors mt-2">
                Log In
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Don't have an account? <a href="register.php" class="text-yellow-600 font-semibold hover:underline">Sign up</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>