<?php
// Additional Helper Functions

function get_user_by_id($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function get_user_family_details($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM family_details WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function get_user_partner_preferences($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM partner_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function update_user_profile($user_id, $data) {
    global $conn;
    
    $fields = [];
    $values = [];
    
    foreach ($data as $field => $value) {
        $fields[] = "$field = ?";
        $values[] = $value;
    }
    
    $values[] = $user_id;
    
    $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
    $stmt = $conn->prepare($sql);
    return $stmt->execute($values);
}

function search_profiles($search_params, $page = 1, $limit = 10) {
    global $conn;
    
    $where_conditions = ["u.is_active = 1"];
    $values = [];
    
    // Build search conditions
    if (!empty($search_params['gender'])) {
        $where_conditions[] = "u.gender = ?";
        $values[] = $search_params['gender'];
    }
    
    if (!empty($search_params['age_from'])) {
        $where_conditions[] = "TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) >= ?";
        $values[] = $search_params['age_from'];
    }
    
    if (!empty($search_params['age_to'])) {
        $where_conditions[] = "TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) <= ?";
        $values[] = $search_params['age_to'];
    }
    
    if (!empty($search_params['religion'])) {
        $where_conditions[] = "u.religion = ?";
        $values[] = $search_params['religion'];
    }
    
    if (!empty($search_params['caste'])) {
        $where_conditions[] = "u.caste = ?";
        $values[] = $search_params['caste'];
    }
    
    if (!empty($search_params['marital_status'])) {
        $where_conditions[] = "u.marital_status = ?";
        $values[] = $search_params['marital_status'];
    }
    
    if (!empty($search_params['mother_tongue'])) {
        $where_conditions[] = "u.mother_tongue = ?";
        $values[] = $search_params['mother_tongue'];
    }
    
    if (!empty($search_params['country'])) {
        $where_conditions[] = "u.country = ?";
        $values[] = $search_params['country'];
    }
    
    if (!empty($search_params['state'])) {
        $where_conditions[] = "u.state = ?";
        $values[] = $search_params['state'];
    }
    
    if (!empty($search_params['city'])) {
        $where_conditions[] = "u.city LIKE ?";
        $values[] = '%' . $search_params['city'] . '%';
    }
    
    if (!empty($search_params['education'])) {
        $where_conditions[] = "u.education LIKE ?";
        $values[] = '%' . $search_params['education'] . '%';
    }
    
    if (!empty($search_params['occupation'])) {
        $where_conditions[] = "u.occupation LIKE ?";
        $values[] = '%' . $search_params['occupation'] . '%';
    }
    
    // Exclude current user if logged in
    if (is_logged_in()) {
        $where_conditions[] = "u.id != ?";
        $values[] = get_user_id();
    }
    
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
    
    $query = "SELECT u.*, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age 
              FROM users u $where_clause 
              ORDER BY u.created_at DESC";
    
    return paginate($query, $page, $limit);
}

function get_matches_for_user($user_id, $page = 1, $limit = 10) {
    global $conn;
    
    // Get user's partner preferences
    $preferences = get_user_partner_preferences($user_id);
    if (!$preferences) {
        return ['results' => [], 'pagination' => []];
    }
    
    $user = get_user_by_id($user_id);
    if (!$user) {
        return ['results' => [], 'pagination' => []];
    }
    
    $where_conditions = ["u.is_active = 1", "u.id != ?", "u.gender != ?"];
    $values = [$user_id, $user['gender']];
    
    // Apply preferences
    if (!empty($preferences['age_from'])) {
        $where_conditions[] = "TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) >= ?";
        $values[] = $preferences['age_from'];
    }
    
    if (!empty($preferences['age_to'])) {
        $where_conditions[] = "TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) <= ?";
        $values[] = $preferences['age_to'];
    }
    
    if (!empty($preferences['religion'])) {
        $where_conditions[] = "u.religion = ?";
        $values[] = $preferences['religion'];
    }
    
    if (!empty($preferences['caste'])) {
        $where_conditions[] = "u.caste = ?";
        $values[] = $preferences['caste'];
    }
    
    if (!empty($preferences['marital_status']) && $preferences['marital_status'] != 'Any') {
        $where_conditions[] = "u.marital_status = ?";
        $values[] = $preferences['marital_status'];
    }
    
    if (!empty($preferences['mother_tongue'])) {
        $where_conditions[] = "u.mother_tongue = ?";
        $values[] = $preferences['mother_tongue'];
    }
    
    if (!empty($preferences['country'])) {
        $where_conditions[] = "u.country = ?";
        $values[] = $preferences['country'];
    }
    
    if (!empty($preferences['education'])) {
        $where_conditions[] = "u.education LIKE ?";
        $values[] = '%' . $preferences['education'] . '%';
    }
    
    if (!empty($preferences['occupation'])) {
        $where_conditions[] = "u.occupation LIKE ?";
        $values[] = '%' . $preferences['occupation'] . '%';
    }
    
    $where_clause = "WHERE " . implode(' AND ', $where_conditions);
    
    $query = "SELECT u.*, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age 
              FROM users u $where_clause 
              ORDER BY u.created_at DESC";
    
    return paginate($query, $page, $limit);
}

function send_interest($sender_id, $receiver_id, $message = '') {
    global $conn;
    
    // Check if interest already exists
    $stmt = $conn->prepare("SELECT id FROM interests WHERE sender_id = ? AND receiver_id = ?");
    $stmt->execute([$sender_id, $receiver_id]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Interest already sent'];
    }
    
    // Insert new interest
    $stmt = $conn->prepare("INSERT INTO interests (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $result = $stmt->execute([$sender_id, $receiver_id, $message]);
    
    if ($result) {
        // Send notification email to receiver
        $sender = get_user_by_id($sender_id);
        $receiver = get_user_by_id($receiver_id);
        
        if ($receiver) {
            $subject = "New Interest Received - " . SITE_NAME;
            $email_message = "
            <h2>New Interest Received</h2>
            <p>Dear {$receiver['first_name']},</p>
            <p>You have received a new interest from {$sender['first_name']} {$sender['last_name']}.</p>
            <p><strong>Sender Details:</strong></p>
            <ul>
                <li>Name: {$sender['first_name']} {$sender['last_name']}</li>
                <li>Age: " . calculate_age($sender['date_of_birth']) . "</li>
                <li>Religion: {$sender['religion']}</li>
                <li>Caste: {$sender['caste']}</li>
                <li>Location: {$sender['city']}, {$sender['state']}</li>
                <li>Education: {$sender['education']}</li>
                <li>Occupation: {$sender['occupation']}</li>
            </ul>
            " . ($message ? "<p><strong>Message:</strong> $message</p>" : "") . "
            <p>Log in to your account to view the complete profile and respond to this interest.</p>
            <p>Best regards,<br>" . SITE_NAME . " Team</p>
            ";
            
            send_email($receiver['email'], $subject, $email_message);
        }
        
        return ['success' => true, 'message' => 'Interest sent successfully'];
    }
    
    return ['success' => false, 'message' => 'Failed to send interest'];
}

function update_interest_status($interest_id, $status) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE interests SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$status, $interest_id]);
}

function get_interests($user_id, $type = 'received', $page = 1, $limit = 10) {
    global $conn;
    
    if ($type == 'sent') {
        $query = "SELECT i.*, u.first_name, u.last_name, u.profile_photo, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age
                  FROM interests i 
                  JOIN users u ON i.receiver_id = u.id 
                  WHERE i.sender_id = ? 
                  ORDER BY i.created_at DESC";
        $values = [$user_id];
    } else {
        $query = "SELECT i.*, u.first_name, u.last_name, u.profile_photo, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age
                  FROM interests i 
                  JOIN users u ON i.sender_id = u.id 
                  WHERE i.receiver_id = ? 
                  ORDER BY i.created_at DESC";
        $values = [$user_id];
    }
    
    return paginate($query, $page, $limit, $values);
}

function send_message($sender_id, $receiver_id, $message) {
    global $conn;
    
    // Check if users can message each other (accepted interest)
    $stmt = $conn->prepare("SELECT id FROM interests WHERE 
                           ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) 
                           AND status = 'Accepted'");
    $stmt->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
    
    if (!$stmt->fetch()) {
        return ['success' => false, 'message' => 'You can only message users with accepted interests'];
    }
    
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $result = $stmt->execute([$sender_id, $receiver_id, $message]);
    
    if ($result) {
        return ['success' => true, 'message' => 'Message sent successfully'];
    }
    
    return ['success' => false, 'message' => 'Failed to send message'];
}

function get_messages($user_id, $other_user_id, $page = 1, $limit = 20) {
    global $conn;
    
    $query = "SELECT m.*, u.first_name, u.last_name, u.profile_photo
              FROM messages m 
              JOIN users u ON m.sender_id = u.id 
              WHERE ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
              ORDER BY m.created_at ASC";
    
    $values = [$user_id, $other_user_id, $other_user_id, $user_id];
    
    return paginate($query, $page, $limit, $values);
}

function mark_messages_as_read($user_id, $sender_id) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?");
    return $stmt->execute([$user_id, $sender_id]);
}

function add_to_shortlist($user_id, $shortlisted_user_id) {
    global $conn;
    
    // Check if already shortlisted
    $stmt = $conn->prepare("SELECT id FROM shortlists WHERE user_id = ? AND shortlisted_user_id = ?");
    $stmt->execute([$user_id, $shortlisted_user_id]);
    
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Already shortlisted'];
    }
    
    $stmt = $conn->prepare("INSERT INTO shortlists (user_id, shortlisted_user_id) VALUES (?, ?)");
    $result = $stmt->execute([$user_id, $shortlisted_user_id]);
    
    if ($result) {
        return ['success' => true, 'message' => 'Added to shortlist'];
    }
    
    return ['success' => false, 'message' => 'Failed to add to shortlist'];
}

function remove_from_shortlist($user_id, $shortlisted_user_id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM shortlists WHERE user_id = ? AND shortlisted_user_id = ?");
    $result = $stmt->execute([$user_id, $shortlisted_user_id]);
    
    if ($result) {
        return ['success' => true, 'message' => 'Removed from shortlist'];
    }
    
    return ['success' => false, 'message' => 'Failed to remove from shortlist'];
}

function get_shortlisted_profiles($user_id, $page = 1, $limit = 10) {
    global $conn;
    
    $query = "SELECT u.*, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age, s.created_at as shortlisted_on
              FROM shortlists s 
              JOIN users u ON s.shortlisted_user_id = u.id 
              WHERE s.user_id = ? AND u.is_active = 1 
              ORDER BY s.created_at DESC";
    
    return paginate($query, $page, $limit, [$user_id]);
}

function is_shortlisted($user_id, $profile_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM shortlists WHERE user_id = ? AND shortlisted_user_id = ?");
    $stmt->execute([$user_id, $profile_id]);
    
    return $stmt->fetch() ? true : false;
}

function record_profile_view($viewer_id, $viewed_id) {
    global $conn;
    
    $stmt = $conn->prepare("INSERT INTO profile_views (viewer_id, viewed_id) VALUES (?, ?)");
    return $stmt->execute([$viewer_id, $viewed_id]);
}

function get_profile_views($user_id, $page = 1, $limit = 10) {
    global $conn;
    
    $query = "SELECT pv.*, u.first_name, u.last_name, u.profile_photo, TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) as age
              FROM profile_views pv 
              JOIN users u ON pv.viewer_id = u.id 
              WHERE pv.viewed_id = ? AND u.is_active = 1 
              ORDER BY pv.created_at DESC";
    
    return paginate($query, $page, $limit, [$user_id]);
}

function get_religions() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM religions WHERE is_active = 1 ORDER BY religion_name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function get_castes($religion = null) {
    global $conn;
    
    if ($religion) {
        $stmt = $conn->prepare("SELECT * FROM castes WHERE religion = ? AND is_active = 1 ORDER BY caste_name");
        $stmt->execute([$religion]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM castes WHERE is_active = 1 ORDER BY caste_name");
        $stmt->execute();
    }
    
    return $stmt->fetchAll();
}
?>
