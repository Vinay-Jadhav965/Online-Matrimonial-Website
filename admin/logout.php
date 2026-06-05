<?php
require_once '../includes/config.php';

// Destroy all session data
session_unset();
session_destroy();

// Redirect to admin login page
flash_message("You have been logged out successfully.", "info");
redirect(SITE_URL . 'admin/login.php');
?>
