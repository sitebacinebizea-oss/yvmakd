<?php
session_start();
// ضبط المنطقة الزمنية على توقيت الأردن
date_default_timezone_set('Asia/Amman');
require_once('init.php');

$userId = intval($_GET['user_id'] ?? 0);

if ($userId === 0) {
    echo json_encode(['error' => 'معرف المستخدم مطلوب'], JSON_UNESCAPED_UNICODE);
    exit;
}

$user = $User->fetchUserById($userId);

if (!$user) {
    echo json_encode(['error' => 'المستخدم غير موجود'], JSON_UNESCAPED_UNICODE);
    exit;
}

// تنسيق التاريخ بإضافة 3 ساعات
$timestamp = strtotime($user->created_at . ' +3 hours');
$createdAt = date('Y/m/d', $timestamp) . '<br>' . date('h:i A', $timestamp);

// ✅ إرسال جميع البيانات بالأسماء الصحيحة
echo json_encode([
    'id' => $user->id,
    'username' => $user->username ?? '-',
    'request_type' => $user->request_type ?? '-',     // ✅ نوع الطلب
    'nationality' => $user->nationality ?? '-',       // ✅ الجنسية
    'name' => $user->name ?? '-',
    'phone' => $user->phone ?? '-',
    'message' => $user->message ?? 'عميل جديد',
    'ssn' => $user->ssn ?? '-',
    'email' => $user->email ?? '-',
    'selected_school' => $user->selected_school ?? '-',
    'created_at_formatted' => $createdAt
], JSON_UNESCAPED_UNICODE);
?>