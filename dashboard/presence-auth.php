<?php
require_once('init.php'); // تأكد أن ملف init يهيء الجلسة ويتضمن كلاس الـ User

header('Content-Type: application/json');

if (!$User->isLoggedIn()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// بيانات Pusher
$options = [
    'cluster' => 'ap2',
    'useTLS' => true
];

$pusher = new Pusher\Pusher(
    'a56388ee6222f6c5fb86',
    '4c77061f4115303aac58',
    '1973588',
    $options
);

// نستخدم ID المستخدم المسجل للدخول
$user_id = $_SESSION['userSession'];
$user_info = $User->getUserById($user_id); // تأكد أن عندك هذه الدالة

// إنشاء بيانات العضو
$presence_data = [
    'user_id' => $user_id,
    'user_info' => [
        'name' => $user_info->username ?? "User $user_id"
    ]
];

echo $pusher->presence_auth($_POST['channel_name'], $_POST['socket_id'], $user_id, $presence_data);
exit;
?>
