<?php
/** @var string $pageTitle */
$base = nagahBaseUrl();
$lang = $_SESSION['lang'] ?? 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $dir; ?>" class="nagah-theme">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="أكاديمية ماستر — منصة تعليمية لطلاب الثانوية العامة والبكالوريا في مصر">
    <meta name="robots" content="index, follow">
    <title><?php echo htmlspecialchars($pageTitle ?? 'أكاديمية ماستر'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.tailwindcss.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Cairo:wght@400;500;600;700;800&family=El+Messiri:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base; ?>/css/nagah-theme.css">
    <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.0/dist/umd/lucide.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#2563EB', light: '#60A5FA', orange: '#F59E0B' }
                    }
                }
            }
        }
    </script>
</head>
<body class="nagah-theme min-h-screen w-full text-slate-900 overflow-x-hidden bg-white">
