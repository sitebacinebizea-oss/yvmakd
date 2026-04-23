<?php
session_start();
require_once 'init.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([]);
    exit;
}

$abshers = $User->fetchAbsherLoginsByUserId($user_id);
echo json_encode($abshers ?: []);
?>