INSERT INTO users (name, email, password, role) VALUES
('Salma', 'salma@test.com', '123', 'student'),
('Dr.Ali', 'ali@test.com', '123', 'professor');

INSERT INTO courses (title, professor_id) VALUES
('Networking', 2);

INSERT INTO course_enrollments (student_id, course_id) VALUES
(1,1);