# Educational Platform - Detailed Team Tasks

## سمية - نظام المستخدمين والمصادقة
**المهام الرئيسية:**
- [x] صفحة تسجيل حساب جديد (Register)
- [x] صفحة تسجيل الدخول (Login)
- [x] نظام الخروج (Logout)
- [x] تحديد نوع المستخدم (دكتور/طالب)


**الملفات المطلوبة:**
- `auth/register.php` - صفحة التسجيل (Folder: auth/)
- `auth/login.php` - صفحة الدخول (Folder: auth/)
- `auth/logout.php` - تسجيل الخروج (Folder: auth/)
- `includes/auth.php` - دوال المصادقة (Folder: includes/)

**وظائف API:**
- `POST /api/users/register` - تسجيل مستخدم جديد
- `POST /api/users/login` - تسجيل الدخول
- `GET /api/users/profile` - جلب بيانات المستخدم
- `PUT /api/users/profile` - تحديث بيانات المستخدم
- `POST /api/users/logout` - تسجيل الخروج

---

## زيدان - نظام الكورسات
**المهام الرئيسية:**
- [x] إنشاء كورس جديد
- [x] عرض قائمة الكورسات
- [x] انضمام الطلبة للكورسات
- [x] إدارة الطلبة في الكورس
- [x] عرض تفاصيل الكورس
- [x] البحث في الكورسات

**الملفات المطلوبة:**
- `courses/create.php` - إنشاء كورس (Folder: courses/)
- `courses/join.php` - انضمام للكورس (Folder: courses/)
- `courses/manage.php` - إدارة الكورس (Folder: courses/)
- `courses/view.php` - عرض الكورس (Folder: courses/)
- `includes/course_functions.php` - دوال الكورسات (Folder: includes/)

**وظائف API:**
- `POST /api/courses/create` - إنشاء كورس
- `GET /api/courses` - قائمة الكورسات
- `POST /api/courses/join` - انضمام للكورس
- `GET /api/courses/{id}/students` - طلبة الكورس
- `DELETE /api/courses/{id}/students/{student_id}` - إزالة طالب

---

## سامية - رفع الملفات والإعلانات
**المهام الرئيسية:**
- [x] رفع المحاضرات والملفات
- [x]  عرض الإعلانات
- [x] تحميل الملفات
- [x] إضافة الإعلانات

**الملفات المطلوبة:**
- `materials/upload.php` - رفع الملفات (Folder: materials/)
- `materials/download.php` - تحميل الملفات (Folder: materials/)
- `announcements/create.php` - إنشاء إعلان (Folder: announcements/)
- `announcements/view.php` - عرض الإعلانات (Folder: announcements/)

**وظائف API:**
- `POST /api/materials/upload` - رفع ملف
- `GET /api/materials/{course_id}` - ملفات الكورس
- `GET /api/materials/download/{id}` - تحميل ملف
- `POST /api/announcements` - إنشاء إعلان
- `GET /api/announcements/{course_id}` - إعلانات الكورس

---

## منه - الأسئلة والمواعيد والإشعارات
**المهام الرئيسية:**
- `courses/list.php` - قائمة الكورسات (Folder: courses/)
- [x] إدارة حسابات الدكاترة
الاسئله 
رفع ورد 



**الملفات المطلوبة:**
- `admin/manage_professors.php` - إدارة الدكاترة (Folder: admin/)


**وظائف API:**
- `POST /api/questions/ask` - طرح سؤال
- `POST /api/questions/answer` - إجابة على سؤال
- `GET /api/questions/{course_id}` - أسئلة الكورس
- `POST /api/appointments/book` - حجز موعد
- `GET /api/appointments/available` - المواعيد المتاحة
- `GET /api/notifications/{user_id}` - إشعارات المستخدم

---

## سلمى - قاعدة البيانات والعلاقات
**المهام الرئيسية:**
- [x] تنفيذ قاعدة البيانات
- [x] إنشاء العلاقات بين الجداول
- [x] التحقق من سلامة البيانات
- [x] إنشاء الـ Foreign Keys
- [x] اختبار العلاقات
- [x] عمل نسخ احتياطية

**الملفات المطلوبة:**
- `database_schema.sql` - هيكل قاعدة البيانات (Folder: root/)
- `database_seed.sql` - بيانات تجريبية (Folder: root/)
- `database_backup.sql` - نسخ احتياطية (Folder: root/)
- `config/database.php` - إعدادات الاتصال (Folder: config/)
- `scripts/setup_database.php` - سكربت التثبيت (Folder: scripts/)

**العلاقات المطلوبة:**
- users ← courses (professor_id)
- users ← course_enrollments (student_id)
- courses ← materials (course_id)
- users ← questions (student_id, professor_id)
- users ← appointments (student_id, professor_id)
- users ← notifications (user_id)

---

## زياد - التصميم والتكامل
**المهام الرئيسية:**
- [x] إنشاء API للمستخدمين
- [x] التحقق من صحة البيانات
- [x] نظام الجلسات الآمن


**الملفات المطلوبة:**
- `api/users.php` - API للمستخدمين (Folder: api/)












## المهام الإضافية (إذا لزم الأمر)

### للجميع:
- [x] اختبار الأمان
- [x] تحسين الأداء
- [x] كتابة التوثيق
- [x] تجربة المستخدم

### أولويات التنفيذ:
1. **الأسبوع الأول**: سلمى (قاعدة البيانات) + سمية (نظام المستخدمين)
2. **الأسبوع الثاني**: زيدان (الكورسات) + زياد (التصميم الأساسي)
3. **الأسبوع الثالث**: سامية (الملفات) + منه (الأسئلة والمواعيد)
4. **الأسبوع الرابع**: الجميع (التكامل والاختبار)

---

## نقاط التكامل بين الأعضاء:

1. **سمية ← الجميع**: توفر بيانات المستخدمين والصلاحيات
2. **سلمى ← الجميع**: توفر قاعدة بيانات جاهزة
3. **زيدان ← سامية**: ربط الملفات بالكورسات
4. **زيدان ← منه**: ربط الأسئلة والمواعيد بالكورسات
5. **زياد ← الجميع**: ربط كل الواجهات ببعضها

---

## معايير الجودة:
- الكود نظيف ومعلق
- الأمان عالي الأولوية
- التجربة المستخدم سلسة
- التوثيق كامل
- الاختبار شامل
