<?php
session_start();

// حذف جميع متغيرات الجلسة
$_SESSION = array();

// حذف ملف تعريف الجلسة (Session Cookie)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// إنهاء الجلسة
session_destroy();

// التوجيه إلى صفحة تسجيل الدخول
header('Location: login.php');
exit;
?>