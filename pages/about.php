<?php
require_once __DIR__ . '/../includes/nagah/layout.php';
nagahPageStart('من نحن | أكاديمية ماستر');
$base = nagahBaseUrl();
?>

<!-- Hero -->
<section class="relative w-full overflow-hidden py-20 sm:py-28">
    <span class="blob" style="width:400px;height:400px;background:#60A5FA;top:-120px;right:-100px;"></span>
    <span class="blob" style="width:340px;height:340px;background:#2563EB;bottom:-100px;left:-80px;opacity:.4;"></span>
    <div class="absolute inset-0 grid-dots opacity-60"></div>
    <div class="relative z-10 max-w-4xl mx-auto px-5 text-center">
        <span class="reveal tag-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide" style="animation-delay:.05s">
            <i data-lucide="graduation-cap" style="width:13px;height:13px;"></i> عن الأكاديمية
        </span>
        <h1 class="display reveal mt-5 text-4xl sm:text-5xl font-semibold text-slate-900 leading-tight" style="animation-delay:.15s">
            أكاديمية <span style="color:#2563EB">ماستر</span>
        </h1>
        <p class="reveal mt-5 text-lg text-slate-600 max-w-xl mx-auto leading-relaxed" style="animation-delay:.25s">
            منصة تعليمية رائدة مخصصة لطلاب الثانوية العامة ونظام البكالوريا في مصر — نجمع بين التميز الأكاديمي والتكنولوجيا الحديثة.
        </p>
        <div class="reveal flex justify-center gap-4 mt-8" style="animation-delay:.35s">
            <a href="<?php echo $base; ?>/auth/register.php" class="btn-primary-nagah px-7 py-3.5 rounded-full font-bold shadow-xl hover:-translate-y-0.5 transition-all">سجّل الآن</a>
            <a href="<?php echo $base; ?>/pages/contact.php" class="btn-outline-nagah px-7 py-3.5 rounded-full font-bold transition-all hover:-translate-y-0.5">تواصل معنا</a>
        </div>
    </div>
</section>

<!-- Stats Band -->
<section class="w-full py-2 pb-12">
    <div class="max-w-5xl mx-auto px-5">
        <div class="glass rounded-3xl grid grid-cols-2 lg:grid-cols-4 gap-6 px-6 py-8">
            <?php foreach ([
                ['10,000+','طالب وطالبة','users','#2563EB'],
                ['150+','كورس دراسي','book-open','#F59E0B'],
                ['50+','معلم محترف','user-cog','#16a34a'],
                ['98%','نسبة النجاح','trophy','#7c3aed'],
            ] as [$val,$label,$icon,$color]): ?>
            <div class="text-center reveal">
                <span class="w-10 h-10 rounded-xl mx-auto flex items-center justify-center mb-2" style="background:<?php echo $color; ?>18">
                    <i data-lucide="<?php echo $icon; ?>" style="width:18px;height:18px;color:<?php echo $color; ?>"></i>
                </span>
                <p class="display font-semibold text-3xl" style="color:<?php echo $color; ?>"><?php echo $val; ?></p>
                <p class="text-sm text-slate-500 mt-1"><?php echo $label; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Vision / Mission / Values -->
<section class="w-full py-12">
    <div class="max-w-5xl mx-auto px-5">
        <div class="grid sm:grid-cols-3 gap-6">
            <?php foreach ([
                ['eye','رؤيتنا','#2563EB','rgba(37,99,235,.1)','أن نكون المنصة التعليمية الأولى في مصر، ونصنع جيلاً متميزاً قادراً على المنافسة محلياً وعالمياً.'],
                ['target','مهمتنا','#F59E0B','rgba(245,158,11,.1)','تقديم تعليم عالي الجودة بمرونة ومتابعة مستمرة، وتوفير بيئة دراسية محفزة وتفاعلية.'],
                ['heart','قيمنا','#dc2626','rgba(220,38,38,.1)','الجودة، الالتزام، الابتكار، والتميز — قيم راسخة في كل درس وكل تفاعل مع طلابنا.'],
            ] as [$icon,$title,$color,$bg,$desc]): ?>
            <div class="glass rounded-3xl p-7 text-center lift reveal">
                <span class="w-14 h-14 rounded-2xl mx-auto flex items-center justify-center mb-4" style="background:<?php echo $bg; ?>">
                    <i data-lucide="<?php echo $icon; ?>" style="width:26px;height:26px;color:<?php echo $color; ?>"></i>
                </span>
                <h3 class="display font-semibold text-lg mb-3"><?php echo $title; ?></h3>
                <p class="text-sm text-slate-500 leading-relaxed"><?php echo $desc; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why us -->
<section class="w-full py-12 pb-20">
    <div class="max-w-5xl mx-auto px-5">
        <div class="glass rounded-3xl overflow-hidden">
            <div class="grid lg:grid-cols-2 items-center">
                <div class="p-8 sm:p-12">
                    <span class="tag-pill inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide mb-4">مميزاتنا</span>
                    <h2 class="display font-semibold text-2xl sm:text-3xl mb-6" style="color:#2563EB">لماذا أكاديمية ماستر؟</h2>
                    <ul class="space-y-4">
                        <?php foreach ([
                            ['معلمون على أعلى مستوى من الكفاءة والخبرة','user-check','#16a34a'],
                            ['منهج متكامل: شرح + تمارين + امتحانات + واجبات','list-checks','#2563EB'],
                            ['دروس مسجلة HD وحصص مباشرة تفاعلية','video','#7c3aed'],
                            ['لوحة تحكم متكاملة للطالب وولي الأمر','layout-dashboard','#F59E0B'],
                            ['شهادات إتمام معترف بها لكل كورس','award','#d97706'],
                            ['دعم فني وأكاديمي على مدار الأسبوع','headphones','#0ea5e9'],
                        ] as [$text,$icon,$color]): ?>
                        <li class="flex items-start gap-3">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center shrink-0 mt-0.5" style="background:<?php echo $color; ?>18">
                                <i data-lucide="<?php echo $icon; ?>" style="width:13px;height:13px;color:<?php echo $color; ?>"></i>
                            </span>
                            <span class="text-slate-700 text-sm"><?php echo $text; ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="hidden lg:block relative h-full min-h-[400px]">
                    <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=700&h=600&fit=crop" alt="طلاب" class="w-full h-full object-cover">
                    <div class="absolute inset-0" style="background:linear-gradient(to right,rgba(255,255,255,0.3),transparent)"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php nagahPageEnd(); ?>
