# JourneyHub

> Plan better journeys, organize every detail, and share the experience.

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Apache](https://img.shields.io/badge/Apache-XAMPP-D22128?style=for-the-badge&logo=apache&logoColor=white)](https://www.apachefriends.org/)

JourneyHub is a PHP and MySQL web application for planning, organizing, and sharing trips. Users can create itineraries, manage destinations and activities, track expenses, view trip dates on a calendar, and share selected trips with others.

## Features

- User registration, login, logout, and password reset
- Session-based authentication with user and administrator roles
- Profile management, including profile photo uploads
- Trip creation and management with dates, descriptions, and cover images
- Multi-stop itineraries with destination search and stop reordering
- Activity discovery and scheduling
- Calendar view for trip plans and activities
- Budget and expense tracking in Indian rupees (INR)
- Public trip sharing through generated share links
- Administrator views for managing users, trips, cities, and activities
- Responsive interface for desktop and mobile screens

## Requirements

- XAMPP with Apache and MySQL enabled
- PHP 7.4 or later with the PDO MySQL extension
- A modern web browser

## Local Setup

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open [phpMyAdmin](http://localhost/phpmyadmin).
3. Create a database named `journeyhub` using `utf8mb4`.
4. Import `database/schema.sql`, followed by `database/seed.sql`.
5. Verify the connection at [database/test.php](database/test.php).
6. Open the application at [http://localhost/JourneyHub/](http://localhost/JourneyHub/).

The default XAMPP connection is configured in `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'journeyhub');
define('DB_USER', 'root');
define('DB_PASS', '');
```

If your MySQL `root` account has a password, update `DB_PASS`. The application uses
port `3306` by default. For another port, add it to `DB_HOST`, such as
`127.0.0.1:3307`.

### MySQL84 Service Conflict

If you see `Access denied for user 'root'@'localhost' (using password: NO)`, Windows
may be running the separate `MySQL84` service instead of XAMPP MySQL. Stop `MySQL84`
and start MySQL from XAMPP, or use the MySQL84 root password in `config/db.php`.
Only one MySQL service should listen on port `3306`.

## Login

There is one login page for regular users and administrators:

- Login: [pages/login.php](pages/login.php)
- Admin dashboard: [admin/index.php](admin/index.php)

After signing in, administrators can open the admin dashboard. Regular users are
redirected to the normal dashboard if they try to access the admin area.

### Seeded Development Accounts

These accounts are for local development only. Do not use them in production.

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@journeyhub.test` | `password123` |
| User | `rahul.patel@journeyhub.test` | `password123` |
| User | `priya.shah@journeyhub.test` | `password123` |

The seed file contains additional regular users. Their passwords are also
`password123`.

## Useful Pages

- Sign up: [pages/signup.php](pages/signup.php)
- Forgot password: [pages/forgot-password.php](pages/forgot-password.php)
- Database test: [database/test.php](database/test.php)

## Project Structure

```text
JourneyHub/
├── admin/                 Administrator pages
├── api/                   Database-backed API endpoints
├── assets/
│   ├── css/               Application stylesheets
│   ├── images/            Covers and profile images
│   └── js/                Frontend JavaScript modules
├── config/                Database configuration
├── database/              Schema, seed data, and database utilities
├── includes/              Shared authentication and navigation helpers
├── pages/                 User-facing application pages
├── index.php              Application entry point
├── SETUP.md               Detailed setup and authentication notes
└── README.md              Project documentation
```

## Development Notes

- Use prepared PDO statements for all database queries.
- Use the `cities` table as the source of truth for destination searches; do not hardcode city lists in PHP or JavaScript.
- Monetary values are stored and displayed in Indian rupees (INR).
- Keep uploaded files in the appropriate `assets/images/` directory and validate their type and size.
- Do not commit production credentials or user-uploaded files to version control.

## Troubleshooting

### Database connection errors

Confirm that MySQL is running, both SQL files have been imported, and the credentials in `config/db.php` match your local XAMPP setup.

### CSS or JavaScript changes are not visible

Refresh the browser with its cache disabled or clear the browser cache. Also confirm that Apache is serving the correct `JourneyHub` directory.

### Uploaded images are not saved

Make sure the relevant directory under `assets/images/` exists and is writable by Apache. Check the upload size and file type restrictions in the application configuration.

## Security and Production Checklist

Before deploying JourneyHub:

- Use environment variables or a secrets manager for database credentials.
- Change all seeded accounts and passwords.
- Enable HTTPS.
- Add CSRF protection to state-changing forms and endpoints.
- Configure rate limiting for authentication endpoints.
- Disable displaying PHP errors to end users and enable secure error logging.
- Configure reliable password-reset email delivery.
- Review upload permissions and validate uploaded content server-side.
- Set up regular database backups.

## License

No license has been specified for this project yet.
