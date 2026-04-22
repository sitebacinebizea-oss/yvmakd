<?php
session_start();
require_once('init.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['user_id'])) {
    echo json_encode([]);
    exit;
}

$userId = intval($_GET['user_id']);
$numbers = $User->getAllNafathNumbers($userId);

echo json_encode($numbers ?: [], JSON_UNESCAPED_UNICODE);