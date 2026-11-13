<?php
require_once 'config/database.php';

// Generate a fresh, guaranteed-valid hash for 'admin123'
$new_hash = password_hash('admin123', PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@tinysprout.com'");
if ($stmt->execute([$new_hash])) {
    echo "Admin password successfully reset! You can now log in with admin123.";
} else {
    echo "Failed to update.";
}
?>