<?php
/**
 * بيانات أكاديمية ماستر الثابتة للعرض
 */

function getAcademyFeatures(): array
{
    return [
        ['icon' => 'fa-chalkboard-teacher', 'title' => 'معلمون محترفون', 'desc' => 'نخبة من أفضل المدرسين المتخصصين في الثانوية العامة والبكالوريا'],
        ['icon' => 'fa-video', 'title' => 'دروس مسجلة', 'desc' => 'شاهد الدروس في أي وقت ومن أي مكان بجودة عالية'],
        ['icon' => 'fa-broadcast-tower', 'title' => 'حصص مباشرة', 'desc' => 'تفاعل مباشر مع المعلمين في حصص أونلاين حية'],
        ['icon' => 'fa-file-alt', 'title' => 'امتحانات تدريبية', 'desc' => 'اختبارات شاملة مع نتائج فورية ومراجعة الإجابات'],
        ['icon' => 'fa-tasks', 'title' => 'متابعة الواجبات', 'desc' => 'نظام متكامل لتسليم الواجبات وتقييمها'],
        ['icon' => 'fa-chart-line', 'title' => 'لوحة تحكم الطالب', 'desc' => 'تابع تقدمك الدراسي وإحصائياتك في مكان واحد'],
    ];
}

function getAcademyCourses(): array
{
    return [
        'general' => [
            'title' => 'الثانوية العامة',
            'courses' => [
                ['name' => 'الرياضيات', 'teacher' => 'أ. محمد حسن', 'grade' => 'الصف الثالث', 'desc' => 'شرح شامل للجبر والهندسة والتفاضل', 'lessons' => 48, 'icon' => 'fa-square-root-alt', 'color' => '#2563EB'],
                ['name' => 'الفيزياء', 'teacher' => 'أ. أحمد سالم', 'grade' => 'الصف الثالث', 'desc' => 'ميكانيكا، كهرباء، وموجات', 'lessons' => 42, 'icon' => 'fa-atom', 'color' => '#3B82F6'],
                ['name' => 'الكيمياء', 'teacher' => 'د. سارة محمود', 'grade' => 'الصف الثالث', 'desc' => 'كيمياء عضوية وغير عضوية', 'lessons' => 40, 'icon' => 'fa-flask', 'color' => '#059669'],
                ['name' => 'الأحياء', 'teacher' => 'د. نور الدين', 'grade' => 'الصف الثالث', 'desc' => 'علم الأحياء والوراثة', 'lessons' => 38, 'icon' => 'fa-dna', 'color' => '#10B981'],
                ['name' => 'اللغة العربية', 'teacher' => 'أ. فاطمة علي', 'grade' => 'الصف الثالث', 'desc' => 'نحو، بلاغة، ونصوص', 'lessons' => 36, 'icon' => 'fa-book-open', 'color' => '#F59E0B'],
                ['name' => 'اللغة الإنجليزية', 'teacher' => 'أ. ياسمين أحمد', 'grade' => 'الصف الثالث', 'desc' => 'قواعد، قراءة، وكتابة', 'lessons' => 44, 'icon' => 'fa-language', 'color' => '#8B5CF6'],
                ['name' => 'اللغة الفرنسية', 'teacher' => 'أ. كريم لطفي', 'grade' => 'الصف الثالث', 'desc' => 'Français pour le baccalauréat', 'lessons' => 32, 'icon' => 'fa-globe-europe', 'color' => '#EC4899'],
                ['name' => 'اللغة الألمانية', 'teacher' => 'أ. هاني كمال', 'grade' => 'الصف الثالث', 'desc' => 'Deutsch für Schüler', 'lessons' => 30, 'icon' => 'fa-globe', 'color' => '#6366F1'],
                ['name' => 'التاريخ', 'teacher' => 'أ. محمود فاروق', 'grade' => 'الصف الثالث', 'desc' => 'تاريخ مصر والعالم', 'lessons' => 28, 'icon' => 'fa-landmark', 'color' => '#D97706'],
                ['name' => 'الجغرافيا', 'teacher' => 'أ. رania سعيد', 'grade' => 'الصف الثالث', 'desc' => 'جغرافيا طبيعية وبشرية', 'lessons' => 26, 'icon' => 'fa-globe-africa', 'color' => '#0EA5E9'],
            ],
        ],
        'baccalaureate' => [
            'title' => 'نظام البكالوريا',
            'courses' => [
                ['name' => 'رياضيات متقدمة', 'teacher' => 'أ. خالد منصور', 'grade' => 'بكالوريا', 'desc' => 'Calculus وStatistics', 'lessons' => 52, 'icon' => 'fa-calculator', 'color' => '#2563EB'],
                ['name' => 'الفيزياء', 'teacher' => 'أ. أحمد سالم', 'grade' => 'بكالوريا', 'desc' => 'فيزياء متقدمة للبكالوريا', 'lessons' => 45, 'icon' => 'fa-atom', 'color' => '#3B82F6'],
                ['name' => 'الكيمياء', 'teacher' => 'د. سارة محمود', 'grade' => 'بكالوريا', 'desc' => 'كيمياء IB', 'lessons' => 42, 'icon' => 'fa-flask', 'color' => '#059669'],
                ['name' => 'الأحياء', 'teacher' => 'د. نور الدين', 'grade' => 'بكالوريا', 'desc' => 'Biology HL', 'lessons' => 40, 'icon' => 'fa-dna', 'color' => '#10B981'],
                ['name' => 'إدارة الأعمال', 'teacher' => 'أ. هبة شريف', 'grade' => 'بكالوريا', 'desc' => 'Business Management', 'lessons' => 35, 'icon' => 'fa-briefcase', 'color' => '#F59E0B'],
                ['name' => 'علوم الحاسب', 'teacher' => 'أ. عمر تامر', 'grade' => 'بكالوريا', 'desc' => 'Computer Science IB', 'lessons' => 38, 'icon' => 'fa-laptop-code', 'color' => '#6366F1'],
                ['name' => 'اللغة الإنجليزية', 'teacher' => 'أ. ياسمين أحمد', 'grade' => 'بكالوريا', 'desc' => 'English A & B', 'lessons' => 46, 'icon' => 'fa-language', 'color' => '#8B5CF6'],
            ],
        ],
    ];
}

function getAcademyTeachers(): array
{
    return [
        ['name' => 'أ. محمد حسن', 'subject' => 'الرياضيات', 'experience' => '15 سنة', 'bio' => 'خبير في تدريس الرياضيات للثانوية العامة والبكالوريا، حاصل على ماجستير في الرياضيات التطبيقية', 'rating' => 4.9, 'students' => 850, 'avatar' => 'م'],
        ['name' => 'أ. أحمد سالم', 'subject' => 'الفيزياء', 'experience' => '12 سنة', 'bio' => 'متخصص في الفيزياء النظرية والتطبيقية، ساعد مئات الطلاب على تحقيق الدرجات النهائية', 'rating' => 4.8, 'students' => 720, 'avatar' => 'أ'],
        ['name' => 'د. سارة محمود', 'subject' => 'الكيمياء', 'experience' => '10 سنوات', 'bio' => 'دكتورة في الكيمياء العضوية، تتميز بأسلوب شرح مبسط وعملي', 'rating' => 4.9, 'students' => 680, 'avatar' => 'س'],
        ['name' => 'أ. ياسمين أحمد', 'subject' => 'اللغة الإنجليزية', 'experience' => '8 سنوات', 'bio' => 'حاصلة على CELTA، متخصصة في تعليم اللغة الإنجليزية للامتحانات الدولية', 'rating' => 4.7, 'students' => 590, 'avatar' => 'ي'],
        ['name' => 'أ. فاطمة علي', 'subject' => 'اللغة العربية', 'experience' => '14 سنة', 'bio' => 'خبيرة في النحو والبلاغة، مؤلفة عدة كتب مساعدة للثانوية العامة', 'rating' => 4.8, 'students' => 640, 'avatar' => 'ف'],
        ['name' => 'أ. عمر تامر', 'subject' => 'علوم الحاسب', 'experience' => '7 سنوات', 'bio' => 'مهندس برمجيات ومدرس IB Computer Science', 'rating' => 4.9, 'students' => 420, 'avatar' => 'ع'],
    ];
}

function getWeeklySchedule(): array
{
    return [
        ['day' => 'السبت', 'subject' => 'الرياضيات', 'teacher' => 'أ. محمد حسن', 'time' => '10:00 - 12:00', 'classroom' => 'قاعة 1', 'online' => false],
        ['day' => 'السبت', 'subject' => 'الفيزياء', 'teacher' => 'أ. أحمد سالم', 'time' => '14:00 - 16:00', 'classroom' => 'أونلاين', 'online' => true],
        ['day' => 'الأحد', 'subject' => 'الكيمياء', 'teacher' => 'د. سارة محمود', 'time' => '10:00 - 12:00', 'classroom' => 'قاعة 2', 'online' => false],
        ['day' => 'الأحد', 'subject' => 'اللغة الإنجليزية', 'teacher' => 'أ. ياسمين أحمد', 'time' => '16:00 - 18:00', 'classroom' => 'أونلاين', 'online' => true],
        ['day' => 'الاثنين', 'subject' => 'الأحياء', 'teacher' => 'د. نور الدين', 'time' => '11:00 - 13:00', 'classroom' => 'قاعة 3', 'online' => false],
        ['day' => 'الاثنين', 'subject' => 'علوم الحاسب', 'teacher' => 'أ. عمر تامر', 'time' => '15:00 - 17:00', 'classroom' => 'معمل الحاسب', 'online' => false],
        ['day' => 'الثلاثاء', 'subject' => 'اللغة العربية', 'teacher' => 'أ. فاطمة علي', 'time' => '10:00 - 12:00', 'classroom' => 'قاعة 1', 'online' => false],
        ['day' => 'الثلاثاء', 'subject' => 'رياضيات متقدمة', 'teacher' => 'أ. خالد منصور', 'time' => '14:00 - 16:00', 'classroom' => 'أونلاين', 'online' => true],
        ['day' => 'الأربعاء', 'subject' => 'التاريخ', 'teacher' => 'أ. محمود فاروق', 'time' => '11:00 - 13:00', 'classroom' => 'قاعة 4', 'online' => false],
        ['day' => 'الأربعاء', 'subject' => 'إدارة الأعمال', 'teacher' => 'أ. هبة شريف', 'time' => '15:00 - 17:00', 'classroom' => 'أونلاين', 'online' => true],
        ['day' => 'الخميس', 'subject' => 'مراجعة شاملة', 'teacher' => 'فريق التدريس', 'time' => '10:00 - 14:00', 'classroom' => 'أونلاين', 'online' => true],
    ];
}

function getSampleExams(): array
{
    return [
        ['title' => 'امتحان الرياضيات - الفصل الأول', 'course' => 'الرياضيات', 'questions' => 30, 'duration' => 60, 'status' => 'available'],
        ['title' => 'اختبار الفيزياء - الميكانيكا', 'course' => 'الفيزياء', 'questions' => 25, 'duration' => 45, 'status' => 'available'],
        ['title' => 'امتحان الكيمياء - العضوية', 'course' => 'الكيمياء', 'questions' => 20, 'duration' => 40, 'status' => 'completed', 'score' => 85],
        ['title' => 'اختبار اللغة الإنجليزية', 'course' => 'الإنجليزية', 'questions' => 35, 'duration' => 50, 'status' => 'completed', 'score' => 92],
    ];
}

function getSampleHomework(): array
{
    return [
        ['title' => 'واجب الجبر - المعادلات', 'course' => 'الرياضيات', 'due' => '2026-07-15', 'status' => 'pending', 'grade' => null],
        ['title' => 'تقرير تجربة الكيمياء', 'course' => 'الكيمياء', 'due' => '2026-07-12', 'status' => 'submitted', 'grade' => null],
        ['title' => 'مقال أدبي', 'course' => 'اللغة العربية', 'due' => '2026-07-10', 'status' => 'graded', 'grade' => 18, 'feedback' => 'عمل ممتاز، حاول تحسين الخاتمة'],
        ['title' => 'مسائل الفيزياء', 'course' => 'الفيزياء', 'due' => '2026-07-08', 'status' => 'graded', 'grade' => 16, 'feedback' => 'جيد جداً، راجع قوانين نيوتن'],
    ];
}

function renderStars(float $rating): string
{
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= floor($rating)) {
            $html .= '<i class="fas fa-star text-warning"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
        } else {
            $html .= '<i class="far fa-star text-warning"></i>';
        }
    }
    return $html;
}
