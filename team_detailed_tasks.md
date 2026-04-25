# Educational Platform - Detailed Team Tasks

## سمية - نظام المستخدمين والمصادقة
**المهام الرئيسية:**
- [ ] صفحة تسجيل حساب جديد (Register)
- [ ] صفحة تسجيل الدخول (Login)
- [ ] نظام الخروج (Logout)
- [ ] تحديد نوع المستخدم (دكتور/طالب)
- [ ] إنشاء API للمستخدمين
- [ ] التحقق من صحة البيانات
- [ ] نظام الجلسات الآمن

**الملفات المطلوبة:**
- `auth/register.php` - صفحة التسجيل (Folder: auth/)
- `auth/login.php` - صفحة الدخول (Folder: auth/)
- `auth/logout.php` - تسجيل الخروج (Folder: auth/)
- `includes/auth.php` - دوال المصادقة (Folder: includes/)
- `api/users.php` - API للمستخدمين (Folder: api/)

**وظائف API:**
- `POST /api/users/register` - تسجيل مستخدم جديد
- `POST /api/users/login` - تسجيل الدخول
- `GET /api/users/profile` - جلب بيانات المستخدم
- `PUT /api/users/profile` - تحديث بيانات المستخدم
- `POST /api/users/logout` - تسجيل الخروج

---

## زيدان - نظام الكورسات
**المهام الرئيسية:**
- [ ] إنشاء كورس جديد
- [ ] عرض قائمة الكورسات
- [ ] انضمام الطلبة للكورسات
- [ ] إدارة الطلبة في الكورس
- [ ] عرض تفاصيل الكورس
- [ ] البحث في الكورسات

**الملفات المطلوبة:**
- `courses/create.php` - إنشاء كورس (Folder: courses/)
- `courses/list.php` - قائمة الكورسات (Folder: courses/)
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
- [ ] رفع المحاضرات والملفات
- [ ] عرض الملفات للطلبة
- [ ] تحميل الملفات
- [ ] إضافة الإعلانات
- [ ] إدارة حسابات الدكاترة
- [ ] تنظيم الملفات حسب الكورس

**الملفات المطلوبة:**
- `materials/upload.php` - رفع الملفات (Folder: materials/)
- `materials/view.php` - عرض الملفات (Folder: materials/)
- `materials/download.php` - تحميل الملفات (Folder: materials/)
- `announcements/create.php` - إنشاء إعلان (Folder: announcements/)
- `announcements/view.php` - عرض الإعلانات (Folder: announcements/)
- `admin/manage_professors.php` - إدارة الدكاترة (Folder: admin/)

**وظائف API:**
- `POST /api/materials/upload` - رفع ملف
- `GET /api/materials/{course_id}` - ملفات الكورس
- `GET /api/materials/download/{id}` - تحميل ملف
- `POST /api/announcements` - إنشاء إعلان
- `GET /api/announcements/{course_id}` - إعلانات الكورس

---

## منه - الأسئلة والمواعيد والإشعارات
**المهام الرئيسية:**
- [ ] نظام الأسئلة والإجابات
- [ ] حجز المواعيد المكتبية
- [ ] نظام الإشعارات
- [ ] عرض المواعيد المتاحة
- [ ] تأكيد المواعيد
- [ ] إدارة الإشعارات

**الملفات المطلوبة:**
- `questions/ask.php` - طرح سؤال (Folder: questions/)
- `questions/answer.php` - الإجابة على سؤال (Folder: questions/)
- `appointments/book.php` - حجز موعد (Folder: appointments/)
- `appointments/view.php` - عرض المواعيد (Folder: appointments/)
- `notifications/view.php` - عرض الإشعارات (Folder: notifications/)
- `includes/notification_functions.php` - دوال الإشعارات (Folder: includes/)

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
- [ ] تنفيذ قاعدة البيانات
- [ ] إنشاء العلاقات بين الجداول
- [ ] التحقق من سلامة البيانات
- [ ] إنشاء الـ Foreign Keys
- [ ] اختبار العلاقات
- [ ] عمل نسخ احتياطية

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
- [ ] تصميم الواجهة الأساسية
- [ ] تصميم متجاوب (Responsive)
- [ ] ربط جميع الأجزاء
- [ ] اختبار النظام
- [ ] إصلاح الأخطاء
- [ ] تحسين الأداء

**الملفات المطلوبة:**
- `css/style.css` - التنسيق الأساسي (Folder: css/)
- `css/responsive.css` - التصميم المتجاوب (Folder: css/)
- `js/main.js` - JavaScript رئيسي (Folder: js/)
- `includes/header.php` - الهيدر (Folder: includes/)
- `includes/footer.php` - الفوتر (Folder: includes/)
- `index.php` - الصفحة الرئيسية (Folder: root/)

**مميزات التصميم:**
- بسيط وسهل الاستخدام
- مناسب للطلبة
- يعمل على الموبايل والكمبيوتر
- ألوان مريحة للعين
- أيقونات واضحة

---

## المهام الإضافية (إذا لزم الأمر)

### للجميع:
- [ ] اختبار الأمان
- [ ] تحسين الأداء
- [ ] كتابة التوثيق
- [ ] تجربة المستخدم

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
