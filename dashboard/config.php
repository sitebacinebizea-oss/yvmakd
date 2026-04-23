<?php
/**
 * Railway: أولاً MYSQL_ROOT_PASSWORD (غالباً تطابق MySQL الحقيقي)، ثم MYSQLPASSWORD، ثم من MYSQL_URL.
 * يصلح حالة لوحة yvmakd حيث MYSQLPASSWORD قديمة و MYSQL_ROOT_PASSWORD أو الرابط العام أحدث.
 */
function railway_env(string $key): string
{
    $v = getenv($key);
    if ($v !== false && $v !== '') {
        return trim((string) $v);
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return trim((string) $_SERVER[$key]);
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return trim((string) $_ENV[$key]);
    }
    return '';
}

$mysql_url = railway_env('MYSQL_URL');
if ($mysql_url === '') {
    $mysql_url = railway_env('MYSQL_PUBLIC_URL');
}

$host = '';
$user = '';
$pass = '';
$name = '';
$port = '3306';

if ($mysql_url !== '' && preg_match('#^mysql://#i', $mysql_url)) {
    $p = parse_url($mysql_url);
    if (is_array($p) && !empty($p['host'])) {
        $host = (string) $p['host'];
        $port = isset($p['port']) ? (string) (int) $p['port'] : '3306';
        $user = (string) ($p['user'] ?? 'root');
        $rp = $p['pass'] ?? '';
        $pass = $rp !== '' ? rawurldecode((string) $rp) : '';
        $name = isset($p['path']) ? ltrim((string) $p['path'], '/') : '';
    }
}

// المتغيرات المنفصلة تطغى على ما جاء من الرابط (تفادي Access denied بسبب MYSQL_URL غير محدّث)
$eh = railway_env('MYSQLHOST');
if ($eh !== '') {
    $host = $eh;
}
$eu = railway_env('MYSQLUSER');
if ($eu !== '') {
    $user = $eu;
}
$epr = railway_env('MYSQL_ROOT_PASSWORD');
$ep = railway_env('MYSQLPASSWORD');
if ($epr !== '') {
    $pass = $epr;
} elseif ($ep !== '') {
    $pass = $ep;
}
$en = railway_env('MYSQLDATABASE');
if ($en === '') {
    $en = railway_env('MYSQL_DATABASE');
}
if ($en !== '') {
    $name = $en;
}
$eport = railway_env('MYSQLPORT');
if ($eport !== '') {
    $port = $eport;
}

if ($host === '') {
    $host = 'localhost';
}
if ($user === '') {
    $user = 'root';
}
if ($name === '') {
    $name = 'railway';
}

define('DB_HOST', $host);
define('DB_USER', $user);
define('DB_PASSWORD', $pass);
define('DB_NAME', $name);
define('DB_PORT', $port);

define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('CAN_REGISTER', 'none');
define('DEFAULT_ROLE', 'member');
define('SECURE', false);
define('DEBUG', true);
