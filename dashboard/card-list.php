<?php
require_once('./functions.php');

session_start();

// الحصول على user_id من الـ GET مع التحقق منه
$id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($id <= 0) {
    echo json_encode([]); // إرجاع مصفوفة فارغة إذا كان user_id غير صالح
    exit;
}

// استخدام prepared statement لتجنب SQL Injection
$stmt = mysqli_prepare($db_connection, "SELECT * FROM card WHERE userId = ? ORDER BY id DESC");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$rows = array();

while ($row = mysqli_fetch_assoc($result)) {
    $rows[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($db_connection);

echo json_encode($rows);
?>