-- ============================================================
-- أكاديمية ماستر — تحديثات قاعدة البيانات
-- شغّل هذا الملف مرة واحدة بعد database_schema.sql
-- ============================================================

USE educational_platform;

-- ── 0. إضافة قيمة admin لـ user_type ─────────────────────────
ALTER TABLE users MODIFY COLUMN user_type ENUM('student','professor','admin') NOT NULL DEFAULT 'student';

-- ── 1. إضافة حقول للـ users ──────────────────────────────────
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone         VARCHAR(20)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS parent_phone  VARCHAR(20)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS grade         VARCHAR(80)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS points        INT UNSIGNED DEFAULT 0,
    ADD COLUMN IF NOT EXISTS is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL;

-- ── 2. تعديل notifications — إضافة نوع الإشعار ──────────────
ALTER TABLE notifications
    ADD COLUMN IF NOT EXISTS type ENUM(
        'new_lesson',
        'new_homework',
        'new_exam',
        'subscription_expiry',
        'general'
    ) NOT NULL DEFAULT 'general';

-- ── 3. جدول الاشتراكات ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS subscriptions (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    student_id      INT          NOT NULL,
    course_id       INT          NOT NULL,
    plan            ENUM('monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly',
    price           DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    start_date      DATE         DEFAULT NULL,
    end_date        DATE         DEFAULT NULL,
    status          ENUM('pending','active','expired','cancelled','rejected') NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE
);

-- ── 4. جدول إيصالات الدفع ────────────────────────────────────
CREATE TABLE IF NOT EXISTS payment_receipts (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    subscription_id  INT          NOT NULL,
    student_id       INT          NOT NULL,
    receipt_image    VARCHAR(255) NOT NULL,
    amount           DECIMAL(8,2) DEFAULT NULL,
    notes            TEXT         DEFAULT NULL,
    status           ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by      INT          DEFAULT NULL,
    reviewed_at      TIMESTAMP    DEFAULT NULL,
    reject_reason    TEXT         DEFAULT NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)  ON DELETE CASCADE,
    FOREIGN KEY (student_id)      REFERENCES users(id)           ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by)     REFERENCES users(id)           ON DELETE SET NULL
);

-- ── 5. جدول الحضور ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS attendance (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    course_id    INT  NOT NULL,
    student_id   INT  NOT NULL,
    lesson_date  DATE NOT NULL,
    status       ENUM('present','absent','late') NOT NULL DEFAULT 'present',
    notes        TEXT DEFAULT NULL,
    recorded_by  INT  DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_attendance (course_id, student_id, lesson_date),
    FOREIGN KEY (course_id)   REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)  REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)   ON DELETE SET NULL
);

-- ── 6. جدول الدرجات ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS grades (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    course_id    INT          NOT NULL,
    student_id   INT          NOT NULL,
    title        VARCHAR(200) NOT NULL,
    grade_type   ENUM('exam','homework','quiz','participation') NOT NULL DEFAULT 'exam',
    score        DECIMAL(5,2) NOT NULL DEFAULT 0,
    max_score    DECIMAL(5,2) NOT NULL DEFAULT 100,
    notes        TEXT         DEFAULT NULL,
    graded_by    INT          DEFAULT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (graded_by)  REFERENCES users(id)   ON DELETE SET NULL
);

-- ── 7. أسعار الكورسات ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS course_pricing (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    course_id       INT          NOT NULL UNIQUE,
    monthly_price   DECIMAL(8,2) NOT NULL DEFAULT 0,
    quarterly_price DECIMAL(8,2) NOT NULL DEFAULT 0,
    yearly_price    DECIMAL(8,2) NOT NULL DEFAULT 0,
    currency        VARCHAR(10)  NOT NULL DEFAULT 'EGP',
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- ── 8. student_achievements ──────────────────────────────────
CREATE TABLE IF NOT EXISTS student_achievements (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    student_id       INT          NOT NULL,
    achievement_name VARCHAR(100) NOT NULL,
    achievement_icon VARCHAR(20)  DEFAULT '⭐',
    earned_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── 9. إنشاء أول حساب أدمن (غيّر الباسورد فور التشغيل!) ─────
-- كلمة المرور الافتراضية: Admin@2026
INSERT IGNORE INTO users (username, full_name, email, password, user_type)
VALUES (
    'admin',
    'مدير النظام',
    'admin@masteracademy.eg',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Admin@2026
    'admin'
);


-- ── 1. إضافة حقول للـ users ──────────────────────────────────
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS phone         VARCHAR(20)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS parent_phone  VARCHAR(20)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS grade         VARCHAR(80)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS points        INT UNSIGNED DEFAULT 0,
    ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255) DEFAULT NULL;

-- ── 2. تعديل notifications — إضافة نوع الإشعار ──────────────
ALTER TABLE notifications
    ADD COLUMN IF NOT EXISTS type ENUM(
        'new_lesson',
        'new_homework',
        'new_exam',
        'subscription_expiry',
        'general'
    ) NOT NULL DEFAULT 'general';

-- ── 3. جدول الاشتراكات ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS subscriptions (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    student_id      INT          NOT NULL,
    course_id       INT          NOT NULL,
    plan            VARCHAR(50)  NOT NULL DEFAULT 'monthly',   -- monthly / quarterly / yearly
    price           DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    start_date      DATE         DEFAULT NULL,
    end_date        DATE         DEFAULT NULL,
    status          ENUM('pending','active','expired','cancelled','rejected')
                                 NOT NULL DEFAULT 'pending',
    created_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (course_id)  REFERENCES courses(id)  ON DELETE CASCADE
);

-- ── 4. جدول إيصالات الدفع ────────────────────────────────────
CREATE TABLE IF NOT EXISTS payment_receipts (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    subscription_id  INT          NOT NULL,
    student_id       INT          NOT NULL,
    receipt_image    VARCHAR(255) NOT NULL,               -- مسار الصورة
    amount           DECIMAL(8,2) DEFAULT NULL,
    notes            TEXT         DEFAULT NULL,
    status           ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by      INT          DEFAULT NULL,           -- admin user_id
    reviewed_at      TIMESTAMP    DEFAULT NULL,
    reject_reason    TEXT         DEFAULT NULL,
    created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id)  ON DELETE CASCADE,
    FOREIGN KEY (student_id)      REFERENCES users(id)           ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by)     REFERENCES users(id)           ON DELETE SET NULL
);

-- ── 5. جدول الحضور ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS attendance (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    course_id    INT  NOT NULL,
    student_id   INT  NOT NULL,
    lesson_date  DATE NOT NULL,
    status       ENUM('present','absent','late') NOT NULL DEFAULT 'present',
    notes        TEXT DEFAULT NULL,
    recorded_by  INT  DEFAULT NULL,   -- professor_id
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance (course_id, student_id, lesson_date),
    FOREIGN KEY (course_id)   REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id)  REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)   ON DELETE SET NULL
);

-- ── 6. جدول الدرجات ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS grades (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    course_id    INT          NOT NULL,
    student_id   INT          NOT NULL,
    title        VARCHAR(200) NOT NULL,         -- "امتحان الفصل الأول" / "واجب رقم 1"
    grade_type   ENUM('exam','homework','quiz','participation') NOT NULL DEFAULT 'exam',
    score        DECIMAL(5,2) NOT NULL DEFAULT 0,
    max_score    DECIMAL(5,2) NOT NULL DEFAULT 100,
    notes        TEXT         DEFAULT NULL,
    graded_by    INT          DEFAULT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id)  REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (graded_by)  REFERENCES users(id)   ON DELETE SET NULL
);

-- ── 7. أسعار الكورسات ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS course_pricing (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    course_id       INT          NOT NULL UNIQUE,
    monthly_price   DECIMAL(8,2) NOT NULL DEFAULT 0,
    quarterly_price DECIMAL(8,2) NOT NULL DEFAULT 0,
    yearly_price    DECIMAL(8,2) NOT NULL DEFAULT 0,
    currency        VARCHAR(10)  NOT NULL DEFAULT 'EGP',
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- ── 8. إضافة student_achievements إن لم تكن موجودة ──────────
CREATE TABLE IF NOT EXISTS student_achievements (
    id               INT PRIMARY KEY AUTO_INCREMENT,
    student_id       INT          NOT NULL,
    achievement_name VARCHAR(100) NOT NULL,
    achievement_icon VARCHAR(20)  DEFAULT '⭐',
    earned_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── 9. إضافة material_downloads إن لم تكن موجودة ────────────
CREATE TABLE IF NOT EXISTS material_downloads (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    material_id   INT NOT NULL,
    user_id       INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE
);

-- ── بيانات تجريبية — أسعار الكورسات ─────────────────────────
-- سيتم تعبئتها تلقائيًا عند إنشاء كورس
-- INSERT INTO course_pricing (course_id, monthly_price, quarterly_price, yearly_price)
-- VALUES (1, 150, 400, 1400);
