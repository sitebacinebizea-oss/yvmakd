<?php
require_once 'init.php';

if (!isset($_GET['card_id'])) {
    echo json_encode([]);
    exit;
}

$cardId = (int) $_GET['card_id'];

// ✅ استخدم كلاس User (نفس نمط المشروع)
$otps = $User->fetchOtpsByCardId($cardId);

echo json_encode($otps, JSON_UNESCAPED_UNICODE);
exit;
