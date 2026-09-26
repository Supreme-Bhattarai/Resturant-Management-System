# 4 TO 9 Restaurant Website

A complete restaurant management system built with PHP, MySQL, HTML5, CSS3, Bootstrap 5, and JavaScript. This system includes customer-facing features for viewing menus, making reservations, and managing bookings, along with a comprehensive admin dashboard for restaurant management.

## Features

### Customer Features
- **Responsive Website**: Modern, elegant design with mobile-first approach
- **Menu Browsing**: Search dishes or open a book-style catalog with live categories and prices
- **Table Reservation**: Online booking system with availability checking
- **User Accounts**: Customer registration, login, and profile management
- **Booking Management**: View booking history, check status, cancel bookings
- **Restaurant Information**: About us, gallery, contact pages

### Admin Features
- **Dashboard**: Real-time statistics and overview of restaurant operations
- **Booking Management**: View, confirm, reject, cancel, and manage reservations
- **Table Management**: Add, edit, delete, and manage restaurant tables
- **Menu Management**: Add catalog categories and dishes, update prices and availability, and upload local dish photos
- **Customer Management**: View customer details and manage accounts
- **Gallery Management**: Upload and manage restaurant gallery images
- **Restaurant Settings**: Configure restaurant information and booking rules
- **Database Backup**: Create, download, and manage database backups
- **Security**: Role-based access control and secure authentication

## Technology Stack

- **Backend**: PHP (vanilla PHP, no frameworks)
- **Database**: MySQL with PDO
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5
- **Fonts**: Google Fonts (Playfair Display, Poppins)
- **Icons**: Bootstrap Icons

## Project Structure

```
4to9/
├── index.php                 # Home page
├── menu.php                  # Menu page
├── about.php                 # About us page
├── gallery.php               # Gallery page
├── contact.php               # Contact page
├── reservation.php           # Table reservation
├── login.php                 # Customer login
├── register.php              # Customer registration
├── logout.php                # Logout
├── profile.php               # Customer profile
├── my-bookings.php           # Customer booking history
├── booking-details.php       # Individual booking details
├── admin/
│   ├── login.php             # Admin login
│   ├── dashboard.php         # Admin dashboard
│   ├── bookings.php          # Booking management
│   ├── booking-details.php   # Booking details and actions
│   ├── tables.php            # Table management
│   ├── categories.php         # Menu category management
│   ├── menu-items.php        # Menu item management
│   ├── customers.php         # Customer management
│   ├── gallery.php           # Gallery management
│   ├── settings.php          # Restaurant settings
│   ├── backup.php            # Database backup
│   ├── profile.php           # Admin profile
│   ├── logout.php            # Admin logout
│   └── includes/
│       ├── header.php        # Admin header
│       ├── sidebar.php       # Admin sidebar
│       ├── navbar.php        # Admin navbar
│       ├── footer.php        # Admin footer
│       └── auth.php          # Admin authentication check
├── config/
│   ├── database.php          # Database configuration
│   └── config.php            # General configuration
├── includes/
│   ├── header.php            # Customer header
│   ├── footer.php            # Customer footer
│   ├── navbar.php             # Customer navigation
│   ├── auth.php              # Customer authentication check
│   └── functions.php         # Common functions
├── assets/
│   ├── css/
│   │   ├── style.css         # Customer styles
│   │   └── admin.css         # Admin styles
│   ├── js/
│   │   ├── script.js         # Customer JavaScript
│   │   └── admin.js          # Admin JavaScript
│   └── images/               # Image assets
├── uploads/
│   ├── menu/                 # Menu item images
│   ├── gallery/              # Gallery images
│   └── categories/           # Category images
├── backups/                  # Database backups (protected)
└── database/
    └── restaurant_4to9.sql   # Database schema
```

## Installation Instructions

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2 or higher
- Web server (Apache, Nginx, or PHP built-in server)
- Modern web browser

### Step 1: Download/Clone the Project

Download or clone this project to your local web server directory:

```bash
# If using git
git clone <repository-url> Restaurantphp
cd Restaurantphp
```

Or extract the zip file to your web server directory (e.g., `htdocs`, `www`, or `public_html`).

### Step 2: Configure Database

1. Open `config/database.php` and update the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
define('DB_NAME', 'restaurant_4to9');
```

2. Create the MySQL database:

```sql
CREATE DATABASE restaurant_4to9;
```

3. Import the database schema:

- Using phpMyAdmin: Import `database/restaurant_4to9.sql`
- Using command line:
```bash
mysql -u root -p restaurant_4to9 < database/restaurant_4to9.sql
```

### Step 3: Configure Site Settings

1. Open `config/config.php` and update the site URL:

```php
define('SITE_URL', 'http://localhost/Restaurantphp');
```

2. Adjust the timezone if needed:

```php
date_default_timezone_set('Asia/Kolkata');
```

### Step 4: Set Directory Permissions

Ensure the following directories have write permissions:

```bash
# On Linux/Mac
chmod 755 uploads/menu
chmod 755 uploads/gallery
chmod 755 uploads/categories
chmod 755 backups

# On Windows, ensure IIS/IUSR has write permissions to these folders
```

### Step 5: Start the Web Server

#### Using XAMPP/WAMP/MAMP:

1. Place the project in your web server's document root
2. Start Apache and MySQL services
3. Access the site: `http://localhost/Restaurantphp`

#### Using PHP Built-in Server:

```bash
php -S localhost:8000
```

Then access: `http://localhost:8000`

### Step 6: Access Admin Panel

1. Navigate to `http://localhost/Restaurantphp/admin/login.php`
2. Login with default admin credentials:
   - **Email**: `admin@4to9.com`
   - **Password**: `admin123`

⚠️ **Important**: Change the default admin password immediately after first login!

## Default Admin Account

The system comes with a pre-configured admin account:

- **Email**: admin@4to9.com
- **Password**: admin123
- **Role**: admin

### Changing Admin Password

1. Login to admin panel
2. Go to Profile page
3. Use the "Change Password" section

### Resetting Admin Password

If you need to reset the admin password, run this SQL query:

```sql
UPDATE users SET password = '$2y$10$aXb1v83WMqEKU/30QfUd9OQ6PLFproDDP1AkQpxXe1XsKBhJnWewG' WHERE email = 'admin@4to9.com';
```

This will reset the password to `admin123`.

## Configuration

### Database Configuration

Edit `config/database.php`:

```php
define('DB_HOST', 'localhost');        // Database host
define('DB_USER', 'root');            // Database username
define('DB_PASS', '');                // Database password
define('DB_NAME', 'restaurant_4to9');  // Database name
```

### Site Configuration

Edit `config/config.php`:

```php
define('SITE_NAME', '4 TO 9');                    // Restaurant name
define('SITE_URL', 'http://localhost/Restaurantphp'); // Site URL
define('ADMIN_URL', SITE_URL . '/admin');          // Admin URL
```

### Restaurant Settings

Configure restaurant-specific settings via the admin panel at `/admin/settings.php`:

- Restaurant name, email, phone, address
- Opening and closing times
- Booking duration (default: 90 minutes)
- Maximum guests per booking
- Online booking availability

## Usage Guide

### For Customers

1. **Browse the Website**: Visit the home page to explore the restaurant
2. **View Menu**: Open the menu catalog to turn through categories, dishes, and current prices; use the kids section for smaller portions
3. **Register Account**: Create an account to make reservations
4. **Book a Table**: Select date, time, and number of guests
5. **Manage Bookings**: View booking history and status in "My Bookings"
6. **Update Profile**: Manage personal information and password

### For Admins

1. **Login**: Access the admin panel at `/admin/login.php`
2. **Dashboard**: View statistics and recent bookings
3. **Manage Bookings**: Review, confirm, reject, or cancel reservations
4. **Manage Tables**: Add and configure restaurant tables
5. **Manage Menu**: Open `/admin/menu-catalog.php` to add categories and dishes or change prices and availability. Use the linked category and dish editors for full details and local photos.
6. **Manage Customers**: View customer information and booking history
7. **Manage Gallery**: Upload restaurant images
8. **Configure Settings**: Update restaurant information and policies
9. **Backup Database**: Create and download database backups

The guest catalog reads active categories and available dishes directly from MySQL. Changes saved in the admin catalog appear on the next page load. Supplied and sourced site photos live under `assets/images/`; admin-uploaded dish photos live under `uploads/menu/`. Photo credits are in `assets/images/photos/SOURCES.md`.

## Security Features

- **Password Hashing**: All passwords are hashed using `password_hash()`
- **SQL Injection Protection**: Prepared statements with PDO
- **XSS Protection**: Input sanitization and output escaping
- **Session Security**: Secure session configuration and regeneration
- **Role-Based Access Control**: Separate customer and admin areas
- **CSRF Protection**: Form validation and secure handling
- **File Upload Validation**: Secure file upload handling
- **Directory Protection**: Sensitive directories are protected

## Booking Workflow

1. Customer browses restaurant website
2. Customer views menu and decides to book a table
3. Customer logs in or registers
4. Customer selects date, time, and number of guests
5. System checks table availability
6. Customer submits booking request
7. Booking status = "Pending"
8. Admin reviews booking in dashboard
9. Admin confirms/rejects booking and assigns table
10. Customer sees updated booking status
11. After the visit, admin marks booking as "Completed"

## Troubleshooting

### Database Connection Issues

If you see "Connection failed" errors:

1. Verify MySQL is running
2. Check database credentials in `config/database.php`
3. Ensure the database `restaurant_4to9` exists
4. Check MySQL user permissions

### File Upload Issues

If image uploads fail:

1. Check directory permissions for `uploads/` folders
2. Verify PHP `upload_max_filesize` and `post_max_size` settings
3. Ensure sufficient disk space

### Session Issues

If users are logged out unexpectedly:

1. Check PHP session save path permissions
2. Verify session configuration in `config/config.php`
3. Check browser cookie settings

### Admin Access Issues

If you cannot access admin pages:

1. Verify you're logged in as admin role
2. Check session variables are set correctly
3. Ensure `admin/includes/auth.php` is working

## Development Notes

### Code Style

- Clean, readable PHP code
- Proper error handling
- Input validation and sanitization
- Security best practices
- Beginner-friendly structure

### Adding New Features

1. Follow existing code patterns
2. Use prepared statements for database queries
3. Include proper authentication checks
4. Test thoroughly before deployment
5. Update documentation as needed

## Browser Compatibility

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License

This project is provided as-is for educational and commercial use.

## Support

For issues, questions, or contributions, please refer to the project documentation or contact the development team.

## Credits

- **Design**: Built with Bootstrap 5 and custom CSS
- **Icons**: Bootstrap Icons
- **Fonts**: Google Fonts (Playfair Display, Poppins)
- **Images**: Unsplash (placeholder images)

---

**Note**: This is a complete, functional restaurant management system. Ensure you test thoroughly in a development environment before deploying to production.
