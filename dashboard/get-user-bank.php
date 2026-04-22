<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'غير مصرح']);
    exit;
}

require_once('init.php');

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['error' => 'معرف المستخدم مطلوب']);
    exit;
}

// جلب بيانات البنوك للمستخدم
$banks = $User->fetchBankLoginsByUserId($userId);

if ($banks === false || empty($banks)) {
    echo json_encode([]);
} else {
    echo json_encode($banks);
}
?>