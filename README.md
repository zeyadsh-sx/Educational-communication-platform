# Educational Communication Platform

## Description
A simple and effective web-based platform built with PHP and MySQL to facilitate communication between university professors and students. The platform organizes academic interactions, reduces chaos, and improves the educational experience.

## Features

### For Professors:
- Upload lectures and course materials
- Manage office hours and appointments
- Answer student questions
- Post announcements and updates
- Track student engagement
- Course management

### For Students:
- Access course materials anytime
- Download lectures and resources
- Ask questions and get answers
- Book office hour appointments
- View announcements
- Track academic progress

### General Features:
- User authentication and authorization
- Responsive design for all devices
- Real-time notifications
- File management system
- Search functionality
- Secure data handling

## Requirements

### Server Requirements:
- PHP 7.4+ (recommended PHP 8.0+)
- MySQL 5.7+ or MariaDB 10.2+
- Apache or Nginx web server
- PHP extensions: PDO, PDO_MySQL, GD, Fileinfo

### Optional:
- SMTP server for email notifications
- SSL certificate for HTTPS

## Installation

### 1. Database Setup
```sql
-- Create the database and import the schema
mysql -u root -p < database_schema.sql
```

### 2. Configuration
1. Copy the project files to your web server directory
2. Update database credentials in `config/database.php`
3. Configure site settings in `config/settings.php`
4. Set proper file permissions:
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/lectures/
   chmod 755 uploads/materials/
   ```

### 3. Web Server Configuration
#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Project Structure

```
educational-platform/
|
|-- config/
|   |-- database.php           # Database connection
|   |-- settings.php           # App settings
|
|-- includes/
|   |-- header.php             # Header template
|   |-- footer.php             # Footer template
|   |-- functions.php          # Helper functions
|   |-- auth.php              # Authentication functions
|
|-- css/
|   |-- style.css              # Main stylesheet
|   |-- responsive.css         # Responsive design
|
|-- js/
|   |-- main.js               # Main JavaScript file
|   |-- validation.js         # Form validation
|
|-- uploads/
|   |-- lectures/             # Uploaded lectures
|   |-- materials/            # Course materials
|
|-- admin/
|   |-- dashboard.php         # Professor dashboard
|   |-- upload.php            # File upload
|   |-- appointments.php      # Manage appointments
|   |-- questions.php         # Answer questions
|
|-- student/
|   |-- dashboard.php         # Student dashboard
|   |-- materials.php         # View materials
|   |-- ask_question.php      # Ask questions
|   |-- book_appointment.php  # Book appointments
|
|-- auth/
|   |-- login.php             # Login page
|   |-- register.php          # Registration page
|   |-- logout.php            # Logout
|
|-- pages/
|   |-- home.php              # Home page
|   |-- about.php             # About page
|
|-- index.php                 # Main entry point
```

## Usage

### Default Login Credentials:
- **Professor:** username: `prof_ahmed`, password: `password`
- **Student:** username: `student_sara`, password: `password`

### For Development:
1. Enable debug mode in `config/settings.php`
2. Set up local server (XAMPP, WAMP, or Docker)
3. Import the database schema
4. Access the platform via `http://localhost/educational-platform`

## Security Features

- Password hashing with bcrypt
- SQL injection prevention
- XSS protection
- CSRF token validation
- Secure session management
- File upload security
- Input validation and sanitization

## Team Development

The project is designed for a 6-person team:

1. **Database & Core Setup** - Database design and basic configuration
2. **Authentication System** - User login, registration, and session management
3. **Professor Dashboard** - Professor-specific features and file management
4. **Student Dashboard** - Student-specific features and content access
5. **Q&A & Appointments** - Communication systems and booking
6. **UI/UX & Frontend** - Design, responsiveness, and user experience

## Contributing

1. Follow the existing code style and conventions
2. Test all changes before committing
3. Update documentation for new features
4. Report bugs and security issues immediately

## License

This project is for educational purposes. Feel free to modify and use it according to your needs.

## Support

For technical support or questions:
- Check the documentation first
- Review error logs for debugging
- Test with sample data provided

## Future Enhancements

- Mobile app development
- Video conferencing integration
- Advanced analytics dashboard
- Multi-language support
- API for third-party integrations
