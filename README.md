# Online Matrimonial Website

A comprehensive PHP-based matrimonial platform that helps individuals find their perfect life partners based on their preferences and requirements.

## Features

### User Features
- **User Registration & Authentication**: Secure registration and login system
- **Profile Management**: Complete personal, family, and partner preference profiles
- **Advanced Search**: Search profiles by various criteria (age, religion, caste, location, etc.)
- **Matching System**: Automatic matching based on partner preferences
- **Interest System**: Send and receive interests from other users
- **Messaging**: Secure messaging between matched users
- **Shortlisting**: Save interesting profiles for later review
- **Profile Views**: Track who viewed your profile
- **Photo & Kundali Upload**: Upload profile photos and kundali files

### Admin Features
- **Dashboard**: Comprehensive admin dashboard with statistics
- **User Management**: View, edit, block/unblock, and delete users
- **Content Management**: Manage religions, castes, and other master data
- **Reports**: View various reports and analytics
- **Settings**: Configure site settings and preferences

### Technical Features
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5
- **Secure Authentication**: Password hashing and session management
- **Database**: MySQL with PDO for secure database operations
- **File Uploads**: Secure file upload handling
- **Email Notifications**: Automated email notifications
- **Search & Filtering**: Advanced search with multiple filters
- **Pagination**: Efficient pagination for large datasets

## Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, jQuery, Font Awesome
- **Backend**: PHP 7.4+, MySQL
- **Database**: MySQL with PDO
- **File Structure**: MVC-like organization with includes, user, and admin modules

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP/LAMP stack

### Steps

1. **Download and Extract**
   ```bash
   # Download the project and extract to your web server directory
   # For XAMPP: C:/xampp/htdocs/Matrimony/
   ```

2. **Database Setup**
   ```sql
   # Create database and import the SQL file
   - Open phpMyAdmin
   - Create database named "matrimony"
   - Import the "database/matrimony.sql" file
   ```

3. **Configure Database**
   ```php
   # Edit includes/config.php and update database credentials
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'matrimony');
   ```

4. **Set Permissions**
   ```bash
   # Make upload directories writable
   chmod 777 uploads/
   chmod 777 uploads/photos/
   chmod 777 uploads/kundali/
   ```

5. **Access the Application**
   ```
   # Open your browser and navigate to:
   http://localhost/Matrimony/
   ```

## Default Login Credentials

### Admin Login
- **Username**: admin
- **Password**: admin123

### Sample User Logins
- **Username**: rahul_sharma
- **Password**: password123

- **Username**: priya_patel
- **Password**: password123

## Project Structure

```
Online Matrimonial Website/
├── admin/                  # Admin module
│   ├── login.php          # Admin login
│   ├── dashboard.php      # Admin dashboard
│   ├── users.php          # User management
│   ├── castes.php         # Caste management
│   └── logout.php         # Admin logout
├── user/                   # User module
│   ├── dashboard.php      # User dashboard
│   ├── profile.php        # Profile management
│   ├── search.php         # Search profiles
│   ├── matches.php        # View matches
│   ├── inbox.php          # Messaging
│   ├── interests.php      # Interest management
│   ├── shortlists.php     # Shortlisted profiles
│   └── logout.php         # User logout
├── includes/              # Common files
│   ├── config.php         # Configuration and database
│   ├── functions.php      # Helper functions
│   ├── header.php         # Header template
│   └── footer.php         # Footer template
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Static images
├── uploads/               # User uploads
│   ├── photos/           # Profile photos
│   └── kundali/          # Kundali files
├── database/              # Database files
│   └── matrimony.sql     # Database schema
├── index.php             # Homepage
├── login.php             # User login
├── register.php          # User registration
└── README.md             # This file
```

## Database Schema

The application uses the following main tables:

- **users**: User profiles and personal information
- **family_details**: Family background information
- **partner_preferences**: Partner preference settings
- **messages**: User-to-user messaging
- **interests**: Interest requests between users
- **shortlists**: User shortlisted profiles
- **profile_views**: Profile view tracking
- **admin**: Administrator accounts
- **religions**: Religion master data
- **castes**: Caste master data

## Key Features Explained

### Registration Process
1. User fills registration form with personal details
2. System validates data and checks for duplicates
3. Account is created and welcome email is sent
4. User can log in and complete their profile

### Profile Matching
1. Users set their partner preferences
2. System finds compatible matches based on preferences
3. Matches are displayed on dashboard and search results
4. Users can send interests to matched profiles

### Communication
1. Users send interest to profiles they like
2. Interest can be accepted or rejected
3. Once accepted, users can exchange messages
4. All communication is tracked and secure

### Admin Management
1. Admin can view all registered users
2. Can block/unblock or delete users
3. Manage master data (religions, castes)
4. View reports and site statistics

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's password_hash()
- **SQL Injection Prevention**: Using PDO prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **Session Management**: Secure session handling
- **File Upload Security**: File type and size validation
- **Access Control**: Role-based access control (User/Admin)

## Customization

### Adding New Fields
1. Update database schema by adding columns to relevant tables
2. Modify the registration/profile forms to include new fields
3. Update the processing logic to handle new fields

### Changing Theme
1. Modify CSS files in `assets/css/style.css`
2. Update color schemes and layouts
3. Add custom JavaScript if needed

### Email Configuration
1. Update email settings in `includes/config.php`
2. Configure SMTP settings if required
3. Modify email templates as needed

## Support

For support and inquiries:
- Email: admin@matrimony.com
- Phone: +91 98765 43210

## License

This project is for educational and demonstration purposes. Please ensure you have proper licensing for production use.

## Version History

- **v1.0.0**: Initial release with core matrimonial features
- Complete user registration and authentication
- Profile management system
- Search and matching functionality
- Admin panel with user management
- Messaging and interest system

---

**Note**: This is a demonstration project. For production use, please ensure proper security measures, testing, and compliance with data protection regulations.
