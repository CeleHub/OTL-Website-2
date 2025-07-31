# OTL Website Image Upload System

A secure, user-friendly image upload system for non-technical users to manage website images.

## Features

- 🔐 **Secure Authentication** - Password-protected admin access
- 📤 **Drag & Drop Upload** - Easy image upload with drag and drop
- 🖼️ **Image Management** - View, organize, and delete uploaded images
- 🛡️ **Security Features** - File validation, size limits, and type checking
- 📱 **Responsive Design** - Works on desktop and mobile devices
- 🎨 **Blue Theme** - Matches your website's color scheme

## Setup Instructions

### 1. Database Setup

First, ensure you have MySQL/MariaDB installed and running.

Run the setup script to create the database and tables:

```bash
php admin/setup.php
```

This will:
- Create the `otl_website` database
- Create the `users` and `images` tables
- Create a default admin user
- Create the uploads directory

### 2. Default Credentials

After setup, you can login with:
- **Username:** `admin`
- **Password:** `admin123`

**⚠️ Important:** Change the default password after first login!

### 3. File Permissions

Ensure the uploads directory is writable:

```bash
chmod 755 uploads/
```

### 4. Database Configuration

If you need to change database settings, edit these files:
- `admin/login.php`
- `admin/dashboard.php`
- `admin/upload.php`

Update the database connection details:
```php
$host = 'localhost';
$dbname = 'otl_website';
$username = 'root';
$password = '';
```

## Usage

### Accessing the Admin Panel

1. Navigate to: `yourdomain.com/admin/login.php`
2. Login with your credentials
3. You'll be redirected to the dashboard

### Uploading Images

1. **Drag & Drop Method:**
   - Drag images directly onto the upload area
   - Add description and category when prompted

2. **Click to Select:**
   - Click "Choose Files" button
   - Select multiple images
   - Add descriptions and categories

### Managing Images

- **View:** Click the "View" button to see full-size images
- **Delete:** Click "Delete" to remove images (with confirmation)
- **Organize:** Images are automatically categorized and dated

### Security Features

- **File Validation:** Only JPG, PNG, and GIF files allowed
- **Size Limits:** Maximum 5MB per file
- **MIME Type Checking:** Prevents malicious file uploads
- **Unique Filenames:** Prevents file conflicts
- **Session Protection:** Secure authentication system

## File Structure

```
admin/
├── login.php          # Admin login page
├── dashboard.php      # Main admin dashboard
├── upload.php         # Image upload handler
├── setup.php          # Database setup script
└── README.md          # This file

uploads/               # Uploaded images directory
```

## Customization

### Changing Upload Limits

Edit `admin/upload.php`:
```php
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
```

### Adding New Users

You can add new admin users by inserting into the database:
```sql
INSERT INTO users (username, password) VALUES ('newuser', 'hashed_password');
```

### Styling

The admin panel uses your existing CSS files:
- `../css/bootstrap.min.css`
- `../css/main.css`
- `../css/font-awesome.min.css`

## Troubleshooting

### Common Issues

1. **"Connection failed" error:**
   - Check database credentials
   - Ensure MySQL is running
   - Verify database exists

2. **"Upload failed" error:**
   - Check file permissions on uploads directory
   - Verify file size is under 5MB
   - Ensure file is a valid image

3. **"Unauthorized" error:**
   - Make sure you're logged in
   - Check session configuration
   - Clear browser cookies if needed

### Security Notes

- Change default admin password immediately
- Use HTTPS in production
- Regularly backup the database
- Monitor uploads directory for disk space
- Consider implementing rate limiting for uploads

## Support

For technical support or customization requests, contact your web developer.

---

**Version:** 1.0  
**Last Updated:** <?php echo date('Y-m-d'); ?> 