# School Management System

A comprehensive, production-ready School Management System built with PHP 8+, MySQL, Bootstrap 5, and vanilla JavaScript. This system provides complete functionality for managing students, teachers, classes, attendance, results, and more.

## 🚀 Features

### Core Features
- **Role-based Authentication** (Admin, Teacher, Student)
- **Secure Login/Logout** with session management
- **Password Hashing** and security features
- **Responsive Design** with Bootstrap 5
- **Dark Mode** support
- **Real-time Notifications**
- **AJAX-powered CRUD operations**
- **Pagination** and search functionality

### Admin Features
- **Dashboard** with analytics and statistics
- **Student Management** (CRUD operations)
- **Teacher Management** (CRUD operations)
- **Class & Section Management**
- **Subject Management**
- **Attendance Overview**
- **Result Management**
- **Reports** and analytics

### Teacher Features
- **Personal Dashboard**
- **View Assigned Classes**
- **Mark Attendance** for students
- **Upload/Manage Marks**
- **View Student Lists**
- **Generate Reports**

### Student Features
- **Personal Dashboard**
- **View Profile** and update limited fields
- **View Attendance History**
- **View Results and Grades**
- **View Class Schedule**
- **Download Reports**

### Technical Features
- **MVC Architecture** with clean separation
- **RESTful API** with JSON responses
- **Prepared Statements** for security
- **CSRF Protection**
- **XSS Prevention**
- **Rate Limiting**
- **File Upload Security**
- **Database Optimization** with indexes

## 📋 Requirements

### Server Requirements
- **PHP 8.0+** with PDO extension
- **MySQL 5.7+** or MariaDB 10.2+
- **Apache** with mod_rewrite enabled
- **PHP Extensions**: PDO, MySQL, JSON, GD, Fileinfo

### Browser Requirements
- **Chrome 80+**
- **Firefox 75+**
- **Safari 13+**
- **Edge 80+**

## 🛠️ Installation

### Step 1: Database Setup

1. **Create Database**
   ```sql
   CREATE DATABASE school_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import Schema**
   - Open phpMyAdmin
   - Select the `school_management` database
   - Import `database/schema.sql`
   - Import `database/seed.sql` (for sample data)

### Step 2: Configuration

1. **Update Database Credentials**
   Edit `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'school_management';
   private $username = 'root';
   private $password = ''; // Your MySQL password
   ```

2. **Update Application Settings**
   Edit `config/config.php` if needed:
   ```php
   define('APP_ENV', 'development'); // Change to 'production' for live server
   define('ENCRYPTION_KEY', 'your-secret-key-here-change-in-production');
   ```

### Step 3: Web Server Setup

#### Apache Configuration

1. **Point Apache to Public Directory**
   - Document Root: `C:/xampp/htdocs/School management/public`
   - Enable `mod_rewrite`

2. **Virtual Host (Optional)**
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

#### XAMPP Setup

1. **Start Apache and MySQL** in XAMPP Control Panel
2. **Access the Application**: `http://localhost/School management/`

## 🔑 Default Login Credentials

| Role | Username | Password |
|------|----------|----------|
| Admin | admin | password123 |
| Teacher | teacher1 | password123 |
| Student | student1 | password123 |

**Important**: Change these passwords immediately after first login!

## 📁 Project Structure

```
School management/
├── app/                    # Application code
│   ├── controllers/         # MVC Controllers
│   ├── models/             # MVC Models
│   ├── views/               # MVC Views
│   │   ├── admin/           # Admin views
│   │   ├── teacher/         # Teacher views
│   │   ├── student/         # Student views
│   │   ├── auth/            # Authentication views
│   │   ├── layouts/         # Layout templates
│   │   └── components/      # Reusable components
│   └── middleware/          # Middleware classes
├── config/                  # Configuration files
│   ├── database.php         # Database configuration
│   ├── config.php           # Application settings
│   └── constants.php        # Application constants
├── database/                # Database files
│   ├── schema.sql           # Database schema
│   └── seed.sql             # Sample data
├── public/                  # Web root
│   ├── assets/              # Static files
│   │   ├── css/            # Stylesheets
│   │   ├── js/             # JavaScript files
│   │   └── images/         # Images
│   ├── index.php            # Main entry point
│   └── .htaccess            # Apache configuration
├── includes/                # Core classes
│   ├── Database.php         # Database connection
│   ├── Session.php          # Session management
│   ├── Validator.php        # Input validation
│   ├── helpers.php          # Helper functions
│   └── Security.php         # Security utilities
├── api/                     # API endpoints
│   └── routes.php           # API routing
└── logs/                    # Log files
```

## 🔧 Configuration Options

### Database Configuration
Edit `config/database.php` to modify database settings.

### Application Settings
Edit `config/config.php` for:
- Environment (development/production)
- Security settings
- File upload limits
- Session configuration

### Security Settings
- **CSRF Protection**: Automatically enabled
- **XSS Prevention**: Built-in input sanitization
- **Rate Limiting**: Configurable per endpoint
- **File Upload Security**: Type and size validation

## 📊 Database Schema

The system uses 10 core tables:

1. **users** - Authentication and user management
2. **students** - Student profiles and data
3. **teachers** - Teacher profiles and data
4. **classes** - Class information
5. **sections** - Class sections
6. **subjects** - Subject catalog
7. **enrollments** - Student-class relationships
8. **attendance** - Attendance records
9. **exams** - Exam definitions
10. **results** - Student results

## 🔌 API Endpoints

### Authentication
- `POST /api/login` - User login
- `POST /api/logout` - User logout

### Students
- `GET /api/students` - List students
- `POST /api/students` - Create student
- `PUT /api/students/{id}` - Update student
- `DELETE /api/students/{id}` - Delete student

### Teachers
- `GET /api/teachers` - List teachers
- `POST /api/teachers` - Create teacher
- `PUT /api/teachers/{id}` - Update teacher
- `DELETE /api/teachers/{id}` - Delete teacher

### Classes & Subjects
- `GET /api/classes` - List classes
- `POST /api/classes` - Create class
- `GET /api/subjects` - List subjects
- `POST /api/subjects` - Create subject

### Attendance & Results
- `GET /api/attendance` - Get attendance records
- `POST /api/attendance` - Mark attendance
- `GET /api/results` - Get results
- `POST /api/results` - Store results

## 🎨 Frontend Features

### Responsive Design
- **Mobile-first** approach
- **Bootstrap 5** framework
- **Dark Mode** toggle
- **Sidebar navigation** with collapsible menu
- **Data tables** with search and pagination

### JavaScript Features
- **AJAX CRUD** operations
- **Real-time notifications**
- **Form validation**
- **File upload** with progress
- **Infinite scroll** pagination
- **Dark mode** persistence

### UI Components
- **Dashboard cards** with animations
- **Modal dialogs** for forms
- **Toast notifications**
- **Progress bars** and loaders
- **Data tables** with sorting
- **Charts** and analytics

## 🔒 Security Features

### Authentication Security
- **Password hashing** with `password_hash()`
- **Session management** with regeneration
- **Remember me** functionality
- **Rate limiting** on login attempts

### Application Security
- **CSRF tokens** for all forms
- **XSS prevention** with output escaping
- **SQL injection** prevention with prepared statements
- **File upload** security validation
- **Input sanitization** and validation

### Server Security
- **Security headers** via .htaccess
- **Directory protection** for sensitive files
- **Error handling** without information disclosure
- **IP-based rate limiting**

## 🚀 Performance Optimization

### Database Optimization
- **Indexes** on frequently queried columns
- **Prepared statements** for query caching
- **Connection pooling** with persistent connections
- **Query optimization** with proper joins

### Frontend Optimization
- **Asset minification** (CSS/JS)
- **Browser caching** headers
- **Image optimization**
- **Lazy loading** for large datasets
- **AJAX pagination** to reduce server load

### Caching
- **Session caching** for user data
- **File caching** for static assets
- **Database query** result caching

## 📱 Mobile Responsiveness

The system is fully responsive and works on:
- **Desktop browsers** (Chrome, Firefox, Safari, Edge)
- **Tablets** (iPad, Android tablets)
- **Mobile phones** (iPhone, Android phones)

### Mobile Features
- **Touch-friendly** interface
- **Collapsible sidebar** for small screens
- **Optimized forms** for mobile input
- **Swipe gestures** support

## 🔄 Updates and Maintenance

### Regular Maintenance
- **Database backups** (daily/weekly)
- **Log file rotation**
- **Security updates**
- **Performance monitoring**

### Update Process
1. **Backup current system**
2. **Update database schema** if needed
3. **Replace application files**
4. **Run database migrations**
5. **Test functionality**
6. **Clear caches**

## 🐛 Troubleshooting

### Common Issues

#### Database Connection Error
```
Error: Database connection failed
```
**Solution**: Check database credentials in `config/database.php`

#### 404 Not Found
```
Error: Page not found
```
**Solution**: Ensure Apache `mod_rewrite` is enabled and `.htaccess` is working

#### Session Issues
```
Error: Session expired
```
**Solution**: Check session settings in `php.ini` and `config/config.php`

#### File Upload Issues
```
Error: File upload failed
```
**Solution**: Check file permissions and upload limits in `php.ini`

### Debug Mode
Enable debug mode in `config/config.php`:
```php
define('APP_ENV', 'development');
```

### Error Logs
Check error logs in:
- `logs/error.log` (application errors)
- Apache error log (server errors)
- PHP error log (PHP errors)

## 🤝 Contributing

### Development Guidelines
1. **Follow PSR-12** coding standards
2. **Write tests** for new features
3. **Document code** with comments
4. **Use version control** (Git)
5. **Test thoroughly** before deployment

### Code Structure
- **MVC pattern** for organization
- **Separate concerns** (logic, presentation, data)
- **Use dependency injection**
- **Write reusable components**

## 📄 License

This project is licensed under the MIT License. See LICENSE file for details.

## 📞 Support

For support and questions:
- **Email**: support@schoolmanagement.com
- **Documentation**: Check this README and code comments
- **Issues**: Report bugs on GitHub issues

## 🗺️ Roadmap

### Future Enhancements
- **Mobile app** (React Native)
- **Email notifications** system
- **Advanced reporting** with charts
- **Multi-language** support
- **Parent portal** module
- **Library management** system
- **Inventory management**
- **Fee management** system
- **SMS notifications**
- **Video conferencing** integration

### Version History
- **v1.0.0** - Initial release with core features
- **v1.1.0** - Added dark mode and notifications
- **v1.2.0** - Enhanced API and security features
- **v1.3.0** - Mobile optimization and performance improvements

---

**Built with ❤️ for educational institutions worldwide**
