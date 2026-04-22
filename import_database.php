<?php
// عرض جميع الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html dir='rtl'>";
echo "<head><meta charset='UTF-8'><title>استيراد قاعدة البيانات</title></head>";
echo "<body style='font-family: Arial; padding: 20px;'>";

echo "<h1>🔄 استيراد قاعدة البيانات إلى Railway</h1>";

try {
    // بيانات الاتصال
    $host = 'mysql.railway.internal';
    $user = 'root';
    $pass = 'ljxFqKdlLXvpaeTChkwXBYiGFzwauiVf';
    $dbname = 'railway';
    $port = 3306;

    echo "<p>📡 جاري الاتصال بقاعدة البيانات...</p>";
    
    // الاتصال بقاعدة البيانات
    $mysqli = new mysqli($host, $user, $pass, $dbname, $port);

    // التحقق من الاتصال
    if ($mysqli->connect_error) {
        throw new Exception("فشل الاتصال: " . $mysqli->connect_error);
    }

    echo "<p style='color: green;'>✅ تم الاتصال بنجاح!</p>";

    // تعيين ترميز UTF8
    $mysqli->set_charset("utf8mb4");

    // قراءة ملف SQL
    $sql_file = __DIR__ . '/database.sql';

    if (!file_exists($sql_file)) {
        throw new Exception("ملف SQL غير موجود! المسار: " . $sql_file);
    }

    echo "<p>📄 تم العثور على ملف SQL</p>";
    
    $sql = file_get_contents($sql_file);

    // تقسيم الاستعلامات
    $queries = array();
    $temp_query = '';
    
    foreach (explode("\n", $sql) as $line) {
        // تجاهل التعليقات
        if (substr(trim($line), 0, 2) == '--' || trim($line) == '' || substr(trim($line), 0, 2) == '/*') {
            continue;
        }
        
        $temp_query .= $line . "\n";
        
        if (substr(trim($line), -1, 1) == ';') {
            $queries[] = trim($temp_query);
            $temp_query = '';
        }
    }

    $success = 0;
    $failed = 0;

    echo "<h3>⚙️ جاري تنفيذ الاستعلامات...</h3>";

    foreach ($queries as $query) {
        $query = trim($query);
        
        if (empty($query)) {
            continue;
        }
        
        // عرض أول 100 حرف من الاستعلام
        $preview = substr($query, 0, 100);
        
        if ($mysqli->query($query)) {
            $success++;
            echo "<div style='color: green; margin: 5px 0;'>✅ نجح: " . htmlspecialchars($preview) . "...</div>";
        } else {
            $failed++;
            echo "<div style='color: red; margin: 5px 0;'>❌ فشل: " . htmlspecialchars($preview) . "...<br>الخطأ: " . $mysqli->error . "</div>";
        }
    }

    echo "<hr>";
    echo "<h3>📊 النتائج:</h3>";
    echo "<p>✅ عدد الاستعلامات الناجحة: <strong>$success</strong></p>";
    echo "<p>❌ عدد الاستعلامات الفاشلة: <strong>$failed</strong></p>";
    echo "<hr>";

    // عرض الجداول الموجودة
    $result = $mysqli->query("SHOW TABLES");
    
    if ($result) {
        echo "<h3>📁 الجداول الموجودة في قاعدة البيانات:</h3>";
        echo "<ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }

    $mysqli->close();
    echo "<h2 style='color: green;'>🎉 تم الانتهاء من عملية الاستيراد!</h2>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffcccc; padding: 15px; border: 1px solid red; border-radius: 5px;'>";
    echo "<h3 style='color: red;'>❌ حدث خطأ:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>
