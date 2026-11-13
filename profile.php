<?php
// profile.php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Security check: Must be logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle Profile Picture Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['avatar']['tmp_name'];
        $fileName = $_FILES['avatar']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $fileExtension;
            $uploadFileDir = 'uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                try {
                    $updateStmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $updateStmt->execute([$newFileName, $_SESSION['user_id']]);
                    $_SESSION['avatar'] = $newFileName;
                    $success = "Profile picture updated successfully!";
                } catch (PDOException $e) {
                    $error = "Database error updating avatar.";
                }
            } else {
                $error = "Error moving uploaded file to server directory.";
            }
        } else {
            $error = "Invalid file type. Please upload JPG, PNG, or WEBP images.";
        }
    } else {
        $error = "Please select a valid image file to upload.";
    }
}

// Handle Avatar Removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_avatar'])) {
    try {
        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current = $stmt->fetch();

        if ($current && !empty($current['avatar'])) {
            $filePath = 'uploads/' . $current['avatar'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $updateStmt = $pdo->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
        $updateStmt->execute([$_SESSION['user_id']]);
        
        if (isset($_SESSION['avatar'])) {
            unset($_SESSION['avatar']);
        }

        $success = "Profile picture removed successfully!";
    } catch (PDOException $e) {
        $error = "Database error removing avatar.";
    }
}

// Fetch latest user info from database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = [];
}

$pageTitle = "My Profile | Elliana's Boutique";
include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-6 py-12">
    <!-- Page Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Customer Profile</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your personal information, avatar, and account settings.</p>
        </div>
        <a href="my-orders.php" class="px-4 py-2 bg-yellow-50 text-yellow-700 font-semibold text-sm rounded-xl hover:bg-yellow-100 transition-colors">
            <i class="fa-solid fa-box-archive mr-2"></i> View My Purchases
        </a>
    </div>

    <!-- Alerts -->
    <?php if (!empty($success)): ?>
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-circle-check mr-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Profile Details Card -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <!-- Avatar Display -->
                <div class="h-16 w-16 bg-yellow-500 text-white rounded-2xl overflow-hidden flex items-center justify-center text-2xl font-extrabold shadow-sm flex-shrink-0">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($user['avatar']); ?>" class="h-full w-full object-cover" alt="Profile Avatar">
                    <?php else: ?>
                        <?php echo strtoupper(substr($user['fullname'] ?? 'U', 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($user['fullname'] ?? ''); ?></h2>
                    <p class="text-xs text-gray-500">Role: <span class="font-semibold uppercase text-yellow-600"><?php echo htmlspecialchars($user['role'] ?? 'Customer'); ?></span></p>
                </div>
            </div>

            <!-- Avatar Action Buttons -->
            <div class="flex items-center gap-2">
                <!-- Upload / Change Avatar Form -->
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="avatar" id="avatar-file" accept="image/*" class="hidden" onchange="this.form.submit()">
                    <label for="avatar-file" class="cursor-pointer px-4 py-2 bg-white border border-gray-300 text-gray-700 font-semibold text-xs rounded-xl hover:bg-gray-100 transition-colors shadow-xs flex items-center gap-1.5">
                        <i class="fa-solid fa-camera text-yellow-600"></i> <?php echo empty($user['avatar']) ? 'Upload Avatar' : 'Change Avatar'; ?>
                    </label>
                    <input type="hidden" name="upload_avatar" value="1">
                </form>

                <!-- Remove Avatar Button -->
                <?php if (!empty($user['avatar'])): ?>
                    <form action="profile.php" method="POST" onsubmit="return confirm('Are you sure you want to remove your profile picture?');">
                        <button type="submit" name="remove_avatar" value="1" class="px-3 py-2 bg-red-50 border border-red-200 text-red-600 font-semibold text-xs rounded-xl hover:bg-red-100 transition-colors shadow-xs flex items-center gap-1.5" title="Remove Avatar">
                            <i class="fa-solid fa-trash-can"></i> Remove
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Full Name</label>
                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-800">
                    <?php echo htmlspecialchars($user['fullname'] ?? ''); ?>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Email Address</label>
                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-800">
                    <?php echo htmlspecialchars($user['email'] ?? ''); ?>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Account ID / SKU</label>
                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-mono font-bold text-yellow-700">
                    <?php echo 'EB-USR-' . str_pad($user['id'] ?? 1, 4, '0', STR_PAD_LEFT); ?>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-gray-400 mb-1">Member Since</label>
                <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-800">
                    <?php echo htmlspecialchars($user['created_at'] ?? 'Recently'); ?>
                </div>
            </div>
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 flex justify-between items-center">
            <p class="text-xs text-gray-500">Need to update your password or details? Contact store support.</p>
            <a href="logout.php" class="px-4 py-2 bg-red-50 text-red-600 font-semibold text-sm rounded-xl hover:bg-red-100 transition-colors">
                <i class="fa-solid fa-right-from-bracket mr-1.5"></i> Logout
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>