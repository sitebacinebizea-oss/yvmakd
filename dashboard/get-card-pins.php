<?php
// get-card-pins.php
require_once 'init.php';

if (!isset($_GET['client_id'])) {
    echo json_encode([]);
    exit;
}

$clientId = (int) $_GET['client_id'];

// ✅ نفس نمط المشروع (User class)
$pin = $User->fetchLastPinByClientId($clientId);

echo json_encode($pin ?: [], JSON_UNESCAPED_UNICODE);
exit;
