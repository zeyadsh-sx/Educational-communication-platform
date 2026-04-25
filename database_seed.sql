USE educational_platform;
-- Insert sample users
INSERT INTO users (
    username,
    email,
    password,
    full_name,
    name,
    user_type
  )
VALUES (
    'student_sara',
    'sara@test.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Sara Ahmed',
    'Sara Ahmed',
    'student'
  ),
  (
    'prof_ahmed',
    'ahmed@test.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Dr. Ahmed Ali',
    'Dr. Ahmed Ali',
    'professor'
  );
-- Insert sample courses
INSERT INTO courses (
    course_name,
    course_code,
    professor_id,
    description
  )
VALUES (
    'Computer Networks',
    'CS301',
    2,
    'Introduction to computer networks and protocols'
  ),
  (
    'Data Structures',
    'CS201',
    2,
    'Fundamental data structures and algorithms'
  );
-- Insert sample enrollments
INSERT INTO course_enrollments (course_id, student_id)
VALUES (1, 1),
  (2, 1);