<?php
// includes/functions.php

/**
 * Format a number into a clean currency string (e.g., $24.00)
 */
// includes/functions.php

function formatPrice($price) {
    return '₱' . number_format($price, 2);
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if the currently logged-in user has admin privileges
 */
function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect utility for cleaner routing
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>