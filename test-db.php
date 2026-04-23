<?php
/**
 * اختبار اتصال قاعدة البيانات — ضعه في جذر الموقع ثم افتحه من المتصفح.
 * احذف هذا الملف بعد الانتهاء (أمان).
 */
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo '<!DOCTYPE html><html dir="rtl"><head><meta charset="UTF-8"><title>اختبار DB</title>';
echo '<style>body{font-family:Tahoma,Arial,sans-serif;padding:20px;max-width:800px;margin:0 auto;background:#1a1a2e;color:#eee}';
echo '.ok{color:#4ecca3}.bad{color:#ff6b6b}.box{background:#16213e;padding:15px;border-radius:8px;margin:12px 0;word-break:break-all}';
echo 'h1{font-size:1.2rem}code{background:#0f3460;padding:2px 6px;border-radius:4px}</style></head><body>';
echo '<h1>اختبار اتصال MySQL</h1>';

$configPath = __DIR__ . '/dashboard/config.php';
if (!is_readable($configPath)) {
    echo '<p class="bad">لم يُعثر على <code>dashboard/config.php</code></p></body></html>';
    exit;
}

require_once $configPath;

function mask_pass(string $p): string
{
    $len = strlen($p);
    if ($len <= 4) {
        return str_repeat('*', $len);
    }
    return substr($p, 0, 2) . str_repeat('*', max(4, $len - 4)) . substr($p, -2);
}

echo '<div class="box"><strong>ما قرأه config.php:</strong><br>';
echo 'DB_HOST = <code>' . htmlspecialchars(DB_HOST) . '</code><br>';
echo 'DB_PORT = <code>' . htmlspecialchars((string) DB_PORT) . '</code><br>';
echo 'DB_USER = <code>' . htmlspecialchars(DB_USER) . '</code><br>';
echo 'DB_NAME = <code>' . htmlspecialchars(DB_NAME) . '</code><br>';
echo 'DB_PASSWORD طولها = ' . strlen(DB_PASSWORD) . ' — معاينة: <code>' . htmlspecialchars(mask_pass(DB_PASSWORD)) . '</code></div>';

$port = (int) DB_PORT;

// 1) mysqli (نفس أسلوب functions.php: اتصال ثم USE)
echo '<h2>1) mysqli</h2>';
$mysqli = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, '', $port);
if (!$mysqli) {
    echo '<p class="bad">فشل: <code>' . htmlspecialchars(mysqli_connect_error()) . '</code></p>';
} else {
    echo '<p class="ok">تم فتح اتصال mysqli.</p>';
    if (!mysqli_query($mysqli, 'USE `' . str_replace('`', '``', DB_NAME) . '`')) {
        echo '<p class="bad">USE: <code>' . htmlspecialchars(mysqli_error($mysqli)) . '</code></p>';
    } else {
        echo '<p class="ok">تم تنفيذ USE للقاعدة <code>' . htmlspecialchars(DB_NAME) . '</code></p>';
        $r = mysqli_query($mysqli, 'SELECT DATABASE() AS db, VERSION() AS ver');
        if ($r) {
            $row = mysqli_fetch_assoc($r);
            echo '<div class="box">DATABASE() = <code>' . htmlspecialchars($row['db'] ?? '') . '</code><br>';
            echo 'VERSION() = <code>' . htmlspecialchars($row['ver'] ?? '') . '</code></div>';
        }
    }
    mysqli_close($mysqli);
}

// 2) PDO (نفس أسلوب classes/db.php)
echo '<h2>2) PDO</h2>';
$dsn = 'mysql:host=' . DB_HOST . ';port=' . $port . ';dbname=' . DB_NAME . ';charset=utf8mb4';
try {
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    echo '<p class="ok">تم الاتصال بـ PDO بنجاح.</p>';
    $st = $pdo->query('SELECT DATABASE() AS db, VERSION() AS ver');
    $row = $st->fetch(PDO::FETCH_ASSOC);
    echo '<div class="box">DATABASE() = <code>' . htmlspecialchars($row['db'] ?? '') . '</code><br>';
    echo 'VERSION() = <code>' . htmlspecialchars($row['ver'] ?? '') . '</code></div>';
} catch (PDOException $e) {
    echo '<p class="bad">PDO: <code>' . htmlspecialchars($e->getMessage()) . '</code></p>';
}

// 3) ما تراه PHP من متغيرات Railway (بدون كشف السر كاملاً)
echo '<h2>3) متغيرات البيئة (وجود / غياب)</h2>';
$keys = ['MYSQL_URL', 'MYSQL_PUBLIC_URL', 'MYSQLHOST', 'MYSQLPORT', 'MYSQLUSER', 'MYSQLPASSWORD', 'MYSQLDATABASE', 'MYSQL_DATABASE'];
echo '<div class="box">';
foreach ($keys as $k) {
    $v = getenv($k);
    if ($v === false || $v === '') {
        echo $k . ': <span class="bad">فارغ أو غير معرّف عند getenv()</span><br>';
    } else {
        $show = $k === 'MYSQLPASSWORD' || strpos($k, 'URL') !== false ? '(مخفي، الطول ' . strlen($v) . ')' : htmlspecialchars(substr($v, 0, 80)) . (strlen($v) > 80 ? '…' : '');
        echo $k . ': <span class="ok">موجود</span> ' . $show . '<br>';
    }
}
echo '</div>';

echo '<p style="margin-top:24px;color:#aaa;font-size:0.9rem">احذف الملف <code>db_test.php</code> بعد التشخيص.</p>';
echo '</body></html>';
