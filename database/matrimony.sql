-- Online Matrimonial Website Database Schema
-- Created for PHP Matrimonial Management System

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: matrimony
CREATE DATABASE IF NOT EXISTS matrimony DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE matrimony;

-- Table structure for users
CREATE TABLE users (
    id int(11) NOT NULL AUTO_INCREMENT,
    username varchar(50) NOT NULL,
    email varchar(100) NOT NULL,
    password varchar(255) NOT NULL,
    first_name varchar(50) NOT NULL,
    last_name varchar(50) NOT NULL,
    gender enum('Male','Female') NOT NULL,
    date_of_birth date NOT NULL,
    religion varchar(50) NOT NULL,
    caste varchar(50) NOT NULL,
    marital_status enum('Never Married','Divorced','Widowed','Awaiting Divorce') NOT NULL,
    height varchar(10) DEFAULT NULL,
    weight varchar(10) DEFAULT NULL,
    complexion varchar(30) DEFAULT NULL,
    body_type varchar(30) DEFAULT NULL,
    physical_status varchar(30) DEFAULT NULL,
    mother_tongue varchar(50) NOT NULL,
    country varchar(50) NOT NULL,
    state varchar(50) NOT NULL,
    city varchar(50) NOT NULL,
    education varchar(100) DEFAULT NULL,
    occupation varchar(100) DEFAULT NULL,
    annual_income varchar(50) DEFAULT NULL,
    about_me text DEFAULT NULL,
    profile_photo varchar(255) DEFAULT NULL,
    kundali_file varchar(255) DEFAULT NULL,
    phone varchar(20) DEFAULT NULL,
    is_active tinyint(1) DEFAULT 1,
    is_verified tinyint(1) DEFAULT 0,
    membership_plan enum('Free','Basic','Premium','Gold') DEFAULT 'Free',
    membership_expiry date DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY username (username),
    UNIQUE KEY email (email),
    KEY gender (gender),
    KEY religion (religion),
    KEY caste (caste),
    KEY marital_status (marital_status),
    KEY is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for family_details
CREATE TABLE family_details (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    father_name varchar(100) DEFAULT NULL,
    father_occupation varchar(100) DEFAULT NULL,
    mother_name varchar(100) DEFAULT NULL,
    mother_occupation varchar(100) DEFAULT NULL,
    brothers int(11) DEFAULT 0,
    married_brothers int(11) DEFAULT 0,
    sisters int(11) DEFAULT 0,
    married_sisters int(11) DEFAULT 0,
    family_type enum('Joint','Nuclear') DEFAULT NULL,
    family_values varchar(100) DEFAULT NULL,
    family_income varchar(50) DEFAULT NULL,
    ancestral_origin varchar(100) DEFAULT NULL,
    about_family text DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for partner_preferences
CREATE TABLE partner_preferences (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    age_from int(11) DEFAULT NULL,
    age_to int(11) DEFAULT NULL,
    height_from varchar(10) DEFAULT NULL,
    height_to varchar(10) DEFAULT NULL,
    marital_status enum('Never Married','Divorced','Widowed','Awaiting Divorce','Any') DEFAULT 'Any',
    religion varchar(50) DEFAULT NULL,
    caste varchar(50) DEFAULT NULL,
    mother_tongue varchar(50) DEFAULT NULL,
    country varchar(50) DEFAULT NULL,
    education varchar(100) DEFAULT NULL,
    occupation varchar(100) DEFAULT NULL,
    annual_income varchar(50) DEFAULT NULL,
    diet enum('Vegetarian','Non-Vegetarian','Eggetarian','Any') DEFAULT 'Any',
    smoking enum('Yes','No','Any') DEFAULT 'Any',
    drinking enum('Yes','No','Any') DEFAULT 'Any',
    about_partner text DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY user_id (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for messages
CREATE TABLE messages (
    id int(11) NOT NULL AUTO_INCREMENT,
    sender_id int(11) NOT NULL,
    receiver_id int(11) NOT NULL,
    message text NOT NULL,
    is_read tinyint(1) DEFAULT 0,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY sender_id (sender_id),
    KEY receiver_id (receiver_id),
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for interests
CREATE TABLE interests (
    id int(11) NOT NULL AUTO_INCREMENT,
    sender_id int(11) NOT NULL,
    receiver_id int(11) NOT NULL,
    status enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
    message text DEFAULT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_interest (sender_id, receiver_id),
    KEY sender_id (sender_id),
    KEY receiver_id (receiver_id),
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for shortlists
CREATE TABLE shortlists (
    id int(11) NOT NULL AUTO_INCREMENT,
    user_id int(11) NOT NULL,
    shortlisted_user_id int(11) NOT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_shortlist (user_id, shortlisted_user_id),
    KEY user_id (user_id),
    KEY shortlisted_user_id (shortlisted_user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shortlisted_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for profile_views
CREATE TABLE profile_views (
    id int(11) NOT NULL AUTO_INCREMENT,
    viewer_id int(11) NOT NULL,
    viewed_id int(11) NOT NULL,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY viewer_id (viewer_id),
    KEY viewed_id (viewed_id),
    FOREIGN KEY (viewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (viewed_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for admin
CREATE TABLE admin (
    id int(11) NOT NULL AUTO_INCREMENT,
    username varchar(50) NOT NULL,
    email varchar(100) NOT NULL,
    password varchar(255) NOT NULL,
    full_name varchar(100) NOT NULL,
    is_active tinyint(1) DEFAULT 1,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY username (username),
    UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for castes
CREATE TABLE castes (
    id int(11) NOT NULL AUTO_INCREMENT,
    caste_name varchar(100) NOT NULL,
    religion varchar(50) NOT NULL,
    is_active tinyint(1) DEFAULT 1,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY caste_religion (caste_name, religion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for religions
CREATE TABLE religions (
    id int(11) NOT NULL AUTO_INCREMENT,
    religion_name varchar(50) NOT NULL,
    is_active tinyint(1) DEFAULT 1,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY religion_name (religion_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO admin (username, email, password, full_name) VALUES 
('admin', 'admin@matrimony.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator');

-- Insert default religions
INSERT INTO religions (religion_name) VALUES 
('Hindu'), ('Muslim'), ('Christian'), ('Sikh'), ('Buddhist'), ('Jain'), ('Parsi'), ('Jewish'), ('Other');

-- Insert default castes
INSERT INTO castes (caste_name, religion) VALUES 
('Brahmin', 'Hindu'), ('Kshatriya', 'Hindu'), ('Vaishya', 'Hindu'), ('Shudra', 'Hindu'),
('Sunni', 'Muslim'), ('Shia', 'Muslim'),
('Catholic', 'Christian'), ('Protestant', 'Christian'),
('Jat', 'Sikh'), ('Khatri', 'Sikh');

-- Insert sample users for testing
INSERT INTO users (username, email, password, first_name, last_name, gender, date_of_birth, religion, caste, marital_status, height, mother_tongue, country, state, city, education, occupation, annual_income, about_me, phone) VALUES 
('rahul_sharma', 'rahul@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rahul', 'Sharma', 'Male', '1990-05-15', 'Hindu', 'Brahmin', 'Never Married', '5\'10"', 'Hindi', 'India', 'Maharashtra', 'Mumbai', 'MBA', 'Software Engineer', '15-20 Lakhs', 'Simple and down to earth person looking for a life partner who values family.', '9876543210'),
('priya_patel', 'priya@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya', 'Patel', 'Female', '1992-08-20', 'Hindu', 'Vaishya', 'Never Married', '5\'4"', 'Gujarati', 'India', 'Gujarat', 'Ahmedabad', 'M.Com', 'Accountant', '8-10 Lakhs', 'Family-oriented girl with modern outlook seeking a compatible life partner.', '9876543211');

COMMIT;
