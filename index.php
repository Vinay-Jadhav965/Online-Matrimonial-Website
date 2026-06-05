<?php
require_once 'includes/config.php';

// Get featured profiles for homepage
$featured_profiles = [];
try {
    $stmt = $conn->prepare("SELECT u.*, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age 
                           FROM users u 
                           WHERE u.is_active = 1 AND u.profile_photo IS NOT NULL 
                           ORDER BY u.created_at DESC 
                           LIMIT 6");
    $stmt->execute();
    $featured_profiles = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error
}

// Get statistics
$stats = [];
try {
    $stmt = $conn->prepare("SELECT 
                           COUNT(*) as total_users,
                           COUNT(CASE WHEN gender = 'Male' THEN 1 END) as male_users,
                           COUNT(CASE WHEN gender = 'Female' THEN 1 END) as female_users,
                           COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_registrations
                           FROM users WHERE is_active = 1");
    $stmt->execute();
    $stats = $stmt->fetch();
} catch(PDOException $e) {
    // Handle error
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1>Find Your Perfect Life Partner</h1>
        <p>Join thousands of happy couples who found their soulmates through our trusted matrimonial platform</p>
        <div class="hero-buttons">
            <a href="<?php echo SITE_URL; ?>register.php" class="btn btn-light btn-lg me-3">
                <i class="fas fa-user-plus"></i> Register Now
            </a>
            <a href="<?php echo SITE_URL; ?>login.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <i class="fas fa-users text-primary"></i>
                    <h3><?php echo number_format($stats['total_users'] ?? 0); ?></h3>
                    <p>Total Members</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <i class="fas fa-male text-info"></i>
                    <h3><?php echo number_format($stats['male_users'] ?? 0); ?></h3>
                    <p>Gentlemen</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <i class="fas fa-female text-danger"></i>
                    <h3><?php echo number_format($stats['female_users'] ?? 0); ?></h3>
                    <p>Ladies</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card">
                    <i class="fas fa-heart text-pink"></i>
                    <h3><?php echo number_format($stats['today_registrations'] ?? 0); ?></h3>
                    <p>Joined Today</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Search Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2>Quick Search</h2>
            <p>Find profiles that match your preferences</p>
        </div>
        
        <form action="<?php echo SITE_URL; ?>user/search.php" method="GET" class="search-form">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Looking For</label>
                    <select name="gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="Female">Bride</option>
                        <option value="Male">Groom</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Age</label>
                    <select name="age_from" class="form-select">
                        <option value="">From</option>
                        <?php for($i = 18; $i <= 60; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <select name="age_to" class="form-select">
                        <option value="">To</option>
                        <?php for($i = 18; $i <= 60; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Religion</label>
                    <select name="religion" class="form-select">
                        <option value="">Select Religion</option>
                        <?php
                        $religions = get_religions();
                        foreach($religions as $religion):
                        ?>
                            <option value="<?php echo $religion['religion_name']; ?>"><?php echo $religion['religion_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mother Tongue</label>
                    <select name="mother_tongue" class="form-select">
                        <option value="">Select</option>
                        <option value="Hindi">Hindi</option>
                        <option value="English">English</option>
                        <option value="Bengali">Bengali</option>
                        <option value="Tamil">Tamil</option>
                        <option value="Telugu">Telugu</option>
                        <option value="Marathi">Marathi</option>
                        <option value="Gujarati">Gujarati</option>
                        <option value="Kannada">Kannada</option>
                        <option value="Malayalam">Malayalam</option>
                        <option value="Punjabi">Punjabi</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">State</label>
                    <input type="text" name="state" class="form-control" placeholder="Enter state">
                </div>
                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-lg flex-fill">
                            <i class="fas fa-search"></i> Search Profiles
                        </button>
                        <a href="<?php echo SITE_URL; ?>user/search.php" class="btn btn-outline-secondary btn-lg">
                            Advanced Search
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Featured Profiles Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2>Featured Profiles</h2>
            <p>New members looking for their life partners</p>
        </div>
        
        <?php if (!empty($featured_profiles)): ?>
            <div class="row">
                <?php foreach ($featured_profiles as $profile): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="profile-card">
                            <div class="card-body text-center">
                                <?php if ($profile['profile_photo']): ?>
                                    <img src="<?php echo SITE_URL . PHOTO_PATH . $profile['profile_photo']; ?>" 
                                         alt="<?php echo $profile['first_name']; ?>" 
                                         class="profile-photo mb-3">
                                <?php else: ?>
                                    <img src="<?php echo SITE_URL; ?>assets/images/default-avatar.png" 
                                         alt="<?php echo $profile['first_name']; ?>" 
                                         class="profile-photo mb-3">
                                <?php endif; ?>
                                
                                <h5 class="card-title"><?php echo $profile['first_name'] . ' ' . $profile['last_name']; ?></h5>
                                <p class="text-muted">
                                    <?php echo $profile['age']; ?> years, <?php echo $profile['gender']; ?><br>
                                    <?php echo $profile['religion']; ?>, <?php echo $profile['caste']; ?><br>
                                    <?php echo $profile['city']; ?>, <?php echo $profile['state']; ?>
                                </p>
                                
                                <div class="profile-details">
                                    <small class="text-muted">
                                        <?php echo $profile['education'] ? $profile['education'] : 'Education not specified'; ?><br>
                                        <?php echo $profile['occupation'] ? $profile['occupation'] : 'Occupation not specified'; ?>
                                    </small>
                                </div>
                                
                                <div class="mt-3">
                                    <?php if (is_logged_in()): ?>
                                        <?php if ($profile['id'] != get_user_id()): ?>
                                            <a href="<?php echo SITE_URL; ?>user/view_profile.php?id=<?php echo $profile['id']; ?>" 
                                               class="btn btn-primary btn-sm me-2">
                                                <i class="fas fa-eye"></i> View Profile
                                            </a>
                                            <button class="btn btn-pink btn-sm send-interest-btn" 
                                                    data-receiver-id="<?php echo $profile['id']; ?>">
                                                <i class="fas fa-heart"></i> Send Interest
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <a href="<?php echo SITE_URL; ?>login.php" class="btn btn-primary btn-sm">
                                            <i class="fas fa-sign-in-alt"></i> Login to View
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>user/search.php" class="btn btn-lg btn-outline-primary">
                    View All Profiles
                </a>
            </div>
        <?php else: ?>
            <div class="text-center">
                <p>No featured profiles available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2>Why Choose Us?</h2>
            <p>We make finding your life partner simple and secure</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h4>Secure & Private</h4>
                <p>Your privacy is our priority. We ensure all profiles are verified and your information is kept secure.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h4>Advanced Search</h4>
                <p>Find your perfect match with our advanced search filters based on religion, caste, education, and more.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h4>Easy Communication</h4>
                <p>Connect with potential matches through our secure messaging system and express interest anonymously.</p>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <h4>Verified Profiles</h4>
                <p>All profiles undergo verification process to ensure authenticity and build trust among members.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h4>Mobile Friendly</h4>
                <p>Access our platform on any device. Search and connect with potential partners anytime, anywhere.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-headset"></i>
                </div>
                <h4>24/7 Support</h4>
                <p>Our dedicated support team is always here to help you with any questions or concerns.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2>Success Stories</h2>
            <p>Happy couples who found their match through our platform</p>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <img src="<?php echo SITE_URL; ?>assets/images/testimonial1.jpg" alt="Couple" class="testimonial-avatar">
                    <h5>Rahul & Priya</h5>
                    <div class="text-warning mb-2">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"We found each other through this platform and couldn't be happier. The verification process gave us confidence, and the search filters helped us find the perfect match!"</p>
                    <small class="text-muted">Married in 2023</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <img src="<?php echo SITE_URL; ?>assets/images/testimonial2.jpg" alt="Couple" class="testimonial-avatar">
                    <h5>Amit & Sneha</h5>
                    <div class="text-warning mb-2">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"The platform made it easy to connect with like-minded individuals. We appreciated the privacy features and the secure messaging system."</p>
                    <small class="text-muted">Married in 2023</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <img src="<?php echo SITE_URL; ?>assets/images/testimonial3.jpg" alt="Couple" class="testimonial-avatar">
                    <h5>Vikram & Anjali</h5>
                    <div class="text-warning mb-2">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"Thank you for bringing us together! The advanced search filters helped us find someone who shares our values and background."</p>
                    <small class="text-muted">Married in 2024</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2>Ready to Find Your Life Partner?</h2>
        <p class="lead">Join thousands of happy couples who started their journey here</p>
        <a href="<?php echo SITE_URL; ?>register.php" class="btn btn-light btn-lg">
            <i class="fas fa-heart"></i> Get Started Now
        </a>
    </div>
</section>

<!-- Back to Top Button -->
<button id="back_to_top" class="btn btn-primary btn-lg" style="position: fixed; bottom: 20px; right: 20px; display: none; z-index: 1000;">
    <i class="fas fa-arrow-up"></i>
</button>

<?php require_once 'includes/footer.php'; ?>
