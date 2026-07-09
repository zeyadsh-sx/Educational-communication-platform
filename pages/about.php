<?php
require_once __DIR__ . '/../includes/nagah/layout.php';
nagahPageStart('من نحن | أكاديمية ماستر');
?>

<section class="w-full py-20">
    <div class="max-w-4xl mx-auto px-5">
        <div class="text-center mb-12">
            <span class="tag-pill inline-block px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide">عن الأكاديمية</span>
            <h1 class="display font-semibold text-3xl sm:text-4xl mt-4">أكاديمية ماستر</h1>
            <p class="mt-3 text-slate-500">منصة تعليمية رائدة للثانوية العامة ونظام البكالوريا</p>
        </div>
        <div class="grid sm:grid-cols-3 gap-6 mb-10">
            <div class="glass rounded-3xl p-6 text-center lift">
                <i data-lucide="eye" class="mx-auto mb-3 text-blue-600"></i>
                <h3 class="font-bold">رؤيتنا</h3>
                <p class="text-sm text-slate-500 mt-2">أن نكون المركز التعليمي الأول في مصر</p>
            </div>
            <div class="glass rounded-3xl p-6 text-center lift">
                <i data-lucide="target" class="mx-auto mb-3 text-amber-500"></i>
                <h3 class="font-bold">مهمتنا</h3>
                <p class="text-sm text-slate-500 mt-2">تعليم عالي الجودة بمرونة ومتابعة مستمرة</p>
            </div>
            <div class="glass rounded-3xl p-6 text-center lift">
                <i data-lucide="heart" class="mx-auto mb-3 text-red-500"></i>
                <h3 class="font-bold">قيمنا</h3>
                <p class="text-sm text-slate-500 mt-2">الجودة، الالتزام، والابتكار</p>
            </div>
        </div>
        <div class="glass rounded-3xl p-8">
            <h2 class="display font-semibold text-xl mb-4" style="color:#2563EB">لماذا أكاديمية ماستر؟</h2>
            <ul class="space-y-3 text-slate-600">
                <li class="flex items-start gap-2"><i data-lucide="check-circle" class="text-green-600 shrink-0 mt-0.5" style="width:18px"></i> معلمون على أعلى مستوى</li>
                <li class="flex items-start gap-2"><i data-lucide="check-circle" class="text-green-600 shrink-0 mt-0.5" style="width:18px"></i> منهج متكامل: شرح، تمارين، امتحانات</li>
                <li class="flex items-start gap-2"><i data-lucide="check-circle" class="text-green-600 shrink-0 mt-0.5" style="width:18px"></i> دروس مسجلة وحصص مباشرة</li>
                <li class="flex items-start gap-2"><i data-lucide="check-circle" class="text-green-600 shrink-0 mt-0.5" style="width:18px"></i> لوحة تحكم للطالب وولي الأمر</li>
            </ul>
        </div>
    </div>
</section>

<?php nagahPageEnd(); ?>
