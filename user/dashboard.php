<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect(SITE_URL . 'login.php');
}

$user_id = get_user_id();
$user = get_user_by_id($user_id);
$family_details = get_user_family_details($user_id);
$partner_preferences = get_user_partner_preferences($user_id);

// Get user statistics
$stats = [];
try {
    // Profile views
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM profile_views WHERE viewed_id = ?");
    $stmt->execute([$user_id]);
    $stats['profile_views'] = $stmt->fetch()['count'];
    
    // Interests sent
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM interests WHERE sender_id = ?");
    $stmt->execute([$user_id]);
    $stats['interests_sent'] = $stmt->fetch()['count'];
    
    // Interests received
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM interests WHERE receiver_id = ?");
    $stmt->execute([$user_id]);
    $stats['interests_received'] = $stmt->fetch()['count'];
    
    // Accepted interests
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM interests WHERE receiver_id = ? AND status = 'Accepted'");
    $stmt->execute([$user_id]);
    $stats['accepted_interests'] = $stmt->fetch()['count'];
    
    // Shortlisted profiles
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM shortlists WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats['shortlisted'] = $stmt->fetch()['count'];
    
    // Unread messages
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $stats['unread_messages'] = $stmt->fetch()['count'];
} catch(PDOException $e) {
    // Handle error
}

// Get recent matches
$recent_matches = [];
try {
    $matches_result = get_matches_for_user($user_id, 1, 6);
    $recent_matches = $matches_result['results'];
} catch(PDOException $e) {
    // Handle error
}

// Get recent interests
$recent_interests = [];
try {
    $stmt = $conn->prepare("SELECT i.*, u.first_name, u.last_name, u.profile_photo, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age 
                           FROM interests i 
                           JOIN users u ON i.sender_id = u.id 
                           WHERE i.receiver_id = ? 
                           ORDER BY i.created_at DESC 
                           LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_interests = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error
}

// Calculate profile completion percentage
$completion_percentage = 0;
$required_fields = ['first_name', 'last_name', 'date_of_birth', 'religion', 'caste', 'mother_tongue', 'country', 'state', 'city'];
$optional_fields = ['height', 'weight', 'education', 'occupation', 'annual_income', 'about_me', 'profile_photo'];

foreach ($required_fields as $field) {
    if (!empty($user[$field])) $completion_percentage += 5;
}

foreach ($optional_fields as $field) {
    if (!empty($user[$field])) $completion_percentage += 3;
}

if ($family_details) $completion_percentage += 15;
if ($partner_preferences) $completion_percentage += 20;

$completion_percentage = min($completion_percentage, 100);

$page_title = "Dashboard";
?>

<?php require_once '../includes/header.php'; ?>

<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3">
        <div class="sidebar">
            <div class="text-center mb-4">
                <?php if ($user['profile_photo']): ?>
                    <img src="<?php echo SITE_URL . PHOTO_PATH . $user['profile_photo']; ?>" 
                         alt="<?php echo $user['first_name']; ?>" 
                         class="profile-photo mb-2">
                <?php else: ?>
                    <img src="<?php echo SITE_URL; ?>assets/images/default-avatar.png" 
                         alt="<?php echo $user['first_name']; ?>" 
                         class="profile-photo mb-2">
                <?php endif; ?>
                <h5><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h5>
                <p class="text-muted"><?php echo calculate_age($user['date_of_birth']); ?> years, <?php echo $user['gender']; ?></p>
            </div>
            
            <div class="mb-4">
                <h6>Profile Completion</h6>
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" 
                         style="width: <?php echo $completion_percentage; ?>%"
                         aria-valuenow="<?php echo $completion_percentage; ?>" 
                         aria-valuemin="0" aria-valuemax="100">
                        <?php echo $completion_percentage; ?>%
                    </div>
                </div>
                <small class="text-muted">Complete your profile to get better matches</small>
            </div>
            
            <nav class="nav flex-column">
                <a href="<?php echo SITE_URL; ?>user/dashboard.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="<?php echo SITE_URL; ?>user/profile.php" class="nav-link">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="<?php echo SITE_URL; ?>user/family_details.php" class="nav-link">
                    <i class="fas fa-users"></i> Family Details
                </a>
                <a href="<?php echo SITE_URL; ?>user/partner_preferences.php" class="nav-link">
                    <i class="fas fa-heart"></i> Partner Preferences
                </a>
                <a href="<?php echo SITE_URL; ?>user/search.php" class="nav-link">
                    <i class="fas fa-search"></i> Search
                </a>
                <a href="<?php echo SITE_URL; ?>user/matches.php" class="nav-link">
                    <i class="fas fa-handshake"></i> My Matches
                </a>
                <a href="<?php echo SITE_URL; ?>user/inbox.php" class="nav-link">
                    <i class="fas fa-envelope"></i> Inbox
                    <?php if ($stats['unread_messages'] > 0): ?>
                        <span class="badge bg-danger"><?php echo $stats['unread_messages']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo SITE_URL; ?>user/interests.php" class="nav-link">
                    <i class="fas fa-heart"></i> Interests
                </a>
                <a href="<?php echo SITE_URL; ?>user/shortlists.php" class="nav-link">
                    <i class="fas fa-star"></i> Shortlists
                </a>
                <a href="<?php echo SITE_URL; ?>user/settings.php" class="nav-link">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="col-md-9">
        <!-- Welcome Message -->
        <div class="card mb-4">
            <div class="card-body">
                <h4 class="card-title">Welcome back, <?php echo $user['first_name']; ?>!</h4>
                <p class="text-muted">Here's what's happening with your account today.</p>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-eye text-primary"></i>
                    <h4><?php echo $stats['profile_views']; ?></h4>
                    <p>Profile Views</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-heart text-danger"></i>
                    <h4><?php echo $stats['interests_received']; ?></h4>
                    <p>Interests Received</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-check-circle text-success"></i>
                    <h4><?php echo $stats['accepted_interests']; ?></h4>
                    <p>Accepted Interests</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="fas fa-star text-warning"></i>
                    <h4><?php echo $stats['shortlisted']; ?></h4>
                    <p>Shortlisted</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Matches -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Matches</h5>
                        <a href="<?php echo SITE_URL; ?>user/matches.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_matches)): ?>
                            <?php foreach ($recent_matches as $match): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <?php if ($match['profile_photo']): ?>
                                        <img src="<?php echo SITE_URL . PHOTO_PATH . $match['profile_photo']; ?>" 
                                             alt="<?php echo $match['first_name']; ?>" 
                                             class="profile-thumbnail me-3">
                                    <?php else: ?>
                                        <img src="<?php echo SITE_URL; ?>assets/images/default-avatar.png" 
                                             alt="<?php echo $match['first_name']; ?>" 
                                             class="profile-thumbnail me-3">
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?php echo $match['first_name'] . ' ' . $match['last_name']; ?></h6>
                                        <small class="text-muted">
                                            <?php echo $match['age']; ?> years, <?php echo $match['city']; ?>
                                        </small>
                                    </div>
                                    <div>
                                        <a href="<?php echo SITE_URL; ?>user/view_profile.php?id=<?php echo $match['id']; ?>" 
                                           class="btn btn-sm btn-primary">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No matches found. Complete your partner preferences to get better matches.</p>
                            <a href="<?php echo SITE_URL; ?>user/partner_preferences.php" class="btn btn-primary btn-sm">
                                Set Preferences
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Interests -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Interests</h5>
                        <a href="<?php echo SITE_URL; ?>user/interests.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recent_interests)): ?>
                            <?php foreach ($recent_interests as $interest): ?>
                                <div class="d-flex align-items-center mb-3">
                                    <?php if ($interest['profile_photo']): ?>
                                        <img src="<?php echo SITE_URL . PHOTO_PATH . $interest['profile_photo']; ?>" 
                                             alt="<?php echo $interest['first_name']; ?>" 
                                             class="profile-thumbnail me-3">
                                    <?php else: ?>
                                        <img src="<?php echo SITE_URL; ?>assets/images/default-avatar.png" 
                                             alt="<?php echo $interest['first_name']; ?>" 
                                             class="profile-thumbnail me-3">
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?php echo $interest['first_name'] . ' ' . $interest['last_name']; ?></h6>
                                        <small class="text-muted">
                                            <?php echo $interest['age']; ?> years
                                            <span class="interest-status interest-<?php echo strtolower($interest['status']); ?>">
                                                <?php echo $interest['status']; ?>
                                            </span>
                                        </small>
                                    </div>
                                    <div>
                                        <a href="<?php echo SITE_URL; ?>user/view_profile.php?id=<?php echo $interest['sender_id']; ?>" 
                                           class="btn btn-sm btn-primary">View</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">No interests received yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-3">
                        <a href="<?php echo SITE_URL; ?>user/search.php" class="btn btn-outline-primary btn-lg w-100">
                            <i class="fas fa-search fa-2x d-block mb-2"></i>
                            Search Profiles
                        </a>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <a href="<?php echo SITE_URL; ?>user/profile.php" class="btn btn-outline-success btn-lg w-100">
                            <i class="fas fa-user-edit fa-2x d-block mb-2"></i>
                            Edit Profile
                        </a>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <a href="<?php echo SITE_URL; ?>user/partner_preferences.php" class="btn btn-outline-info btn-lg w-100">
                            <i class="fas fa-heart fa-2x d-block mb-2"></i>
                            Set Preferences
                        </a>
                    </div>
                    <div class="col-md-3 text-center mb-3">
                        <a href="<?php echo SITE_URL; ?>user/shortlists.php" class="btn btn-outline-warning btn-lg w-100">
                            <i class="fas fa-star fa-2x d-block mb-2"></i>
                            View Shortlists
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Membership Status -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Membership Status</h5>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6>Current Plan: <span class="badge bg-primary"><?php echo $user['membership_plan']; ?></span></h6>
                        <p class="text-muted mb-0">
                            <?php if ($user['membership_plan'] == 'Free'): ?>
                                Upgrade to Premium to unlock unlimited messaging, advanced search filters, and more features.
                            <?php else: ?>
                                Enjoy premium features including unlimited messaging, profile highlighting, and priority support.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <?php if ($user['membership_plan'] == 'Free'): ?>
                            <a href="<?php echo SITE_URL; ?>user/upgrade.php" class="btn btn-primary">Upgrade Now</a>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>user/membership.php" class="btn btn-outline-primary">Manage</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
