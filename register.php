<?php
require_once 'includes/config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    
    // Validate and sanitize inputs
    $username = clean_input($_POST['username']);
    $email = clean_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = clean_input($_POST['first_name']);
    $last_name = clean_input($_POST['last_name']);
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $religion = clean_input($_POST['religion']);
    $caste = clean_input($_POST['caste']);
    $marital_status = $_POST['marital_status'];
    $mother_tongue = clean_input($_POST['mother_tongue']);
    $country = clean_input($_POST['country']);
    $state = clean_input($_POST['state']);
    $city = clean_input($_POST['city']);
    $phone = clean_input($_POST['phone']);
    
    // Validation
    if (empty($username)) $errors[] = "Username is required";
    elseif (strlen($username) < 3) $errors[] = "Username must be at least 3 characters";
    
    if (empty($email)) $errors[] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    
    if (empty($password)) $errors[] = "Password is required";
    elseif (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($gender)) $errors[] = "Gender is required";
    if (empty($date_of_birth)) $errors[] = "Date of birth is required";
    if (empty($religion)) $errors[] = "Religion is required";
    if (empty($caste)) $errors[] = "Caste is required";
    if (empty($marital_status)) $errors[] = "Marital status is required";
    if (empty($mother_tongue)) $errors[] = "Mother tongue is required";
    if (empty($country)) $errors[] = "Country is required";
    if (empty($state)) $errors[] = "State is required";
    if (empty($city)) $errors[] = "City is required";
    
    // Check age (must be 18 years or older)
    $age = calculate_age($date_of_birth);
    if ($age < 18) {
        $errors[] = "You must be at least 18 years old to register";
    }
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $errors[] = "Username already exists";
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already exists";
    }
    
    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, gender, date_of_birth, religion, caste, marital_status, mother_tongue, country, state, city, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $gender, $date_of_birth, $religion, $caste, $marital_status, $mother_tongue, $country, $state, $city, $phone]);
            
            if ($result) {
                // Send welcome email
                $subject = "Welcome to " . SITE_NAME;
                $message = "
                <h2>Welcome to " . SITE_NAME . "</h2>
                <p>Dear $first_name $last_name,</p>
                <p>Thank you for registering with " . SITE_NAME . ". Your account has been created successfully.</p>
                <p>Your login details:</p>
                <ul>
                    <li>Username: $username</li>
                    <li>Email: $email</li>
                </ul>
                <p>Please log in to your account and complete your profile to get started with finding your perfect match.</p>
                <p>Best regards,<br>" . SITE_NAME . " Team</p>
                ";
                
                send_email($email, $subject, $message);
                
                flash_message("Registration successful! Please login to continue.", "success");
                redirect("login.php");
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        } catch(PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

$page_title = "Register";
?>

<?php require_once 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fas fa-user-plus"></i> Create Your Account</h4>
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
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Account Information</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" 
                                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                       required minlength="3">
                                <div class="form-text">Choose a unique username (min. 3 characters)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" name="password" class="form-control" 
                                       id="password" required minlength="6">
                                <div class="form-text">Minimum 6 characters</div>
                                <div id="password_strength" class="password-strength mt-1"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control" 
                                       id="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Personal Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">First Name *</label>
                                        <input type="text" name="first_name" class="form-control" 
                                               value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name *</label>
                                        <input type="text" name="last_name" class="form-control" 
                                               value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" 
                                               required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Gender *</label>
                                        <select name="gender" class="form-select" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Date of Birth *</label>
                                        <input type="date" name="date_of_birth" class="form-control" 
                                               value="<?php echo isset($_POST['date_of_birth']) ? htmlspecialchars($_POST['date_of_birth']) : ''; ?>" 
                                               required max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>">
                                        <div class="form-text">Must be 18 years or older</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Religion *</label>
                                        <select name="religion" class="form-select" id="religion" required>
                                            <option value="">Select Religion</option>
                                            <?php
                                            $religions = get_religions();
                                            foreach($religions as $religion):
                                            ?>
                                                <option value="<?php echo $religion['religion_name']; ?>" 
                                                        <?php echo (isset($_POST['religion']) && $_POST['religion'] == $religion['religion_name']) ? 'selected' : ''; ?>>
                                                    <?php echo $religion['religion_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Caste *</label>
                                        <select name="caste" class="form-select" id="caste" required>
                                            <option value="">Select Caste</option>
                                            <?php
                                            if (isset($_POST['religion'])) {
                                                $castes = get_castes($_POST['religion']);
                                                foreach($castes as $caste):
                                            ?>
                                                <option value="<?php echo $caste['caste_name']; ?>" 
                                                        <?php echo (isset($_POST['caste']) && $_POST['caste'] == $caste['caste_name']) ? 'selected' : ''; ?>>
                                                    <?php echo $caste['caste_name']; ?>
                                                </option>
                                            <?php
                                                endforeach;
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Marital Status *</label>
                                        <select name="marital_status" class="form-select" required>
                                            <option value="">Select Status</option>
                                            <option value="Never Married" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Never Married') ? 'selected' : ''; ?>>Never Married</option>
                                            <option value="Divorced" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                                            <option value="Widowed" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                                            <option value="Awaiting Divorce" <?php echo (isset($_POST['marital_status']) && $_POST['marital_status'] == 'Awaiting Divorce') ? 'selected' : ''; ?>>Awaiting Divorce</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mother Tongue *</label>
                                        <select name="mother_tongue" class="form-select" required>
                                            <option value="">Select</option>
                                            <option value="Hindi" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'Hindi') ? 'selected' : ''; ?>>Hindi</option>
                                            <option value="English" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'English') ? 'selected' : ''; ?>>English</option>
                                            <option value="Bengali" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'Bengali') ? 'selected' : ''; ?>>Bengali</option>
                                            <option value="Tamil" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'Tamil') ? 'selected' : ''; ?>>Tamil</option>
                                            <option value="Telugu" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'Telugu') ? 'selected' : ''; ?>>Telugu</option>
                                            <option value="Marathi" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'Marathi') ? 'selected' : ''; ?>>Marathi</option>
                                            <option value="Gujarati" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'Gujarati') ? 'selected' : ''; ?>>Gujarati</option>
                                            <option value="Kannada" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'Kannada') ? 'selected' : ''; ?>>Kannada</option>
                                            <option value="Malayalam" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'Malayalam') ? 'selected' : ''; ?>>Malayalam</option>
                                            <option value="Punjabi" <?php echo (isset($_POST['mother_tongue']) && $_POST['mother_tongue'] == 'Punjabi') ? 'selected' : ''; ?>>Punjabi</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Country *</label>
                                        <input type="text" name="country" class="form-control" 
                                               value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country']) : 'India'; ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">State *</label>
                                        <input type="text" name="state" class="form-control" 
                                               value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" 
                                               required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">City *</label>
                                        <input type="text" name="city" class="form-control" 
                                               value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" 
                                               required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" 
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                                       pattern="[0-9]{10}" placeholder="10-digit mobile number">
                                <div class="form-text">Optional</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-3">
                    <p>Already have an account? <a href="<?php echo SITE_URL; ?>login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
