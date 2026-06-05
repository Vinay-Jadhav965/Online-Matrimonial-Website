<?php
require_once '../includes/config.php';

// Destroy all session data
session_unset();
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to login page
flash_message("You have been logged out successfully.", "info");
redirect(SITE_URL . 'login.php');
?>
