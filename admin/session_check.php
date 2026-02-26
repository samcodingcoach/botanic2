<?php
/**
 * Session Check Include File
 * Protects admin pages by requiring valid session
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['id_users'])) {
    // Redirect to login page
    header('Location: ../login.php');
    exit;
}
