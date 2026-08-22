# JourneyHub

JourneyHub is a PHP and MySQL travel planning and trip-sharing application.

## Requirements

- XAMPP with Apache, PHP, and MySQL
- PHP 7.4 or later with PDO MySQL enabled
- A MySQL database named `journeyhub`

## Local Setup

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open phpMyAdmin at <http://localhost/phpmyadmin>.
3. Create a database named `journeyhub` using `utf8mb4`.
4. Import these files in order:
	- `database/schema.sql`
	- `database/seed.sql`
5. Verify the connection at <http://localhost/JourneyHub/database/test.php>.
6. Open the application at <http://localhost/JourneyHub/>.

The default XAMPP connection is configured in `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'journeyhub');
define('DB_USER', 'root');
define('DB_PASS', '');
```

If your MySQL `root` account has a password, update `DB_PASS` with that password.
The application uses port `3306` by default. If MySQL uses another port, add it to
`DB_HOST`, for example `127.0.0.1:3307`.

### MySQL84 Service Conflict

If the error says `Access denied for user 'root'@'localhost' (using password: NO)`,
Windows may be running the separate `MySQL84` service instead of XAMPP MySQL.
Stop `MySQL84` and start MySQL from XAMPP, or use the MySQL84 root password in
`config/db.php`. Only one MySQL service should listen on port `3306`.

## Login

There is one login page for both regular users and administrators:

- Login: <http://localhost/JourneyHub/pages/login.php>
- Admin dashboard: <http://localhost/JourneyHub/admin/index.php>

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

- Sign up: <http://localhost/JourneyHub/pages/signup.php>
- Forgot password: <http://localhost/JourneyHub/pages/forgot-password.php>
- Database test: <http://localhost/JourneyHub/database/test.php>

## Security

The seeded credentials and local database settings are development defaults.
Change all passwords, database credentials, and application secrets before
deploying JourneyHub to a shared or production environment.
