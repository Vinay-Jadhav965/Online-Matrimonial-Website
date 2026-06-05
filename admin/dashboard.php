<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    redirect(SITE_URL . 'admin/login.php');
}

// Get dashboard statistics
$stats = [];
try {
    // Total users
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE is_active = 1");
    $stmt->execute();
    $stats['total_users'] = $stmt->fetch()['count'];
    
    // Male users
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE gender = 'Male' AND is_active = 1");
    $stmt->execute();
    $stats['male_users'] = $stmt->fetch()['count'];
    
    // Female users
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE gender = 'Female' AND is_active = 1");
    $stmt->execute();
    $stats['female_users'] = $stmt->fetch()['count'];
    
    // Today's registrations
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $stats['today_registrations'] = $stmt->fetch()['count'];
    
    // Total interests
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM interests");
    $stmt->execute();
    $stats['total_interests'] = $stmt->fetch()['count'];
    
    // Accepted interests
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM interests WHERE status = 'Accepted'");
    $stmt->execute();
    $stats['accepted_interests'] = $stmt->fetch()['count'];
    
    // Total messages
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM messages");
    $stmt->execute();
    $stats['total_messages'] = $stmt->fetch()['count'];
    
    // Premium members
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE membership_plan != 'Free' AND is_active = 1");
    $stmt->execute();
    $stats['premium_members'] = $stmt->fetch()['count'];
} catch(PDOException $e) {
    // Handle error
}

// Get recent registrations
$recent_users = [];
try {
    $stmt = $conn->prepare("SELECT u.*, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age 
                           FROM users u 
                           WHERE u.is_active = 1 
                           ORDER BY u.created_at DESC 
                           LIMIT 5");
    $stmt->execute();
    $recent_users = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error
}

// Get recent activities
$recent_activities = [];
try {
    $stmt = $conn->prepare("SELECT 'interest' as activity_type, i.sender_id, i.receiver_id, i.created_at, 
                                   u1.first_name as sender_name, u2.first_name as receiver_name
                            FROM interests i
                            JOIN users u1 ON i.sender_id = u1.id
                            JOIN users u2 ON i.receiver_id = u2.id
                            ORDER BY i.created_at DESC
                            LIMIT 5");
    $stmt->execute();
    $interests = $stmt->fetchAll();
    
    $stmt = $conn->prepare("SELECT 'message' as activity_type, m.sender_id, m.receiver_id, m.created_at,
                                   u1.first_name as sender_name, u2.first_name as receiver_name
                            FROM messages m
                            JOIN users u1 ON m.sender_id = u1.id
                            JOIN users u2 ON m.receiver_id = u2.id
                            ORDER BY m.created_at DESC
                            LIMIT 5");
    $stmt->execute();
    $messages = $stmt->fetchAll();
    
    // Combine and sort activities
    $recent_activities = array_merge($interests, $messages);
    usort($recent_activities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    $recent_activities = array_slice($recent_activities, 0, 10);
} catch(PDOException $e) {
    // Handle error
}

// Get membership statistics
$membership_stats = [];
try {
    $stmt = $conn->prepare("SELECT membership_plan, COUNT(*) as count 
                           FROM users 
                           WHERE is_active = 1 
                           GROUP BY membership_plan");
    $stmt->execute();
    $membership_stats = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error
}

$page_title = "Admin Dashboard";
?>

<?php require_once '../includes/header.php'; ?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Admin Dashboard</h1>
            <div>
                <span class="badge bg-success">Welcome, <?php echo $_SESSION['admin_name']; ?></span>
                <a href="<?php echo SITE_URL; ?>admin/logout.php" class="btn btn-danger btn-sm ms-2">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <i class="fas fa-users text-primary"></i>
            <h3><?php echo number_format($stats['total_users']); ?></h3>
            <p>Total Users</p>
            <small class="text-success">
                <i class="fas fa-arrow-up"></i> +<?php echo $stats['today_registrations']; ?> today
            </small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <i class="fas fa-male text-info"></i>
            <h3><?php echo number_format($stats['male_users']); ?></h3>
            <p>Male Users</p>
            <small class="text-muted">
                <?php echo round(($stats['male_users'] / $stats['total_users']) * 100, 1); ?>% of total
            </small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <i class="fas fa-female text-danger"></i>
            <h3><?php echo number_format($stats['female_users']); ?></h3>
            <p>Female Users</p>
            <small class="text-muted">
                <?php echo round(($stats['female_users'] / $stats['total_users']) * 100, 1); ?>% of total
            </small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <i class="fas fa-crown text-warning"></i>
            <h3><?php echo number_format($stats['premium_members']); ?></h3>
            <p>Premium Members</p>
            <small class="text-muted">
                <?php echo round(($stats['premium_members'] / $stats['total_users']) * 100, 1); ?>% of total
            </small>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <i class="fas fa-heart text-pink"></i>
            <h3><?php echo number_format($stats['total_interests']); ?></h3>
            <p>Total Interests</p>
            <small class="text-success">
                <?php echo $stats['accepted_interests']; ?> accepted
            </small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <i class="fas fa-comments text-success"></i>
            <h3><?php echo number_format($stats['total_messages']); ?></h3>
            <p>Total Messages</p>
            <small class="text-muted">User communications</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <i class="fas fa-user-plus text-info"></i>
            <h3><?php echo $stats['today_registrations']; ?></h3>
            <p>Today's Registrations</p>
            <small class="text-muted">New users today</small>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <i class="fas fa-percentage text-primary"></i>
            <h3>
                <?php 
                $success_rate = $stats['total_interests'] > 0 ? 
                    round(($stats['accepted_interests'] / $stats['total_interests']) * 100, 1) : 0;
                echo $success_rate; ?>%
            </h3>
            <p>Success Rate</p>
            <small class="text-muted">Interest acceptance</small>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Registrations -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Registrations</h5>
                <a href="<?php echo SITE_URL; ?>admin/users.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_users)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th>Location</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo SITE_URL; ?>admin/view_user.php?id=<?php echo $user['id']; ?>">
                                                <?php echo $user['first_name'] . ' ' . $user['last_name']; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['gender'] == 'Male' ? 'info' : 'danger'; ?>">
                                                <?php echo $user['gender']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $user['age']; ?></td>
                                        <td><?php echo $user['city']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent registrations found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Activities -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Activities</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recent_activities)): ?>
                    <div class="activity-list">
                        <?php foreach ($recent_activities as $activity): ?>
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <?php if ($activity['activity_type'] == 'interest'): ?>
                                        <i class="fas fa-heart text-pink"></i>
                                    <?php else: ?>
                                        <i class="fas fa-comment text-primary"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted">
                                        <?php echo date('M j, Y H:i', strtotime($activity['created_at'])); ?>
                                    </small>
                                    <div>
                                        <?php if ($activity['activity_type'] == 'interest'): ?>
                                            <strong><?php echo $activity['sender_name']; ?></strong> sent interest to 
                                            <strong><?php echo $activity['receiver_name']; ?></strong>
                                        <?php else: ?>
                                            <strong><?php echo $activity['sender_name']; ?></strong> messaged 
                                            <strong><?php echo $activity['receiver_name']; ?></strong>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No recent activities found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Membership Distribution -->
<div class="row mt-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Membership Distribution</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($membership_stats)): ?>
                    <div class="row">
                        <?php foreach ($membership_stats as $membership): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><?php echo $membership['membership_plan']; ?></span>
                                    <span class="badge bg-primary"><?php echo $membership['count']; ?></span>
                                </div>
                                <div class="progress mt-1">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?php echo ($membership['count'] / $stats['total_users']) * 100; ?>%"
                                         aria-valuenow="<?php echo $membership['count']; ?>" 
                                         aria-valuemin="0" aria-valuemax="<?php echo $stats['total_users']; ?>">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No membership data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="<?php echo SITE_URL; ?>admin/users.php" class="btn btn-outline-primary btn-lg w-100">
                            <i class="fas fa-users fa-2x d-block mb-2"></i>
                            Manage Users
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="<?php echo SITE_URL; ?>admin/castes.php" class="btn btn-outline-success btn-lg w-100">
                            <i class="fas fa-list fa-2x d-block mb-2"></i>
                            Manage Castes
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="<?php echo SITE_URL; ?>admin/reports.php" class="btn btn-outline-info btn-lg w-100">
                            <i class="fas fa-chart-bar fa-2x d-block mb-2"></i>
                            View Reports
                        </a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="<?php echo SITE_URL; ?>admin/settings.php" class="btn btn-outline-warning btn-lg w-100">
                            <i class="fas fa-cog fa-2x d-block mb-2"></i>
                            Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
