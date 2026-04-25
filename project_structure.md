# Educational Communication Platform - Project Structure

## Folder Structure
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
|-- README.md                 # Project documentation
```

## Database Tables Needed

### 1. users
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- username (VARCHAR, UNIQUE)
- email (VARCHAR, UNIQUE)
- password (VARCHAR)
- full_name (VARCHAR)
- user_type (ENUM: 'professor', 'student')
- created_at (TIMESTAMP)

### 2. courses
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- course_name (VARCHAR)
- course_code (VARCHAR, UNIQUE)
- professor_id (INT, FOREIGN KEY)
- description (TEXT)
- created_at (TIMESTAMP)

### 3. materials
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR)
- description (TEXT)
- file_path (VARCHAR)
- file_type (VARCHAR)
- course_id (INT, FOREIGN KEY)
- professor_id (INT, FOREIGN KEY)
- upload_date (TIMESTAMP)

### 4. questions
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- question_text (TEXT)
- student_id (INT, FOREIGN KEY)
- professor_id (INT, FOREIGN KEY)
- course_id (INT, FOREIGN KEY)
- answer_text (TEXT, NULLABLE)
- status (ENUM: 'pending', 'answered')
- created_at (TIMESTAMP)
- answered_at (TIMESTAMP, NULLABLE)

### 5. appointments
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- student_id (INT, FOREIGN KEY)
- professor_id (INT, FOREIGN KEY)
- appointment_date (DATETIME)
- duration (INT, in minutes)
- status (ENUM: 'pending', 'confirmed', 'cancelled')
- notes (TEXT, NULLABLE)
- created_at (TIMESTAMP)

### 6. announcements
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- title (VARCHAR)
- content (TEXT)
- professor_id (INT, FOREIGN KEY)
- course_id (INT, FOREIGN KEY, NULLABLE)
- priority (ENUM: 'low', 'medium', 'high')
- created_at (TIMESTAMP)

## Features Required

### Professor Features:
- Upload lectures and materials
- Create and manage appointments
- Answer student questions
- Post announcements
- View student lists
- Manage course content

### Student Features:
- View course materials
- Download files
- Ask questions
- Book appointments
- View announcements
- Track progress

### General Features:
- User authentication
- Responsive design
- Search functionality
- Notifications
- File management
- Dashboard analytics
