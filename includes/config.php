<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'matrimony');

// Site Configuration
define('SITE_URL', 'http://localhost/Matrimony/');
define('SITE_NAME', 'Online Matrimonial Website');
define('SITE_EMAIL', 'admin@matrimony.com');

// File Upload Configuration
define('UPLOAD_PATH', 'uploads/');
define('PHOTO_PATH', 'uploads/photos/');
define('KUNDALI_PATH', 'uploads/kundali/');
define('MAX_PHOTO_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_KUNDALI_SIZE', 2 * 1024 * 1024); // 2MB

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database Connection
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Helper Functions
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_admin_id() {
    return $_SESSION['admin_id'] ?? null;
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function calculate_age($date_of_birth) {
    $today = new DateTime();
    $dob = new DateTime($date_of_birth);
    $age = $today->diff($dob);
    return $age->y;
}

function upload_file($file, $upload_path, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 5242880) {
    if (!isset($file['name']) || empty($file['name'])) {
        return ['success' => false, 'message' => 'No file selected'];
    }
    
    $file_name = $file['name'];
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
    
    // Check file size
    if ($file_size > $max_size) {
        return ['success' => false, 'message' => 'File size exceeds maximum limit'];
    }
    
    // Check file type
    if (!in_array(strtolower($file_type), $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $new_file_name = uniqid() . '.' . $file_type;
    $upload_file_path = $upload_path . $new_file_name;
    
    // Upload file
    if (move_uploaded_file($file_tmp, $upload_file_path)) {
        return ['success' => true, 'filename' => $new_file_name, 'path' => $upload_file_path];
    } else {
        return ['success' => false, 'message' => 'File upload failed'];
    }
}

function paginate($query, $page = 1, $limit = 10) {
    global $conn;
    
    $offset = ($page - 1) * $limit;
    
    // Get total records
    $count_query = "SELECT COUNT(*) as total FROM ($query) as count_table";
    $stmt = $conn->prepare($count_query);
    $stmt->execute();
    $total = $stmt->fetch()['total'];
    
    // Get paginated results
    $paginated_query = $query . " LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($paginated_query);
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    // Calculate pagination info
    $total_pages = ceil($total / $limit);
    $has_next = $page < $total_pages;
    $has_prev = $page > 1;
    
    return [
        'results' => $results,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total,
            'has_next' => $has_next,
            'has_prev' => $has_prev,
            'limit' => $limit
        ]
    ];
}

function send_email($to, $subject, $message, $headers = '') {
    $default_headers = "MIME-Version: 1.0" . "\r\n";
    $default_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $default_headers .= "From: " . SITE_NAME . " <" . SITE_EMAIL . ">" . "\r\n";
    
    $headers = $headers ? $headers : $default_headers;
    
    return mail($to, $subject, $message, $headers);
}

// Include common functions
require_once 'functions.php';
?>
