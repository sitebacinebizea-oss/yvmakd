<?php
// عرض جميع الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html dir='rtl'>";
echo "<head><meta charset='UTF-8'><title>استيراد قاعدة البيانات</title>";
echo "<style>body{font-family:Arial;padding:20px;max-width:1200px;margin:0 auto}.success{color:green;margin:5px 0}.error{color:red;margin:5px 0}.box{background:#f5f5f5;padding:15px;border-radius:5px;margin:10px 0}</style>";
echo "</head>";
echo "<body>";

echo "<h1>🔄 استيراد قاعدة البيانات إلى Railway</h1>";

try {
    // بيانات الاتصال
    $host = 'mysql.railway.internal';
    $user = 'root';
    $pass = 'ljxFqKdlLXvpaeTChkwXBYiGFzwauiVf';
    $dbname = 'railway';
    $port = 3306;

    echo "<p>📡 جاري الاتصال بقاعدة البيانات...</p>";
    
    // الاتصال بقاعدة البيانات باستخدام PDO
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    
    $pdo = new PDO($dsn, $user, $pass, $options);

    echo "<p style='color: green;'>✅ تم الاتصال بنجاح!</p>";

    // قراءة ملف SQL
    $sql_file = __DIR__ . '/database.sql';

    if (!file_exists($sql_file)) {
        throw new Exception("ملف SQL غير موجود! المسار: " . $sql_file);
    }

    echo "<p>📄 تم العثور على ملف SQL (حجم: " . round(filesize($sql_file)/1024, 2) . " KB)</p>";
    
    $sql = file_get_contents($sql_file);

    // تقسيم الاستعلامات
    $queries = array();
    $temp_query = '';
    
    foreach (explode("\n", $sql) as $line) {
        // تجاهل التعليقات والأسطر الفارغة
        $trimmed = trim($line);
        if (empty($trimmed) || 
            substr($trimmed, 0, 2) == '--' || 
            substr($trimmed, 0, 2) == '/*' ||
            $trimmed == 'START TRANSACTION;' ||
            $trimmed == 'COMMIT;' ||
            substr($trimmed, 0, 3) == '/*!') {
            continue;
        }
        
        $temp_query .= $line . "\n";
        
        if (substr($trimmed, -1) == ';') {
            $queries[] = trim($temp_query);
            $temp_query = '';
        }
    }

    $success = 0;
    $failed = 0;

    echo "<div class='box'>";
    echo "<h3>⚙️ جاري تنفيذ الاستعلامات... (إجمالي: " . count($queries) . ")</h3>";
    echo "<div style='max-height: 400px; overflow-y: auto;'>";

    foreach ($queries as $index => $query) {
        $query = trim($query);
        
        if (empty($query)) {
            continue;
        }
        
        // عرض أول 80 حرف من الاستعلام
        $preview = substr(str_replace(["\n", "\r", "\t"], ' ', $query), 0, 80);
        
        try {
            $pdo->exec($query);
            $success++;
            echo "<div class='success'>✅ [$success] " . htmlspecialchars($preview) . "...</div>";
        } catch (PDOException $e) {
            $failed++;
            echo "<div class='error'>❌ [$failed] " . htmlspecialchars($preview) . "...<br>&nbsp;&nbsp;&nbsp;&nbsp;الخطأ: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        // تحديث كل 10 استعلامات
        if (($success + $failed) % 10 == 0) {
            flush();
        }
    }

    echo "</div>";
    echo "</div>";

    echo "<hr>";
    echo "<div class='box'>";
    echo "<h3>📊 النتائج:</h3>";
    echo "<p>✅ عدد الاستعلامات الناجحة: <strong style='color: green;'>$success</strong></p>";
    echo "<p>❌ عدد الاستعلامات الفاشلة: <strong style='color: red;'>$failed</strong></p>";
    echo "</div>";

    // عرض الجداول الموجودة
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if ($tables) {
        echo "<div class='box'>";
        echo "<h3>📁 الجداول الموجودة في قاعدة البيانات (" . count($tables) . "):</h3>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li><strong>" . htmlspecialchars($table) . "</strong></li>";
        }
        echo "</ul>";
        echo "</div>";
    }

    echo "<h2 style='color: green;'>🎉 تم الانتهاء من عملية الاستيراد!</h2>";
    echo "<p style='background: #e8f5e9; padding: 15px; border-radius: 5px;'>يمكنك الآن حذف هذا الملف (import_database.php) من السيرفر.</p>";
    
} catch (PDOException $e) {
    echo "<div style='background: #ffcccc; padding: 15px; border: 1px solid red; border-radius: 5px;'>";
    echo "<h3 style='color: red;'>❌ خطأ في الاتصال بقاعدة البيانات:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background: #ffcccc; padding: 15px; border: 1px solid red; border-radius: 5px;'>";
    echo "<h3 style='color: red;'>❌ حدث خطأ:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</body></html>";
?>
