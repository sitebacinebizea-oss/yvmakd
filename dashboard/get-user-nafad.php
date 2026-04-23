<?php
session_start();
require_once('init.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['user_id'])) {
    echo json_encode([]);
    exit;
}

$userId = intval($_GET['user_id']);

try {
    $user = new User();
    
    // 1️⃣ جلب بيانات من nafad_logs (الجدول القديم)
    $nafadLogs = $user->fetchNafadLogsByUserId($userId);
    
    // 2️⃣ جلب رموز التحقق من nafad_codes (الجدول الجديد)
    $nafadCodes = $user->fetchNafadCodesByClientId($userId);
    
    // 3️⃣ دمج البيانات
    $result = [
        'logs' => $nafadLogs ?: [],
        'codes' => $nafadCodes ?: []
    ];
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error in get-user-nafad.php: " . $e->getMessage());
    echo json_encode(['logs' => [], 'codes' => []]);
}