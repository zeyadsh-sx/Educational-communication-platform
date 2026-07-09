<?php
/**
 * Start Nagah-themed public page
 */
function nagahPageStart(string $title): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../includes/functions.php';
    require_once __DIR__ . '/../../includes/nagah_theme.php';
    $GLOBALS['pageTitle'] = $title;
    $pageTitle = $title;
    require __DIR__ . '/head.php';
    require __DIR__ . '/nav.php';
    echo '<main class="min-h-[60vh]">';
}

function nagahPageEnd(): void
{
    echo '</main>';
    require __DIR__ . '/footer.php';
}
