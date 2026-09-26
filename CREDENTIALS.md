# 4 TO 9 Restaurant - Login Credentials

## Default Admin Account
- **Email**: admin@4to9.com
- **Password**: admin123

- **Role**: Administrator
- **Login URL**: http://localhost:8000/admin/login.php

## How to Create Customer Accounts

### Option 1: Register via Website
1. Go to http://localhost:8000/register.php
2. Fill in the registration form:
   - Full Name
   - Email
   - Phone Number (10 digits)
   - Password (minimum 6 characters)
   - Confirm Password
3. Click "Register"
4. Login with your new credentials at http://localhost:8000/login.php

### Option 2: Create via Database
If you need to create customer accounts directly in the database, you can use this SQL:

```sql
INSERT INTO users (name, email, phone, password, role, status) 
VALUES ('John Doe', 'john@example.com', '9876543210', '$2y$10$YourHashedPasswordHere', 'customer', 'active');
```

**Note**: The password must be hashed using PHP's `password_hash()` function.

## Password Reset

### Reset Admin Password
If you need to reset the admin password, run this SQL query:

```sql
UPDATE users SET password = '$2y$10$aXb1v83WMqEKU/30QfUd9OQ6PLFproDDP1AkQpxXe1XsKBhJnWewG' WHERE email = 'admin@4to9.com';
```

This will reset the password back to: `admin123`

### Reset Customer Password
Customers can reset their password through the profile page once logged in, or you can reset it via the database using the same method.

## Security Notes

⚠️ **Important Security Recommendations:**

1. **Change the default admin password immediately** after first login
2. **Use strong passwords** (minimum 8 characters, mix of letters, numbers, symbols)
3. **Enable HTTPS** in production environments
4. **Regularly update passwords**
5. **Limit admin access** to trusted personnel only

## Testing Accounts

For testing purposes, you can register these sample accounts:

### Test Customer 1
- Email: customer1@test.com
- Password: test1234
- Phone: 9876543210

### Test Customer 2  
- Email: customer2@test.com
- Password: test1234
- Phone: 9876543211

## Access Levels

### Customer Access
- View menu and restaurant information
- Register and login
- Make table reservations
- View booking history
- Update profile

### Admin Access
- All customer features
- Manage bookings (confirm, reject, cancel)
- Manage restaurant tables
- Manage menu categories and items
- Manage customer accounts
- Manage gallery
- Configure restaurant settings
- Create database backups
- Full system control

## Troubleshooting

### Cannot Login
1. Check email and password are correct
2. Ensure account status is 'active' in database
3. Clear browser cookies and try again
4. Check if database connection is working

### Account Locked
If an account is set to 'inactive' status in the database, the user cannot login. Update the status to 'active':

```sql
UPDATE users SET status = 'active' WHERE email = 'user@example.com';
```