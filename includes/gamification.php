<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Award points to a student for an action.
 */
function awardPoints($studentId, $action)
{
    $pointsMap = [
        'ask_question' => 10,
        'answer_question' => 20,
        'download_material' => 5,
        'book_appointment' => 15,
        'complete_profile' => 20,
        'daily_login' => 2
    ];

    if (!isset($pointsMap[$action])) return false;

    $points = $pointsMap[$action];
    $pdo = getDB();

    try {
        $stmt = $pdo->prepare("UPDATE users SET points = points + ? WHERE id = ? AND user_type = 'student'");
        $stmt->execute([$points, $studentId]);

        // Check for achievements
        checkAchievements($studentId);

        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get student leaderboard.
 */
function getLeaderboard($limit = 5)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, full_name, points, profile_image 
                           FROM users 
                           WHERE user_type = 'student' 
                           ORDER BY points DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get student rank.
 */
function getStudentRank($studentId)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) + 1 as rank 
                           FROM users 
                           WHERE user_type = 'student' AND points > (SELECT points FROM users WHERE id = ?)");
    $stmt->execute([$studentId]);
    return $stmt->fetchColumn();
}

/**
 * Check and award achievements based on points or actions.
 */
function checkAchievements($studentId)
{
    $pdo = getDB();

    // Get total points
    $stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
    $stmt->execute([$studentId]);
    $points = $stmt->fetchColumn();

    $achievements = [
        ['name' => 'البداية القوية', 'threshold' => 50, 'icon' => 'fa-rocket'],
        ['name' => 'المتعلم النشط', 'threshold' => 200, 'icon' => 'fa-fire'],
        ['name' => 'بطل الكورسات', 'threshold' => 500, 'icon' => 'fa-trophy'],
        ['name' => 'الأسطورة الأكاديمية', 'threshold' => 1000, 'icon' => 'fa-crown']
    ];

    foreach ($achievements as $ach) {
        if ($points >= $ach['threshold']) {
            // Check if already earned
            $check = $pdo->prepare("SELECT id FROM student_achievements WHERE student_id = ? AND achievement_name = ?");
            $check->execute([$studentId, $ach['name']]);
            if (!$check->fetch()) {
                // Award achievement
                $ins = $pdo->prepare("INSERT INTO student_achievements (student_id, achievement_name, achievement_icon) VALUES (?, ?, ?)");
                $ins->execute([$studentId, $ach['name'], $ach['icon']]);

                // Add notification
                $notif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notif->execute([$studentId, "لقد حصلت على وسام جديد: " . $ach['name']]);
            }
        }
    }
}

/**
 * Get student achievements.
 */
function getStudentAchievements($studentId)
{
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM student_achievements WHERE student_id = ? ORDER BY earned_at DESC");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}
