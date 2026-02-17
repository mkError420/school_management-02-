# School Management System - API Documentation

This document provides comprehensive documentation for the RESTful API endpoints of the School Management System.

## 🔐 Authentication

All API endpoints (except login/logout) require authentication. Include the session cookie or API key in your requests.

### Authentication Methods

#### Session-Based Authentication
```http
Cookie: PHPSESSID=session_id_here
```

#### API Key Authentication (Future Feature)
```http
Authorization: Bearer your_api_key_here
```

### Headers
```http
Content-Type: application/json
X-Requested-With: XMLHttpRequest
X-CSRF-Token: csrf_token_here
```

## 📊 Response Format

All API responses follow this format:

### Success Response
```json
{
    "status": "success",
    "message": "Operation completed successfully",
    "data": {
        // Response data here
    },
    "pagination": {
        "page": 1,
        "limit": 10,
        "total": 100,
        "total_pages": 10
    }
}
```

### Error Response
```json
{
    "status": "error",
    "message": "Error description",
    "errors": {
        "field_name": "Error message"
    }
}
```

### HTTP Status Codes
- `200 OK` - Request successful
- `201 Created` - Resource created
- `400 Bad Request` - Invalid input
- `401 Unauthorized` - Authentication required
- `403 Forbidden` - Access denied
- `404 Not Found` - Resource not found
- `422 Unprocessable Entity` - Validation failed
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

## 🔑 Authentication Endpoints

### Login
```http
POST /api/login
```

**Request Body:**
```json
{
    "username": "admin",
    "password": "password123"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "username": "admin",
            "email": "admin@school.com",
            "role": "admin",
            "first_name": "Admin",
            "last_name": "User"
        },
        "session_id": "session_id_here"
    }
}
```

### Logout
```http
POST /api/logout
```

**Response:**
```json
{
    "status": "success",
    "message": "Logout successful"
}
```

### Check Session
```http
GET /api/session
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "authenticated": true,
        "user": {
            "id": 1,
            "username": "admin",
            "role": "admin"
        }
    }
}
```

## 👥 User Management

### Get Users
```http
GET /api/users
```

**Query Parameters:**
- `page` (int): Page number (default: 1)
- `limit` (int): Items per page (default: 10)
- `role` (string): Filter by role (admin, teacher, student)
- `search` (string): Search term
- `status` (string): Filter by status (active, inactive)

**Response:**
```json
{
    "status": "success",
    "data": {
        "users": [
            {
                "id": 1,
                "username": "admin",
                "email": "admin@school.com",
                "role": "admin",
                "status": "active",
                "created_at": "2024-01-01 10:00:00",
                "last_login": "2024-01-15 14:30:00"
            }
        ]
    },
    "pagination": {
        "page": 1,
        "limit": 10,
        "total": 50,
        "total_pages": 5
    }
}
```

### Create User
```http
POST /api/users
```

**Request Body:**
```json
{
    "username": "newuser",
    "email": "newuser@school.com",
    "password": "password123",
    "role": "teacher",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "123-456-7890",
    "address": "123 Main St"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "User created successfully",
    "data": {
        "id": 2,
        "username": "newuser",
        "email": "newuser@school.com",
        "role": "teacher"
    }
}
```

### Update User
```http
PUT /api/users/{id}
```

**Request Body:**
```json
{
    "email": "updated@school.com",
    "phone": "987-654-3210",
    "address": "456 Oak Ave"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "User updated successfully",
    "data": {
        "id": 2,
        "email": "updated@school.com",
        "phone": "987-654-3210"
    }
}
```

### Delete User
```http
DELETE /api/users/{id}
```

**Response:**
```json
{
    "status": "success",
    "message": "User deleted successfully"
}
```

## 🎓 Student Management

### Get Students
```http
GET /api/students
```

**Query Parameters:**
- `page` (int): Page number
- `limit` (int): Items per page
- `search` (string): Search term
- `class` (int): Filter by class ID
- `section` (int): Filter by section ID
- `status` (string): Filter by status

**Response:**
```json
{
    "status": "success",
    "data": {
        "students": [
            {
                "id": 1,
                "student_id": "STU2024001",
                "user_id": 3,
                "first_name": "Alice",
                "last_name": "Brown",
                "gender": "female",
                "date_of_birth": "2008-03-15",
                "phone": "555-0101",
                "address": "123 Main St",
                "parent_name": "James Brown",
                "parent_phone": "555-0102",
                "parent_email": "james.brown@email.com",
                "enrollment_date": "2023-08-01",
                "status": "active",
                "class_name": "Grade 10",
                "section_name": "A",
                "username": "alice",
                "email": "alice.brown@email.com"
            }
        ]
    },
    "pagination": {
        "page": 1,
        "limit": 10,
        "total": 25,
        "total_pages": 3
    }
}
```

### Create Student
```http
POST /api/students
```

**Request Body:**
```json
{
    "username": "newstudent",
    "email": "newstudent@email.com",
    "password": "password123",
    "first_name": "Bob",
    "last_name": "Smith",
    "gender": "male",
    "date_of_birth": "2008-07-22",
    "phone": "555-0103",
    "address": "456 Oak Ave",
    "parent_name": "Robert Smith",
    "parent_phone": "555-0104",
    "parent_email": "robert.smith@email.com",
    "enrollment_date": "2024-01-15",
    "class_id": 1,
    "section_id": 2
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Student created successfully",
    "data": {
        "id": 6,
        "student_id": "STU2024006",
        "user_id": 7,
        "first_name": "Bob",
        "last_name": "Smith"
    }
}
```

### Get Student Details
```http
GET /api/students/{id}
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "student_id": "STU2024001",
        "first_name": "Alice",
        "last_name": "Brown",
        "gender": "female",
        "date_of_birth": "2008-03-15",
        "age": 15,
        "phone": "555-0101",
        "address": "123 Main St",
        "parent_name": "James Brown",
        "parent_phone": "555-0102",
        "parent_email": "james.brown@email.com",
        "enrollment_date": "2023-08-01",
        "status": "active",
        "class": {
            "id": 1,
            "name": "Grade 10",
            "grade_level": 10
        },
        "section": {
            "id": 1,
            "name": "A"
        },
        "statistics": {
            "attendance": {
                "total": 180,
                "present": 165,
                "absent": 10,
                "late": 5,
                "percentage": 91.67
            },
            "results": {
                "total_exams": 12,
                "average_marks": 85.5,
                "highest": 95,
                "lowest": 72
            }
        }
    }
}
```

## 👨‍🏫 Teacher Management

### Get Teachers
```http
GET /api/teachers
```

**Query Parameters:**
- `page` (int): Page number
- `limit` (int): Items per page
- `search` (string): Search term
- `status` (string): Filter by status

**Response:**
```json
{
    "status": "success",
    "data": {
        "teachers": [
            {
                "id": 1,
                "employee_id": "EMP2024001",
                "user_id": 2,
                "first_name": "John",
                "last_name": "Smith",
                "gender": "male",
                "date_of_birth": "1985-05-15",
                "phone": "123-456-7890",
                "address": "123 Teacher St",
                "qualification": "M.Ed Mathematics",
                "specialization": "Mathematics",
                "experience_years": 10,
                "hire_date": "2014-08-01",
                "salary": "50000.00",
                "status": "active",
                "username": "john.smith",
                "email": "john.smith@school.com"
            }
        ]
    }
}
```

### Get Teacher Classes
```http
GET /api/teachers/{id}/classes
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "classes": [
            {
                "id": 1,
                "name": "Grade 10",
                "grade_level": 10,
                "sections": [
                    {
                        "id": 1,
                        "name": "A",
                        "room_number": "101",
                        "max_students": 35,
                        "current_students": 32
                    }
                ]
            }
        ]
    }
}
```

## 📚 Class Management

### Get Classes
```http
GET /api/classes
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "classes": [
            {
                "id": 1,
                "name": "Grade 10",
                "grade_level": 10,
                "description": "Tenth Grade Class",
                "status": "active",
                "sections_count": 2,
                "students_count": 65,
                "teachers_count": 2
            }
        ]
    }
}
```

### Get Class Details
```http
GET /api/classes/{id}
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "Grade 10",
        "grade_level": 10,
        "description": "Tenth Grade Class",
        "status": "active",
        "sections": [
            {
                "id": 1,
                "name": "A",
                "teacher_id": 1,
                "teacher_name": "John Smith",
                "room_number": "101",
                "max_students": 35,
                "current_students": 32,
                "students": [
                    {
                        "id": 1,
                        "student_id": "STU2024001",
                        "first_name": "Alice",
                        "last_name": "Brown"
                    }
                ]
            }
        ],
        "subjects": [
            {
                "id": 1,
                "name": "Mathematics",
                "code": "MATH101"
            }
        ]
    }
}
```

## 📖 Subject Management

### Get Subjects
```http
GET /api/subjects
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "subjects": [
            {
                "id": 1,
                "name": "Mathematics",
                "code": "MATH101",
                "description": "Mathematics for Grade 10",
                "credits": 4,
                "status": "active",
                "exam_count": 5
            }
        ]
    }
}
```

## 📅 Attendance Management

### Get Attendance
```http
GET /api/attendance
```

**Query Parameters:**
- `date` (string): Date in Y-m-d format
- `section` (int): Section ID
- `student` (int): Student ID
- `month` (string): Month in Y-m format

**Response:**
```json
{
    "status": "success",
    "data": {
        "attendance": [
            {
                "id": 1,
                "student_id": 1,
                "section_id": 1,
                "subject_id": 1,
                "date": "2024-01-15",
                "status": "present",
                "remarks": null,
                "marked_by": 1,
                "marked_at": "2024-01-15 09:00:00",
                "student": {
                    "id": 1,
                    "student_id": "STU2024001",
                    "first_name": "Alice",
                    "last_name": "Brown"
                },
                "subject": {
                    "id": 1,
                    "name": "Mathematics",
                    "code": "MATH101"
                }
            }
        ]
    }
}
```

### Mark Attendance
```http
POST /api/attendance
```

**Request Body:**
```json
{
    "date": "2024-01-15",
    "section_id": 1,
    "attendance": {
        "1": "present",
        "2": "absent",
        "3": "late"
    },
    "remarks": {
        "2": "Sick leave"
    }
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Attendance marked successfully"
}
```

### Get Attendance Statistics
```http
GET /api/attendance/statistics
```

**Query Parameters:**
- `date` (string): Specific date
- `month` (string): Month in Y-m format
- `section` (int): Section ID

**Response:**
```json
{
    "status": "success",
    "data": {
        "total_students": 35,
        "present": 32,
        "absent": 2,
        "late": 1,
        "percentage": 91.43,
        "by_date": [
            {
                "date": "2024-01-15",
                "present": 32,
                "absent": 2,
                "late": 1
            }
        ]
    }
}
```

## 📊 Results Management

### Get Results
```http
GET /api/results
```

**Query Parameters:**
- `student` (int): Student ID
- `exam` (int): Exam ID
- `class` (int): Class ID
- `subject` (int): Subject ID
- `year` (string): Academic year

**Response:**
```json
{
    "status": "success",
    "data": {
        "results": [
            {
                "id": 1,
                "student_id": 1,
                "exam_id": 1,
                "marks_obtained": 85.00,
                "grade": "A",
                "remarks": "Good performance",
                "created_at": "2024-01-20 10:00:00",
                "student": {
                    "id": 1,
                    "student_id": "STU2024001",
                    "first_name": "Alice",
                    "last_name": "Brown"
                },
                "exam": {
                    "id": 1,
                    "title": "Mathematics Midterm",
                    "exam_type": "midterm",
                    "total_marks": 100.00,
                    "exam_date": "2024-01-15"
                },
                "subject": {
                    "id": 1,
                    "name": "Mathematics",
                    "code": "MATH101"
                }
            }
        ]
    }
}
```

### Store Result
```http
POST /api/results
```

**Request Body:**
```json
{
    "student_id": 1,
    "exam_id": 1,
    "marks_obtained": 88.00,
    "remarks": "Excellent work"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Result stored successfully"
}
```

### Get Student Results Summary
```http
GET /api/results/student/{id}/summary
```

**Query Parameters:**
- `year` (string): Academic year

**Response:**
```json
{
    "status": "success",
    "data": {
        "student": {
            "id": 1,
            "student_id": "STU2024001",
            "first_name": "Alice",
            "last_name": "Brown"
        },
        "summary": {
            "total_exams": 12,
            "average_marks": 85.5,
            "gpa": 3.8,
            "grade_distribution": {
                "A+": 2,
                "A": 4,
                "B+": 3,
                "B": 2,
                "C+": 1
            },
            "subject_averages": {
                "Mathematics": 88.0,
                "English": 82.0,
                "Science": 87.5
            }
        }
    }
}
```

## 🔍 Search API

### Global Search
```http
GET /api/search
```

**Query Parameters:**
- `q` (string): Search term
- `type` (string): Search type (students, teachers, classes, subjects)

**Response:**
```json
{
    "status": "success",
    "data": {
        "results": [
            {
                "type": "student",
                "id": 1,
                "title": "Alice Brown",
                "description": "Student ID: STU2024001",
                "link": "/students/1"
            }
        ]
    }
}
```

## 📈 Statistics API

### Get Dashboard Statistics
```http
GET /api/stats/dashboard
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "overview": {
            "total_students": 150,
            "total_teachers": 25,
            "total_classes": 8,
            "total_subjects": 12
        },
        "attendance": {
            "today_present": 142,
            "today_total": 150,
            "today_percentage": 94.67,
            "monthly_average": 92.5
        },
        "results": {
            "recent_exams": 5,
            "average_marks": 83.2,
            "grade_distribution": {
                "A+": 15,
                "A": 45,
                "B+": 30,
                "B": 25,
                "C+": 20,
                "C": 15
            }
        },
        "recent_activities": [
            {
                "type": "student_enrolled",
                "message": "New student enrolled: John Doe",
                "timestamp": "2024-01-15 10:30:00"
            }
        ]
    }
}
```

### Get Attendance Statistics
```http
GET /api/stats/attendance
```

**Query Parameters:**
- `period` (string): today, week, month, year
- `class` (int): Class ID

**Response:**
```json
{
    "status": "success",
    "data": {
        "period": "month",
        "total_students": 150,
        "present_days": 20,
        "total_days": 22,
        "present": 2980,
        "absent": 120,
        "late": 60,
        "percentage": 90.0,
        "daily_breakdown": [
            {
                "date": "2024-01-01",
                "present": 148,
                "absent": 2
            }
        ]
    }
}
```

## 🔔 Notifications API

### Get Notifications
```http
GET /api/notifications
```

**Query Parameters:**
- `limit` (int): Number of notifications (default: 10)
- `unread` (bool): Filter unread only

**Response:**
```json
{
    "status": "success",
    "data": {
        "notifications": [
            {
                "id": 1,
                "type": "info",
                "title": "New Exam Scheduled",
                "message": "Mathematics midterm scheduled for January 20",
                "read": false,
                "created_at": "2024-01-15 14:30:00",
                "actions": [
                    {
                        "label": "View Details",
                        "action": "view_exam",
                        "url": "/exams/1"
                    }
                ]
            }
        ],
        "unread_count": 3
    }
}
```

### Mark Notification as Read
```http
PUT /api/notifications/{id}/read
```

**Response:**
```json
{
    "status": "success",
    "message": "Notification marked as read"
}
```

## 🔧 System API

### Get System Information
```http
GET /api/system/info
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "version": "1.0.0",
        "php_version": "8.2.0",
        "mysql_version": "8.0.33",
        "environment": "production",
        "timezone": "Asia/Dhaka",
        "max_upload_size": "5M",
        "post_max_size": "6M",
        "memory_limit": "256M",
        "execution_time": 30
    }
}
```

### Health Check
```http
GET /api/health
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "database": "connected",
        "cache": "available",
        "storage": "available",
        "services": {
            "mysql": "running",
            "apache": "running",
            "php": "running"
        }
    }
}
```

## 📝 File Upload API

### Upload File
```http
POST /api/upload
```

**Request:** (multipart/form-data)
```
file: [file data]
type: profile_picture
user_id: 1
```

**Response:**
```json
{
    "status": "success",
    "message": "File uploaded successfully",
    "data": {
        "filename": "profile_1642230400_abc123.jpg",
        "original_name": "profile.jpg",
        "size": 245760,
        "type": "image/jpeg",
        "url": "/assets/images/uploads/profile_1642230400_abc123.jpg"
    }
}
```

## 🚨 Error Handling

### Validation Errors
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "email": "Please enter a valid email address",
        "password": "Password must be at least 6 characters"
    }
}
```

### Authentication Errors
```json
{
    "status": "error",
    "message": "Authentication required"
}
```

### Authorization Errors
```json
{
    "status": "error",
    "message": "Access denied"
}
```

### Rate Limiting
```json
{
    "status": "error",
    "message": "Rate limit exceeded. Please try again later."
}
```

## 🔄 Rate Limiting

API endpoints are rate-limited to prevent abuse:

- **General endpoints**: 100 requests per hour
- **Authentication endpoints**: 10 requests per minute
- **File upload endpoints**: 20 requests per hour
- **Search endpoints**: 200 requests per hour

Rate limiting is based on IP address and authenticated user.

## 🔒 Security Features

### CSRF Protection
All state-changing requests require a valid CSRF token.

### Input Validation
All inputs are validated and sanitized before processing.

### SQL Injection Prevention
All database queries use prepared statements.

### XSS Prevention
All output is properly escaped to prevent XSS attacks.

## 📱 SDK and Libraries

### JavaScript SDK
```javascript
// Include the SDK
<script src="/assets/js/api-client.js"></script>

// Initialize client
const client = new SchoolManagementAPI({
    baseURL: '/api',
    timeout: 5000
});

// Make requests
client.getStudents()
    .then(data => console.log(data))
    .catch(error => console.error(error));
```

### Python SDK (Future)
```python
from school_management_api import SchoolManagementAPI

client = SchoolManagementAPI(base_url='http://localhost/School management/api')
students = client.get_students()
```

## 🧪 Testing

### API Testing with curl

```bash
# Login
curl -X POST http://localhost/School management/api/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password123"}'

# Get students
curl -X GET http://localhost/School management/api/students \
  -H "Cookie: PHPSESSID=session_id_here"

# Create student
curl -X POST http://localhost/School management/api/students \
  -H "Content-Type: application/json" \
  -H "X-CSRF-Token: token_here" \
  -d '{"username":"newstudent","email":"test@test.com","password":"password123","first_name":"Test","last_name":"User","gender":"male","date_of_birth":"2008-01-01"}'
```

## 📚 Additional Resources

### Postman Collection
Import the provided Postman collection for easy API testing.

### OpenAPI Specification
The API is documented using OpenAPI 3.0 specification.

### Code Examples
Check the `examples/` directory for code examples in various programming languages.

---

**Last Updated**: January 15, 2024
**Version**: 1.0.0
**Contact**: api-support@schoolmanagement.com
