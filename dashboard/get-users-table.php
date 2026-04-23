<?php
session_start();
require_once 'init.php';

// حماية بسيطة (اختياري)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit;
}

// جلب المستخدمين
$users = $User->fetchAllUsers();

// تجهيز البيانات
$data = [];

if ($users) {
    foreach ($users as $row) {
        $data[] = [
            'id' => $row->id,
            'username' => $row->username ?? '',
            'full_name' => $row->name ?? '',
            'message' => $row->message ?? '',
            'created_at_formatted' => $row->created_at
        ];
    }
}

// إخراج JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data, JSON_UNESCAPED_UNICODE);
