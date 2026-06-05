<?php
require_once 'includes/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    $username = clean_input($_POST['username']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validation
    if (empty($username)) {
        $errors[] = "Username or email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        try {
            // Check user credentials
            $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_gender'] = $user['gender'];
                
                // Set remember me cookie
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expiry = time() + (30 * 24 * 60 * 60); // 30 days
                    
                    setcookie('remember_token', $token, $expiry, '/');
                    
                    // Store token in database (optional implementation)
                    // For now, we'll use a simple approach
                }
                
                // Redirect to dashboard
                flash_message("Login successful! Welcome back.", "success");
                redirect("user/dashboard.php");
            } else {
                $errors[] = "Invalid username/email or password";
            }
        } catch(PDOException $e) {
            $errors[] = "Login failed. Please try again.";
        }
    }
}

// Check for remember me cookie
if (isset($_COOKIE['remember_token']) && !is_logged_in()) {
    // Auto-login implementation (optional)
    // This would verify the token and log in the user
}

$page_title = "Login";
?>

<?php require_once 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> Login to Your Account</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <a href="<?php echo SITE_URL; ?>forgot_password.php">Forgot Password?</a>
                </div>
                
                <hr>
                
                <div class="text-center">
                    <p>Don't have an account? <a href="<?php echo SITE_URL; ?>register.php">Register here</a></p>
                </div>
                
                <div class="text-center mt-3">
                    <p class="text-muted">Are you an administrator? <a href="<?php echo SITE_URL; ?>admin/login.php">Admin Login</a></p>
                </div>
            </div>
        </div>
        
        <!-- Features Card -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title text-center mb-3">Why Join Our Platform?</h5>
                <div class="row text-center">
                    <div class="col-4">
                        <i class="fas fa-shield-alt text-primary fa-2x mb-2"></i>
                        <h6>Secure</h6>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-search text-success fa-2x mb-2"></i>
                        <h6>Easy Search</h6>
                    </div>
                    <div class="col-4">
                        <i class="fas fa-comments text-info fa-2x mb-2"></i>
                        <h6>Connect</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.querySelector('input[name="password"]');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
