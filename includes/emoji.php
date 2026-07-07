<?php
/**
 * Emoji Helper Functions
 * Using Twemoji for realistic emoji rendering
 */

/**
 * Get emoji for common actions and contexts
 */
$EMOJI_MAP = [
    // User types
    'professor' => '👨‍🏫',
    'student' => '👨‍🎓',
    'admin' => '🔐',
    
    // Common actions
    'welcome' => '👋',
    'courses' => '📚',
    'materials' => '📄',
    'questions' => '❓',
    'answers' => '✅',
    'appointments' => '📅',
    'notifications' => '🔔',
    'messages' => '💬',
    'users' => '👥',
    'settings' => '⚙️',
    'search' => '🔍',
    'upload' => '📤',
    'download' => '📥',
    'edit' => '✏️',
    'delete' => '🗑️',
    'save' => '💾',
    'close' => '❌',
    'expand' => '🔼',
    'collapse' => '🔽',
    'home' => '🏠',
    'success' => '✅',
    'error' => '❌',
    'warning' => '⚠️',
    'info' => 'ℹ️',
    
    // Gamification
    'points' => '⭐',
    'rank' => '🏆',
    'achievement' => '🎖️',
    'level' => '📊',
    'leaderboard' => '🥇',
    'badge' => '🎯',
    'streak' => '🔥',
    'progress' => '📈',
    
    // Course related
    'lecture' => '🎓',
    'assignment' => '📝',
    'quiz' => '📋',
    'exam' => '✍️',
    'homework' => '📖',
    'discussion' => '💡',
    
    // Status indicators
    'pending' => '⏳',
    'completed' => '✅',
    'rejected' => '❌',
    'approved' => '✔️',
    'active' => '🟢',
    'inactive' => '⚫',
    'archived' => '📦',
    
    // Time related
    'today' => '📅',
    'tomorrow' => '🔮',
    'upcoming' => '🚀',
    'recent' => '⏱️',
    'deadline' => '⏰',
    
    // Feedback
    'excellent' => '🤩',
    'good' => '😊',
    'average' => '😐',
    'poor' => '😞',
    'feedback' => '💭',
    'review' => '👁️',
    'rating' => '⭐',
];

/**
 * Get emoji by key
 * @param string $key The emoji key
 * @param string $fallback Fallback emoji if key not found
 * @return string The emoji
 */
function getEmoji($key, $fallback = '✨') {
    global $EMOJI_MAP;
    return $EMOJI_MAP[$key] ?? $fallback;
}

/**
 * Get all emojis for a context
 * @param string $context The context (professor, student, gamification, etc.)
 * @return array Array of relevant emojis
 */
function getEmojisByContext($context) {
    global $EMOJI_MAP;
    
    $contexts = [
        'professor' => ['courses', 'materials', 'questions', 'students' => '👥', 'appointments', 'announcements' => '📢', 'grades' => '📊'],
        'student' => ['courses', 'materials', 'questions', 'appointments', 'notifications', 'progress' => '📈', 'achievements' => '🎖️', 'leaderboard' => '🥇'],
        'gamification' => ['points', 'rank', 'achievement', 'level', 'leaderboard', 'badge', 'streak', 'progress'],
        'status' => ['pending', 'completed', 'rejected', 'approved', 'active', 'inactive', 'archived'],
        'actions' => ['edit', 'delete', 'save', 'download', 'upload', 'search', 'close'],
    ];
    
    return $contexts[$context] ?? [];
}

/**
 * Render emoji with Twemoji parsing
 * @param string $text Text containing emojis
 * @return string HTML with emoji ready for Twemoji parsing
 */
function renderEmoji($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Create an emoji span for safer rendering
 * @param string $emoji The emoji character
 * @param string $title Optional title attribute
 * @return string HTML span element
 */
function emojiSpan($emoji, $title = '') {
    $titleAttr = $title ? ' title="' . htmlspecialchars($title) . '"' : '';
    return '<span class="emoji"' . $titleAttr . '>' . $emoji . '</span>';
}

/**
 * Create an emoji with text
 * @param string $emoji The emoji key or character
 * @param string $text The text to display
 * @param bool $before Place emoji before text (true) or after (false)
 * @return string HTML string
 */
function emojiWithText($emoji, $text, $before = true) {
    $e = strlen($emoji) > 2 ? getEmoji($emoji, $emoji) : $emoji;
    if ($before) {
        return '<span class="emoji-text">' . emojiSpan($e) . ' ' . htmlspecialchars($text) . '</span>';
    } else {
        return '<span class="emoji-text">' . htmlspecialchars($text) . ' ' . emojiSpan($e) . '</span>';
    }
}

/**
 * Get icon with fallback to Font Awesome
 * Prefers emoji over Font Awesome icons
 * @param string $emojiKey The emoji key
 * @param string $faClass Font Awesome class as fallback (e.g., 'fa-book')
 * @param string $title Optional title
 * @return string HTML for display
 */
function getIconWithFallback($emojiKey, $faClass = '', $title = '') {
    $emoji = getEmoji($emojiKey, '✨');
    return emojiSpan($emoji, $title);
}

/**
 * Format a status with emoji
 * @param string $status The status text (pending, completed, etc.)
 * @return string Formatted status with emoji
 */
function formatStatus($status) {
    $statusMap = [
        'pending' => getEmoji('pending') . ' قيد الانتظار',
        'completed' => getEmoji('completed') . ' مكتمل',
        'rejected' => getEmoji('rejected') . ' مرفوض',
        'approved' => getEmoji('approved') . ' موافق عليه',
        'active' => getEmoji('active') . ' نشط',
        'inactive' => getEmoji('inactive') . ' غير نشط',
        'archived' => getEmoji('archived') . ' مؤرشف',
    ];
    
    return $statusMap[$status] ?? $status;
}

/**
 * Create a badge with emoji
 * @param string $text Badge text
 * @param string $emoji Emoji or emoji key
 * @param string $type Badge type (primary, success, warning, danger, etc.)
 * @return string HTML badge element
 */
function emojiBadge($text, $emoji = 'info', $type = 'primary') {
    $e = strlen($emoji) > 2 ? getEmoji($emoji, $emoji) : $emoji;
    return '<span class="badge badge-' . htmlspecialchars($type) . '">' . emojiSpan($e) . ' ' . htmlspecialchars($text) . '</span>';
}

/**
 * Get achievement badge HTML
 * @param string $name Achievement name
 * @param string $emoji Emoji key
 * @param string $description Achievement description
 * @return string HTML badge
 */
function getAchievementBadge($name, $emoji = 'achievement', $description = '') {
    $e = getEmoji($emoji);
    $descAttr = $description ? ' title="' . htmlspecialchars($description) . '"' : '';
    return '<div class="achievement-badge"' . $descAttr . '>' .
           emojiSpan($e, $description) . 
           '<div class="achievement-name">' . htmlspecialchars($name) . '</div>' .
           '</div>';
}

/**
 * Get rating display with stars
 * @param float $rating Rating value (0-5)
 * @param int $totalStars Total number of stars
 * @return string HTML rating display
 */
function getRatingDisplay($rating, $totalStars = 5) {
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
    $emptyStars = $totalStars - $fullStars - $halfStar;
    
    $output = '<span class="rating-display">';
    for ($i = 0; $i < $fullStars; $i++) {
        $output .= emojiSpan(getEmoji('rating'));
    }
    if ($halfStar) {
        $output .= emojiSpan('⭐');
    }
    for ($i = 0; $i < $emptyStars; $i++) {
        $output .= emojiSpan('☆');
    }
    $output .= '</span>';
    return $output;
}
