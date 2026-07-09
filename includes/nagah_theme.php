<?php
/**
 * Nagah-style landing theme assets & helpers
 */

function nagahBaseUrl(): string
{
    return getBaseUrl();
}

function getLandingSubjects(): array
{
    return [
        [
            'lucide'   => 'sigma',
            'title'    => 'الرياضيات',
            'desc'     => 'جبر، هندسة، وتفاضل وتكامل — الثانوية العامة والبكالوريا',
            'gradient' => 'linear-gradient(135deg,#2563EB,#60A5FA)',
            'badge'    => 'ثانوي · بكالوريا',
        ],
        [
            'lucide'   => 'atom',
            'title'    => 'الفيزياء',
            'desc'     => 'ميكانيكا، كهرباء، وموجات — شرح تجريبي وتفاعلي',
            'gradient' => 'linear-gradient(135deg,#F59E0B,#fbbf24)',
            'badge'    => 'ثانوي · بكالوريا',
        ],
        [
            'lucide'   => 'flask-conical',
            'title'    => 'الكيمياء',
            'desc'     => 'كيمياء عضوية وغير عضوية — تجارب ومسائل تطبيقية',
            'gradient' => 'linear-gradient(135deg,#0ea5e9,#38bdf8)',
            'badge'    => 'ثانوي · بكالوريا',
        ],
        [
            'lucide'   => 'dna',
            'title'    => 'الأحياء',
            'desc'     => 'علم الأحياء والوراثة والبيولوجيا الجزيئية — ثانوي',
            'gradient' => 'linear-gradient(135deg,#16a34a,#4ade80)',
            'badge'    => 'ثانوي · بكالوريا',
        ],
        [
            'lucide'   => 'book-open-text',
            'title'    => 'اللغة العربية',
            'desc'     => 'نحو، بلاغة، أدب، وتحليل نصوص الامتحانات',
            'gradient' => 'linear-gradient(135deg,#7c3aed,#a78bfa)',
            'badge'    => 'ثانوي عام',
        ],
        [
            'lucide'   => 'languages',
            'title'    => 'اللغة الإنجليزية',
            'desc'     => 'Grammar, reading, writing & exam techniques',
            'gradient' => 'linear-gradient(135deg,#2563EB,#818cf8)',
            'badge'    => 'ثانوي · بكالوريا',
        ],
        [
            'lucide'   => 'landmark',
            'title'    => 'التاريخ',
            'desc'     => 'تاريخ مصر والعرب والحضارات — تحليل ومراجعة',
            'gradient' => 'linear-gradient(135deg,#d97706,#f59e0b)',
            'badge'    => 'ثانوي عام',
        ],
        [
            'lucide'   => 'globe-2',
            'title'    => 'الجغرافيا',
            'desc'     => 'جغرافيا طبيعية وبشرية وخرائط رقمية تفاعلية',
            'gradient' => 'linear-gradient(135deg,#0891b2,#22d3ee)',
            'badge'    => 'ثانوي عام',
        ],
        [
            'lucide'   => 'cpu',
            'title'    => 'علوم الحاسب',
            'desc'     => 'Computer Science, Python & IB CS للبكالوريا',
            'gradient' => 'linear-gradient(135deg,#4338ca,#6366f1)',
            'badge'    => 'بكالوريا',
        ],
    ];
}

function getLandingFeatures(): array
{
    return [
        ['lucide' => 'play-circle', 'color' => '#2563EB', 'title' => 'دروس مسجلة', 'desc' => 'شاهد المحاضرات في أي وقت بجودة HD'],
        ['lucide' => 'video', 'color' => '#F59E0B', 'title' => 'حصص مباشرة', 'desc' => 'تفاعل مباشر مع المعلمين أونلاين'],
        ['lucide' => 'file-check-2', 'color' => '#0ea5e9', 'title' => 'امتحانات تدريبية', 'desc' => 'اختبارات فورية مع مراجعة الإجابات'],
        ['lucide' => 'notebook-pen', 'color' => '#7c3aed', 'title' => 'متابعة الواجبات', 'desc' => 'تسليم وتقييم وملاحظات المعلم'],
        ['lucide' => 'trending-up', 'color' => '#16a34a', 'title' => 'تتبع التقدم', 'desc' => 'إحصائيات واضحة لتطورك الدراسي'],
        ['lucide' => 'award', 'color' => '#d97706', 'title' => 'شهادات إتمام', 'desc' => 'احصل على شهادات معترف بها'],
        ['lucide' => 'layout-dashboard', 'color' => '#2563EB', 'title' => 'لوحة الطالب', 'desc' => 'كل مواردك في مكان واحد'],
    ];
}

function getLandingTeachers(): array
{
    return [
        [
            'name'    => 'أ. محمد حسن',
            'subject' => 'الرياضيات — ثانوية عامة وبكالوريا',
            'exp'     => '15 سنة خبرة',
            'rating'  => '4.9',
            // معلم يشرح على السبورة في فصل دراسي
            'photo'   => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=500&h=400&fit=crop',
        ],
        [
            'name'    => 'أ. أحمد سالم',
            'subject' => 'الفيزياء — ثانوية عامة',
            'exp'     => '12 سنة خبرة',
            'rating'  => '4.8',
            // معلم يستخدم جهاز عرض في الفصل
            'photo'   => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=500&h=400&fit=crop',
        ],
        [
            'name'    => 'د. سارة محمود',
            'subject' => 'الكيمياء — بكالوريا',
            'exp'     => '10 سنوات خبرة',
            'rating'  => '4.9',
            // معلمة في مختبر/فصل دراسي
            'photo'   => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=500&h=400&fit=crop',
        ],
        [
            'name'    => 'أ. ياسمين أحمد',
            'subject' => 'اللغة الإنجليزية — ثانوية وبكالوريا',
            'exp'     => '8 سنوات خبرة',
            'rating'  => '4.7',
            // معلمة أمام مجموعة طلاب
            'photo'   => 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=500&h=400&fit=crop',
        ],
    ];
}

function renderLucideStars(): string
{
    $stars = '';
    for ($i = 0; $i < 5; $i++) {
        $stars .= '<i data-lucide="star" style="width:16px;height:16px;fill:currentColor;"></i>';
    }
    return $stars;
}
