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
        ['lucide' => 'sigma', 'title' => 'الرياضيات', 'desc' => 'جبر، هندسة، وتفاضل — شرح شامل للثانوية والبكالوريا', 'gradient' => 'linear-gradient(135deg,#2563EB,#60A5FA)'],
        ['lucide' => 'atom', 'title' => 'الفيزياء', 'desc' => 'ميكانيكا، كهرباء، وموجات مع تجارب تفاعلية', 'gradient' => 'linear-gradient(135deg,#F59E0B,#fbbf24)'],
        ['lucide' => 'flask-conical', 'title' => 'الكيمياء', 'desc' => 'كيمياء عضوية وغير عضوية بأسلوب مبسّط', 'gradient' => 'linear-gradient(135deg,#0ea5e9,#60A5FA)'],
        ['lucide' => 'leaf', 'title' => 'الأحياء', 'desc' => 'علم الأحياء، الوراثة، والبيولوجيا الجزيئية', 'gradient' => 'linear-gradient(135deg,#16a34a,#4ade80)'],
        ['lucide' => 'book-open-text', 'title' => 'اللغة العربية', 'desc' => 'نحو، بلاغة، أدب، ونصوص امتحانات', 'gradient' => 'linear-gradient(135deg,#7c3aed,#a78bfa)'],
        ['lucide' => 'languages', 'title' => 'اللغة الإنجليزية', 'desc' => 'Grammar, reading & writing for exams', 'gradient' => 'linear-gradient(135deg,#2563EB,#818cf8)'],
        ['lucide' => 'scroll', 'title' => 'التاريخ', 'desc' => 'تاريخ مصر والعرب والحضارات', 'gradient' => 'linear-gradient(135deg,#d97706,#f59e0b)'],
        ['lucide' => 'globe-2', 'title' => 'الجغرافيا', 'desc' => 'جغرافيا طبيعية وبشرية وخرائط', 'gradient' => 'linear-gradient(135deg,#0891b2,#22d3ee)'],
        ['lucide' => 'cpu', 'title' => 'علوم الحاسب', 'desc' => 'Computer Science & programming للبكالوريا', 'gradient' => 'linear-gradient(135deg,#4338ca,#6366f1)'],
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
        ['name' => 'أ. محمد حسن', 'subject' => 'الرياضيات', 'exp' => '15 سنة خبرة', 'rating' => '4.9', 'photo' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=500&h=400&fit=crop'],
        ['name' => 'أ. أحمد سالم', 'subject' => 'الفيزياء', 'exp' => '12 سنة خبرة', 'rating' => '4.8', 'photo' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=500&h=400&fit=crop'],
        ['name' => 'د. سارة محمود', 'subject' => 'الكيمياء', 'exp' => '10 سنوات خبرة', 'rating' => '4.9', 'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=500&h=400&fit=crop'],
        ['name' => 'أ. ياسمين أحمد', 'subject' => 'الإنجليزية', 'exp' => '8 سنوات خبرة', 'rating' => '4.7', 'photo' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=500&h=400&fit=crop'],
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
