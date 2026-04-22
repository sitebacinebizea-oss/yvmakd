<?php
// ملف استيراد قاعدة البيانات إلى Railway

// بيانات الاتصال
define('DB_HOST', 'mysql.railway.internal');
define('DB_USER', 'root');
define('DB_PASSWORD', 'ljxFqKdlLXvpaeTChkwXBYiGFzwauiVf');
define('DB_NAME', 'railway');
define('DB_PORT', 3306);

// الاتصال بقاعدة البيانات
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

// التحقق من الاتصال
if ($mysqli->connect_error) {
    die("فشل الاتصال: " . $mysqli->connect_error);
}

echo "تم الاتصال بنجاح!<br><br>";

// تعيين ترميز UTF8
$mysqli->set_charset("utf8mb4");

// قراءة ملف SQL
$sql_file = __DIR__ . '/database.sql';

if (!file_exists($sql_file)) {
    die("ملف SQL غير موجود! الرجاء وضع ملف database.sql في نفس المجلد.");
}

$sql = file_get_contents($sql_file);

// تقسيم الاستعلامات
$queries = explode(';', $sql);

$success = 0;
$failed = 0;

echo "جاري استيراد قاعدة البيانات...<br><br>";

foreach ($queries as $query) {
    $query = trim($query);
    
    // تجاهل الاستعلامات الفارغة والتعليقات
    if (empty($query) || substr($query, 0, 2) == '--' || substr($query, 0, 2) == '/*') {
        continue;
    }
    
    if ($mysqli->query($query)) {
        $success++;
        echo "✅ تم التنفيذ بنجاح<br>";
    } else {
        $failed++;
        echo "❌ خطأ: " . $mysqli->error . "<br>";
    }
}

echo "<br>=====================<br>";
echo "✅ عدد الاستعلامات الناجحة: $success<br>";
echo "❌ عدد الاستعلامات الفاشلة: $failed<br>";
echo "=====================<br><br>";

// عرض الجداول الموجودة
$result = $mysqli->query("SHOW TABLES");
echo "<h3>الجداول الموجودة في قاعدة البيانات:</h3>";
while ($row = $result->fetch_array()) {
    echo "📁 " . $row[0] . "<br>";
}

$mysqli->close();
echo "<br><h2 style='color: green;'>✅ تم استيراد قاعدة البيانات بنجاح!</h2>";
?>
