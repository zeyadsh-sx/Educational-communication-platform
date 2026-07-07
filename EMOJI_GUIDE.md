# Emoji Usage Guide - دليل استخدام الايموجيز

## نظرة عامة
تم تحديث المنصة لاستخدام **Twemoji** لعرض ايموجيز واقعية وموحدة. يتم تحميل المكتبة تلقائياً من CDN في ملف `header.php`.

## كيفية الاستخدام

### 1. استخدام دوال Helper

في أي صفحة PHP، تأكد من تضمين `header.php`:

```php
<?php
require_once __DIR__ . '/../includes/header.php';
?>
```

هذا يضمن تحميل:
- `emoji.php` تلقائياً
- مكتبة Twemoji من CDN
- أسلوب الايموجيز

### 2. الدوال المتاحة

#### الحصول على ايموجي معين
```php
<?php
echo getEmoji('welcome');      // 👋
echo getEmoji('courses');      // 📚
echo getEmoji('points');       // ⭐
echo getEmoji('rank');         // 🏆
?>
```

#### إنشاء span للايموجي
```php
<?php
echo emojiSpan('👋');          // <span class="emoji">👋</span>
echo emojiSpan('📚', 'Books'); // <span class="emoji" title="Books">📚</span>
?>
```

#### دمج الايموجي مع النص
```php
<?php
echo emojiWithText('welcome', 'مرحباً');     // 👋 مرحباً
echo emojiWithText('courses', 'الكورسات', false); // الكورسات 📚
?>
```

#### إنشاء badge مع ايموجي
```php
<?php
echo emojiBadge('5 نقاط', 'points', 'primary');
echo emojiBadge('مكتمل', 'success', 'success');
?>
```

#### عرض شارات الإنجاز
```php
<?php
echo getAchievementBadge('البداية القوية', 'rocket', 'لقد بدأت رحلتك بقوة!');
?>
```

#### عرض التقييمات بنجوم
```php
<?php
echo getRatingDisplay(4.5, 5); // ⭐⭐⭐⭐☆
?>
```

### 3. أمثلة عملية

#### في Dashboard
```php
<div class="stat-card">
    <h3><?php echo getEmoji('courses'); ?> الكورسات</h3>
    <p><?php echo count($courses); ?></p>
</div>
```

#### في الإعلانات
```php
<div class="announcement">
    <h2><?php echo emojiWithText('notifications', 'إعلان جديد'); ?></h2>
    <p><?php echo htmlspecialchars($announcement['content']); ?></p>
</div>
```

#### في الإشعارات
```php
<?php
$notifications = getNotifications($userId);
foreach ($notifications as $notif) {
    echo '<div class="notification">';
    echo getEmoji('notifications') . ' ';
    echo htmlspecialchars($notif['message']);
    echo '</div>';
}
?>
```

## معادلة الايموجيز

### أنواع المستخدمين
| الدالة | الايموجي | الاستخدام |
|--------|----------|-----------|
| `getEmoji('professor')` | 👨‍🏫 | للأساتذة |
| `getEmoji('student')` | 👨‍🎓 | للطلاب |
| `getEmoji('admin')` | 🔐 | للإدارة |

### الإجراءات
| الدالة | الايموجي | الاستخدام |
|--------|----------|-----------|
| `getEmoji('welcome')` | 👋 | الترحيب |
| `getEmoji('courses')` | 📚 | الكورسات |
| `getEmoji('materials')` | 📄 | المواد |
| `getEmoji('questions')` | ❓ | الأسئلة |
| `getEmoji('answers')` | ✅ | الإجابات |
| `getEmoji('appointments')` | 📅 | المواعيد |
| `getEmoji('notifications')` | 🔔 | الإشعارات |
| `getEmoji('messages')` | 💬 | الرسائل |
| `getEmoji('users')` | 👥 | المستخدمون |
| `getEmoji('settings')` | ⚙️ | الإعدادات |

### النقاط والترتيب
| الدالة | الايموجي | الاستخدام |
|--------|----------|-----------|
| `getEmoji('points')` | ⭐ | النقاط |
| `getEmoji('rank')` | 🏆 | الترتيب |
| `getEmoji('achievement')` | 🎖️ | الإنجازات |
| `getEmoji('leaderboard')` | 🥇 | لوحة الشرف |
| `getEmoji('badge')` | 🎯 | الشارات |
| `getEmoji('streak')` | 🔥 | الاستمرارية |

### حالات الحالة
| الدالة | الايموجي | الاستخدام |
|--------|----------|-----------|
| `getEmoji('success')` | ✅ | نجاح |
| `getEmoji('error')` | ❌ | خطأ |
| `getEmoji('warning')` | ⚠️ | تحذير |
| `getEmoji('info')` | ℹ️ | معلومة |
| `getEmoji('pending')` | ⏳ | في الانتظار |
| `getEmoji('completed')` | ✅ | مكتمل |

## تخصيص الايموجيز

إذا أردت إضافة ايموجي جديد، عدّل ملف `includes/emoji.php`:

```php
<?php
$EMOJI_MAP = [
    'your_key' => '🎉',
    // ...
];
?>
```

ثم استخدمه:
```php
<?php
echo getEmoji('your_key'); // 🎉
?>
```

## الملفات المرتبطة

- **`includes/emoji.php`**: مكتبة الايموجيز الرئيسية
- **`includes/header.php`**: يتضمن emoji.php وTwemoji CDN
- **`css/style.css`**: تنسيقات الايموجيز (إذا لزم الأمر)

## نصائح مهمة

1. **الأداء**: Twemoji يتم تحميله من CDN، لذا تأكد من وجود اتصال إنترنت
2. **التوافقية**: الايموجيز تظهر بنفس الشكل على جميع المتصفحات والأنظمة
3. **الترجمة**: استخدم `__('key')` من ملفات اللغة مع الايموجيز
4. **الصيانة**: عند تحديث الايموجيز، غيّر فقط في `includes/emoji.php`

## استكشاف الأخطاء

### الايموجيز لا تظهر بشكل صحيح
- تحقق من أن `header.php` محمّل
- تأكد من وجود اتصال إنترنت (لتحميل Twemoji من CDN)
- تحقق من console في متصفحك للأخطاء

### الايموجيز تظهر بشكل مختلف على أجهزة مختلفة
هذا طبيعي! نحن نستخدم Twemoji لتوحيد الايموجيز، لكن تتصفحات أخرى قد تستخدم نسختها الخاصة.

### رسالة خطأ في PHP
تأكد من أن `includes/emoji.php` موجود وأن `header.php` يتضمنه.

---

**ملاحظة**: تم اختبار جميع الدوال والايموجيز على الإصدار PHP 7.4 والأعلى.
