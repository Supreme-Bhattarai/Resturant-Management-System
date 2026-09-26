# Role-Based Login System - 4 TO 9 Restaurant

## Overview
This document explains the unified role-based login system implemented for the 4 TO 9 Restaurant website. The system uses a single login form for both Admin and Customer users, with automatic role-based redirection.

## Database Structure

### Users Table
```sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Default Admin Account
```sql
INSERT INTO users (name, email, phone, password, role, status) 
VALUES ('Admin User', 'admin@4to9.com', '9876543210', '$2y$10$aXb1v83WMqEKU/30QfUd9OQ6PLFproDDP1AkQpxXe1XsKBhJnWewG', 'admin', 'active');
-- Default password: admin123
```

## File Structure

```
E:\Resturant\Restaurantphp/
├── login.php                    # Unified login page for all users
├── logout.php                   # Customer logout page
├── register.php                 # Customer registration page
├── includes/
│   ├── auth.php                 # Customer authentication check
│   ├── functions.php           # Helper functions
│   └── header.php              # Page header
├── admin/
│   ├── login.php               # Admin login page (redirects to main login)
│   ├── logout.php              # Admin logout page
│   ├── includes/
│   │   ├── auth.php           # Admin authentication check
│   │   ├── header.php        # Admin page header
│   │   └── sidebar.php       # Admin sidebar
│   ├── dashboard.php          # Admin dashboard
│   ├── bookings.php           # Booking management
│   ├── tables.php             # Table management
│   ├── menu-items.php         # Menu item management
│   ├── categories.php         # Category management
│   ├── customers.php          # Customer management
│   ├── gallery.php            # Gallery management
│   ├── settings.php           # Settings management
│   └── backup.php             # Database backup
├── index.php                   # Customer homepage
├── menu.php                    # Customer menu page
├── about.php                   # Customer about page
├── gallery.php                 # Customer gallery page
├── contact.php                 # Customer contact page
├── tables.php                  # Table selection page
├── reservation.php             # Reservation form
├── profile.php                 # Customer profile
├── my-bookings.php             # Customer booking history
└── config/
    ├── config.php              # Site configuration
    └── database.php            # Database connection
```

## Authentication Flow

### Login Process
```
User enters credentials → login.php
    ↓
Validate email and password
    ↓
Check user status (active/inactive)
    ↓
Verify password hash
    ↓
Set session variables:
    - user_id
    - user_name
    - user_email
    - user_role
    ↓
Check user role:
    - If 'admin' → Redirect to admin/dashboard.php
    - If 'customer' → Redirect to index.php or booking page
```

### Role-Based Protection

#### Customer Pages Protection
- **File**: `includes/auth.php`
- **Protected Pages**: All customer pages (profile.php, my-bookings.php, etc.)
- **Logic**:
  - Check if user is logged in
  - Check if user role is 'customer'
  - If not logged in → redirect to login.php
  - If admin → redirect to admin/dashboard.php

#### Admin Pages Protection
- **File**: `admin/includes/auth.php`
- **Protected Pages**: All admin pages (dashboard.php, bookings.php, etc.)
- **Logic**:
  - Check if user is logged in
  - Check if user role is 'admin'
  - If not logged in → redirect to login.php
  - If customer → redirect to index.php

## Session Variables

### Set During Login
```php
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
```

### Optional Session Variables
```php
$_SESSION['redirect_after_login'] = $url;  // For redirecting after login
$_SESSION['booking_data'] = $data;        // For preserving booking data
```

## Page Protection Implementation

### Customer Pages
Add this at the top of every customer page that requires login:
```php
<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';  // This protects the page
require_once 'includes/header.php';
?>
```

### Admin Pages
Add this at the top of every admin page:
```php
<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once 'includes/auth.php';  // This protects the page
require_once 'includes/header.php';
?>
```

## Logout Functionality

### Customer Logout
- **File**: `logout.php`
- **Process**:
  1. Destroy all session data
  2. Delete session cookie
  3. Destroy session
  4. Redirect to login.php

### Admin Logout
- **File**: `admin/logout.php`
- **Process**: Same as customer logout
- **Redirect**: login.php (same login page)

## User Roles and Permissions

### Admin Role
- **Access**: Admin dashboard and all admin functions
- **Pages**: admin/dashboard.php, admin/bookings.php, admin/tables.php, etc.
- **Functions**: 
  - Manage bookings
  - Manage tables
  - Manage menu items
  - Manage categories
  - Manage customers
  - Manage gallery
  - Settings management
  - Database backup

### Customer Role
- **Access**: Customer website and customer functions
- **Pages**: index.php, menu.php, tables.php, profile.php, my-bookings.php, etc.
- **Functions**:
  - View menu
  - Book tables
  - View booking history
  - Manage profile
  - Cancel bookings

## Security Features

1. **Password Hashing**: Uses `password_hash()` and `password_verify()`
2. **Session Security**: Session regeneration after login
3. **Role-Based Access**: Strict separation of admin and customer areas
4. **URL Protection**: Cannot access admin pages by manually entering URL
5. **Session Timeout**: Sessions are destroyed on logout
6. **Cookie Security**: Session cookies are deleted on logout

## Error Messages

### Login Errors
- "Please fill in all fields." - Empty form submission
- "Invalid email or password. Please try again." - Wrong credentials
- "Please login to complete your table reservation." - Redirected from booking

### Access Errors
- Automatic redirect without message (security best practice)
- Users are redirected to appropriate login/dashboard

## Testing the System

### Test Admin Login
1. Go to: `http://localhost:8000/login.php`
2. Email: `admin@4to9.com`
3. Password: `admin123`
4. Should redirect to: `admin/dashboard.php`

### Test Customer Login
1. Register a new account at: `register.php`
2. Login at: `login.php`
3. Should redirect to: `index.php`

### Test URL Protection
1. Login as customer
2. Try to access: `http://localhost:8000/admin/dashboard.php`
3. Should redirect to: `index.php`

4. Login as admin
5. Try to access: `http://localhost:8000/profile.php`
6. Should redirect to: `admin/dashboard.php`

## Code Examples

### Check User Role (Anywhere in code)
```php
if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin') {
        // User is admin
    } else {
        // User is customer
    }
}
```

### Check if Logged In
```php
if (isset($_SESSION['user_id'])) {
    // User is logged in
} else {
    // User is not logged in
}
```

### Get Current User Info
```php
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];
```

## Helper Functions

### Redirect Function (in functions.php)
```php
function redirect($url) {
    header('Location: ' . SITE_URL . '/' . $url);
    exit();
}
```

### isLoggedIn Function (in functions.php)
```php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
```

## Important Notes

1. **Single Login Page**: Both admin and customer use the same login.php
2. **Automatic Role Detection**: System automatically detects role and redirects appropriately
3. **No Manual Role Selection**: Users don't select their role - it's determined by their account
4. **Secure Session Management**: Sessions are properly destroyed on logout
5. **Database-Driven Roles**: User roles are stored in the database
6. **Default Customer Role**: New registrations default to 'customer' role
7. **Admin Role Assignment**: Admin role must be manually assigned in database

## Maintenance

### Adding New Admin Users
```sql
INSERT INTO users (name, email, phone, password, role, status) 
VALUES ('New Admin', 'newadmin@4to9.com', '9876543210', '$2y$10$hashedpassword', 'admin', 'active');
```

### Changing User Role
```sql
UPDATE users SET role = 'admin' WHERE email = 'user@example.com';
```

### Deactivating User
```sql
UPDATE users SET status = 'inactive' WHERE id = 1;
```

## Summary

This role-based login system provides:
- ✅ Single unified login page for all users
- ✅ Automatic role-based redirection
- ✅ Secure session management
- ✅ Protected admin and customer areas
- ✅ URL access protection
- ✅ Clean, beginner-friendly code
- ✅ Well-organized file structure
- ✅ Comprehensive error handling
- ✅ Secure password handling
- ✅ Role-based feature access

The system is designed to be simple to understand, easy to maintain, and secure to use.