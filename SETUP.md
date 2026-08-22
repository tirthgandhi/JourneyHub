# JourneyHub Authentication Setup

## Prerequisites
- XAMPP installed and running (Apache + MySQL)
- PHP 7.4+ with PDO extension
- MySQL/MariaDB

## Database Setup

1. **Create the database:**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create a new database named `journeyhub`
   - Set charset to `utf8mb4_unicode_ci`

2. **Import the schema:**
   - Click on the `journeyhub` database
   - Go to the "SQL" tab
   - Copy and paste the contents of `database/schema.sql`
   - Click "Go" to execute

   OR use MySQL CLI:
   ```bash
   mysql -u root -p
   CREATE DATABASE journeyhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   USE journeyhub;
   source C:/xampp/htdocs/JourneyHub/database/schema.sql;
   ```

3. **Verify database configuration:**
   - Check `config/database.php` and update credentials if needed:
     - Host: `localhost`
     - Database: `journeyhub`
     - User: `root`
     - Password: (empty by default on XAMPP)

## Testing the Authentication System

### Access the Application
Navigate to: `http://localhost/JourneyHub/`

This will redirect you to the login page.

### Test Flow

1. **Sign Up (Create Account)**
   - Go to: `http://localhost/JourneyHub/pages/signup.php`
   - Fill in the form:
     - Name: Your Full Name
     - Email: test@example.com
     - Password: password123 (min 8 characters)
     - Confirm Password: password123
   - Click "Create Account"
   - You'll be automatically logged in and redirected to the dashboard

2. **Login**
   - Go to: `http://localhost/JourneyHub/pages/login.php`
   - Use the credentials you created
   - Or use the default admin account:
     - Email: admin@journeyhub.local
     - Password: admin123

3. **Profile Management**
   - Click "Profile" in the navigation
   - Update your name, email, or language preference
   - Upload a profile photo (max 5MB, JPEG/PNG/GIF/WebP)
   - Add saved destinations (optional)
   - Click "Save Changes"

4. **Forgot Password Flow**
   - Click "Forgot password?" on the login page
   - Enter your email address
   - Copy the reset link shown (in production, this would be emailed)
   - Paste the link in your browser
   - Set a new password
   - Login with your new password

5. **Delete Account**
   - Go to Profile page
   - Scroll to "Danger Zone"
   - Click "Delete Account"
   - Type "DELETE" to confirm
   - Your account will be permanently deleted

6. **Logout**
   - Click "Logout" in any navigation menu
   - You'll be redirected to the login page

## Features Implemented

### Authentication
- ✅ User registration with validation
- ✅ Login with password verification
- ✅ Session-based authentication
- ✅ Logout functionality
- ✅ Password reset flow with tokens
- ✅ Role-based access (user/admin)

### Security
- ✅ Password hashing with `password_hash()`
- ✅ Prepared statements (PDO) - no SQL injection
- ✅ XSS prevention with `htmlspecialchars()`
- ✅ Frontend & backend validation
- ✅ Session guards for protected pages
- ✅ File upload validation (type & size)

### Profile Management
- ✅ Edit name, email, language
- ✅ Profile photo upload with preview
- ✅ Saved destinations list
- ✅ Account deletion with confirmation
- ✅ View account information

### User Experience
- ✅ Responsive design (mobile-friendly)
- ✅ Real-time frontend validation
- ✅ Error and success messages
- ✅ Smooth gradients and modern UI
- ✅ Dashboard with welcome message

## File Structure

```
JourneyHub/
├── config/
│   └── database.php           # PDO database connection
├── database/
│   └── schema.sql             # Database schema (users, password_resets)
├── includes/
│   └── auth-check.php         # Session guards and auth helpers
├── pages/
│   ├── signup.php             # User registration
│   ├── login.php              # User login
│   ├── logout.php             # Logout script
│   ├── forgot-password.php    # Request password reset
│   ├── reset-password.php     # Reset password with token
│   ├── profile.php            # Profile management
│   └── dashboard.php          # Main dashboard
├── assets/
│   ├── css/
│   │   ├── style.css          # Global styles
│   │   └── auth.css           # Authentication styles
│   ├── js/
│   │   ├── app.js             # Main app JS
│   │   └── auth.js            # Auth validation JS
│   └── images/
│       └── profiles/          # Profile photo uploads
└── index.php                  # Entry point (redirects based on session)
```

## Troubleshooting

### "Database connection failed"
- Ensure MySQL is running in XAMPP
- Verify database name is `journeyhub`
- Check credentials in `config/database.php`

### "Failed to upload file"
- Ensure `assets/images/profiles/` directory exists
- Check directory permissions (should be writable)
- Verify file size is under 5MB
- Check file type is JPEG, PNG, GIF, or WebP

### Session issues
- Clear your browser cookies
- Ensure PHP session is working: check `php.ini` for session settings
- Verify `session.save_path` is writable

### Styling not loading
- Clear browser cache
- Check that Apache is serving the correct directory
- Verify CSS files exist in `assets/css/`

## Next Steps

This authentication system is ready for integration with:
- Trip planning features
- Destination browsing
- Itinerary management
- Budget tracking
- Social features (following, sharing)
- Admin panel for user management

## Security Notes for Production

Before deploying to production:
1. Change database credentials in `config/database.php`
2. Enable HTTPS (SSL/TLS)
3. Implement CSRF protection
4. Add rate limiting for login attempts
5. Implement real email sending for password resets
6. Set up proper error logging (don't display errors to users)
7. Use environment variables for sensitive config
8. Implement account lockout after failed login attempts
9. Add remember-me functionality with secure tokens
10. Set up database backups

---

**Built for the feature/auth branch of JourneyHub** 🚀
