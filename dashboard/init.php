<?php

// مسارات مطلقة حتى لا يعتمد التحميل على مجلد العمل الحالي (مهم على Railway ومن جذر المشروع)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/db.php';
require_once __DIR__ . '/classes/core.php';
require_once __DIR__ . '/classes/user.php';
require_once __DIR__ . '/functions2.php';

// Check debug mode
debug_mode();

$Core = new Core();
$User = new User();

?>

