# School Management System - Setup Guide

This guide provides step-by-step instructions for setting up the School Management System on XAMPP.

## 📋 Prerequisites

Before you begin, ensure you have the following:

- **XAMPP** (latest version recommended)
- **PHP 8.0+** (included with XAMPP)
- **MySQL 5.7+** (included with XAMPP)
- **Web browser** (Chrome, Firefox, Safari, or Edge)
- **Text editor** (VS Code, Sublime Text, or similar)

## 🚀 Quick Setup (5 Minutes)

### Step 1: Extract Files
1. Extract the `School management` folder to your XAMPP htdocs directory:
   ```
   C:\xampp\htdocs\School management\
   ```

### Step 2: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** service
3. Start **MySQL** service

### Step 3: Create Database
1. Open your web browser
2. Go to `http://localhost/phpmyadmin`
3. Click **New** in the left sidebar
4. Enter database name: `school_management`
5. Click **Create**

### Step 4: Import Database Schema
1. Select the `school_management` database
2. Click **Import** tab
3. Choose file: `C:\xampp\htdocs\School management\database\schema.sql`
4. Click **Go**

### Step 5: Import Sample Data (Optional)
1. Still in the `school_management` database
2. Click **Import** tab
3. Choose file: `C:\xampp\htdocs\School management\database\seed.sql`
4. Click **Go**

### Step 6: Access the Application
Open your browser and navigate to:
```
http://localhost/School management/
```

### Step 7: Login
Use the default credentials:
- **Username**: `admin`
- **Password**: `password123`

🎉 **Setup Complete!**

---

## 🔧 Detailed Setup Instructions

### 1. XAMPP Installation

If you don't have XAMPP installed:

1. **Download XAMPP** from https://www.apachefriends.org
2. **Run installer** with administrator privileges
3. **Select components**: Apache, MySQL, PHP, phpMyAdmin
4. **Install** to default location (C:\xampp)
5. **Start** XAMPP Control Panel

### 2. Database Setup

#### Using phpMyAdmin (Recommended)
1. **Start MySQL** in XAMPP Control Panel
2. **Open browser** and go to `http://localhost/phpmyadmin`
3. **Create database**:
   - Click **Databases** tab
   - Enter `school_management` as database name
   - Select `utf8mb4_unicode_ci` collation
   - Click **Create**

#### Using MySQL Command Line
```sql
mysql -u root -p
CREATE DATABASE school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

#### Import Database Files
1. **Import schema**:
   - Select `school_management` database
   - Click **Import** tab
   - Browse to `C:\xampp\htdocs\School management\database\schema.sql`
   - Click **Go**

2. **Import sample data** (optional):
   - Click **Import** tab again
   - Browse to `C:\xampp\htdocs\School management\database\seed.sql`
   - Click **Go**

### 3. Configuration

#### Database Configuration
Edit `C:\xampp\htdocs\School management\config\database.php`:

```php
private $host = 'localhost';
private $db_name = 'school_management';
private $username = 'root';
private $password = ''; // Your MySQL password if set
```

#### Application Configuration
Edit `C:\xampp\htdocs\School management\config\config.php`:

```php
define('APP_ENV', 'development'); // Change to 'production' for live server
define('ENCRYPTION_KEY', 'your-secret-key-here-change-in-production');
```

### 4. Web Server Configuration

#### Apache Configuration
The system includes a pre-configured `.htaccess` file in the public directory. Ensure:

1. **mod_rewrite** is enabled in Apache
2. **AllowOverride** is set to `All` in Apache config
3. **DocumentRoot** points to the public directory

#### Virtual Host Setup (Optional)
Edit `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```apache
<VirtualHost *:80>
    DocumentRoot "C:/xampp/htdocs/School management/public"
    ServerName school.local
    <Directory "C:/xampp/htdocs/School management/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Add to your hosts file (`C:\Windows\System32\drivers\etc\hosts`):
```
127.0.0.1 school.local
```

### 5. File Permissions

Ensure the following directories are writable:

```bash
# In XAMPP shell or command prompt
chmod 755 "C:/xampp/htdocs/School management/logs"
chmod 755 "C:/xampp/htdocs/School management/public/assets/images"
```

### 6. Testing the Installation

#### Basic Tests
1. **Database Connection**: Visit `http://localhost/School management/`
2. **Login Page**: Should load without errors
3. **Admin Login**: Use `admin` / `password123`
4. **Dashboard**: Should display with statistics

#### Functionality Tests
1. **Create Student**: Try adding a new student
2. **Mark Attendance**: Test attendance marking
3. **View Reports**: Check report generation
4. **API Endpoints**: Test with browser dev tools

---

## 🔑 Default Login Credentials

| Role | Username | Password | First Login Action |
|------|----------|----------|-------------------|
| Admin | admin | password123 | Change password immediately |
| Teacher | teacher1 | password123 | Update profile information |
| Student | student1 | password123 | View personal information |

**⚠️ Important**: Change all default passwords after first login!

---

## 🛠️ Troubleshooting

### Common Issues and Solutions

#### 1. Database Connection Error
**Error**: `Database connection failed`

**Solutions**:
- Check MySQL is running in XAMPP
- Verify database name in config
- Check MySQL username/password
- Ensure database exists

#### 2. 404 Not Found Error
**Error**: Page not found

**Solutions**:
- Ensure Apache is running
- Check .htaccess file exists in public folder
- Verify mod_rewrite is enabled
- Check Apache error logs

#### 3. Permission Denied Error
**Error**: Access denied or permission issues

**Solutions**:
- Check file permissions
- Ensure logs directory is writable
- Verify Apache user permissions

#### 4. Session Issues
**Error**: Session timeout or login issues

**Solutions**:
- Check session.save_path in php.ini
- Verify session cookie settings
- Clear browser cookies and cache

#### 5. File Upload Issues
**Error**: File upload not working

**Solutions**:
- Check upload_max_filesize in php.ini
- Verify post_max_size in php.ini
- Ensure temporary directory is writable
- Check file permissions

### Debug Mode

To enable debug mode, edit `config/config.php`:

```php
define('APP_ENV', 'development');
```

This will:
- Display detailed error messages
- Show SQL queries
- Enable error logging

### Error Logs

Check these locations for error logs:

1. **Application Logs**: `C:\xampp\htdocs\School management\logs\error.log`
2. **Apache Logs**: `C:\xampp\apache\logs\error.log`
3. **PHP Logs**: `C:\xampp\php\logs\php_error_log`

### Performance Issues

If the system is slow:

1. **Check MySQL performance**:
   - Run `EXPLAIN` on slow queries
   - Add indexes to frequently queried columns

2. **Optimize PHP**:
   - Enable OPcache
   - Increase memory limit

3. **Check server resources**:
   - Monitor CPU and memory usage
   - Check disk space

---

## 🔄 Updates and Maintenance

### Regular Maintenance Tasks

#### Daily
- **Backup database**
- **Check error logs**
- **Monitor system performance**

#### Weekly
- **Update security patches**
- **Clean old log files**
- **Review user accounts**

#### Monthly
- **Full system backup**
- **Security audit**
- **Performance review**

### Updating the System

1. **Backup Current System**
   - Export database
   - Backup configuration files
   - Save custom modifications

2. **Update Files**
   - Replace application files
   - Update database schema if needed
   - Clear caches

3. **Test Updates**
   - Test all major functions
   - Verify API endpoints
   - Check mobile compatibility

4. **Monitor**
   - Watch error logs
   - Check performance metrics
   - Verify user access

---

## 📱 Mobile Setup

### Mobile Browser Access
The system works on mobile browsers:

1. **Open browser** on mobile device
2. **Navigate to** `http://[your-server-ip]/School management/`
3. **Login** with credentials
4. **Use responsive interface**

### Mobile App Development
For native mobile app development:

1. **Use the REST API** endpoints
2. **Implement authentication** tokens
3. **Follow API documentation**
4. **Test on mobile devices**

---

## 🔒 Security Configuration

### Production Security

When moving to production:

1. **Change all passwords**
2. **Set APP_ENV to 'production'**
3. **Update encryption key**
4. **Enable HTTPS**
5. **Configure firewall**
6. **Set up backups**
7. **Monitor security logs**

### SSL/HTTPS Setup

1. **Obtain SSL certificate** (Let's Encrypt recommended)
2. **Configure Apache** for HTTPS
3. **Update .htaccess** for SSL redirect
4. **Test HTTPS access**

### Security Headers

The system includes security headers in `.htaccess`:

- **X-Frame-Options**: DENY
- **X-Content-Type-Options**: nosniff
- **X-XSS-Protection**: 1; mode=block
- **Strict-Transport-Security**: max-age=31536000
- **Content-Security-Policy**: Default-src self

---

## 📊 Performance Optimization

### Database Optimization

1. **Add indexes** to frequently queried columns
2. **Optimize queries** with EXPLAIN
3. **Use prepared statements**
4. **Enable query cache**

### Frontend Optimization

1. **Minify CSS/JS** files
2. **Enable browser caching**
3. **Compress images**
4. **Use CDN** for static assets

### Server Optimization

1. **Enable OPcache** in PHP
2. **Configure Apache caching**
3. **Use Gzip compression**
4. **Optimize server settings**

---

## 🤝 Support Resources

### Documentation
- **README.md**: Complete system overview
- **Code comments**: Inline documentation
- **API documentation**: Endpoint descriptions

### Community Support
- **GitHub Issues**: Report bugs and request features
- **Wiki**: Additional documentation
- **Discussions**: Community support

### Professional Support
- **Email**: support@schoolmanagement.com
- **Phone**: +1-234-567-8900
- **Chat**: Available on website

---

## 🎓 Training Resources

### For Administrators
- **User manual**: Complete feature guide
- **Video tutorials**: Step-by-step instructions
- **Best practices**: Security and performance

### For Teachers
- **Quick start guide**: Basic operations
- **Feature tutorials**: Specific functionality
- **FAQ**: Common questions

### For Students
- **Student guide**: Portal usage
- **Mobile guide**: Access on phones
- **Help desk**: Support contact

---

## ✅ Setup Checklist

Before going live, ensure:

- [ ] XAMPP services are running
- [ ] Database is created and imported
- [ ] Configuration files are updated
- [ ] Default passwords are changed
- [ ] File permissions are set
- [ ] SSL certificate is installed
- [ ] Security headers are configured
- [ ] Backup system is set up
- [ ] Monitoring is configured
- [ ] Documentation is reviewed

---

## 🚀 Going Live

### Pre-Launch Checklist

1. **Final testing** of all features
2. **Performance testing** under load
3. **Security audit** and penetration testing
4. **Backup verification**
5. **User training** completion
6. **Support documentation** preparation

### Launch Day

1. **Switch to production mode**
2. **Enable HTTPS**
3. **Monitor system performance**
4. **Check error logs**
5. **User support readiness**

### Post-Launch

1. **Monitor user feedback**
2. **Track system performance**
3. **Regular security updates**
4. **Continuous improvement**
5. **User training updates**

---

**Congratulations! 🎉 Your School Management System is now ready for use!**

For additional support, refer to the documentation or contact our support team.
