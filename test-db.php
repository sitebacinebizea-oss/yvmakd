<?php
echo "<h2>🔍 اختبار الاتصال بقاعدة البيانات</h2>";

// Show all MySQL environment variables
echo "<h3>المتغيرات:</h3>";
echo "MYSQL_URL: " . (getenv('MYSQL_URL') ?: 'غير موجود') . "<br>";
echo "MYSQLHOST: " . (getenv('MYSQLHOST') ?: 'غير موجود') . "<br>";
echo "MYSQLPORT: " . (getenv('MYSQLPORT') ?: 'غير موجود') . "<br>";
echo "MYSQLUSER: " . (getenv('MYSQLUSER') ?: 'غير موجود') . "<br>";
echo "MYSQLPASSWORD: " . (getenv('MYSQLPASSWORD') ? '***موجود***' : 'غير موجود') . "<br>";
echo "MYSQLDATABASE: " . (getenv('MYSQLDATABASE') ?: 'غير موجود') . "<br>";

echo "<hr>";

// Try to connect
$mysql_url = getenv('MYSQL_URL');

if ($mysql_url) {
    echo "<h3>استخدام MYSQL_URL</h3>";
    $url_parts = parse_url($mysql_url);
    $host = $url_parts['host'];
    $port = $url_parts['port'] ?? 3306;
    $user = $url_parts['user'];
    $password = $url_parts['pass'];
    $database = ltrim($url_parts['path'], '/');
} else {
    echo "<h3>استخدام المتغيرات المنفصلة</h3>";
    $host = getenv('MYSQLHOST') ?: 'localhost';
    $port = getenv('MYSQLPORT') ?: 3306;
    $user = getenv('MYSQLUSER') ?: 'root';
    $password = getenv('MYSQLPASSWORD') ?: '';
    $database = getenv('MYSQLDATABASE') ?: 'railway';
}

echo "Host: $host<br>";
echo "Port: $port<br>";
echo "User: $user<br>";
echo "Database: $database<br>";

echo "<hr>";

// Test connection
$con = @mysqli_connect($host, $user, $password, $database, $port);

if ($con) {
    echo "✅ <strong style='color:green;'>الاتصال نجح!</strong><br>";
    mysqli_close($con);
} else {
    echo "❌ <strong style='color:red;'>فشل الاتصال: " . mysqli_connect_error() . "</strong><br>";
}
?>
