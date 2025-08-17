# Database Setup Instructions for NAZZY's Thrift Shop

## Quick Fix for Database Connection Error

The error you're seeing occurs because the database `nazzys_thrift_shop` doesn't exist yet. Here's how to fix it:

### Option 1: Using phpMyAdmin (Recommended)
1. Open your web browser and go to `http://localhost/phpmyadmin`
2. Click on "SQL" tab at the top
3. Copy and paste the entire content from `database_setup.sql` file
4. Click "Go" to execute the script

### Option 2: Using MySQL Command Line
1. Open Command Prompt as Administrator
2. Navigate to MySQL bin directory: `cd C:\xampp\mysql\bin`
3. Connect to MySQL: `mysql -u root -p` (press Enter if no password)
4. Run: `source C:\xampp\htdocs\my web app\database_setup.sql`

### Option 3: Manual Database Creation
If the above doesn't work, create the database manually:
1. In phpMyAdmin, click "New" on the left sidebar
2. Enter database name: `nazzys_thrift_shop`
3. Click "Create"
4. Then run the SQL script from `database_setup.sql`

## Default Login Credentials
After setup, you can login with:
- **Username:** admin
- **Password:** admin123

## Verify Setup
1. Go to `http://localhost/my web app/login.php`
2. Try logging in with the admin credentials
3. If successful, the database is properly configured

## Troubleshooting
- Make sure XAMPP MySQL service is running
- Check that the database name matches exactly: `nazzys_thrift_shop`
- Verify MySQL is running on port 3306 (default)

## Database Structure Created
- ✅ Users table (for authentication)
- ✅ Inventory table (for items)
- ✅ Customers table (for customer management)
- ✅ Sales table (for transactions)
- ✅ Sale items table (for transaction details)
- ✅ Staff schedule table (for scheduling)
