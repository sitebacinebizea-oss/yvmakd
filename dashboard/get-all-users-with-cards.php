<?php
session_start();
require_once('init.php');

header('Content-Type: application/json; charset=utf-8');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // جلب جميع المستخدمين الذين لديهم بطاقات
    $users = $User->getUsersWithCards();
    
    // إرجاع البيانات كـ JSON
    echo json_encode($users, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>