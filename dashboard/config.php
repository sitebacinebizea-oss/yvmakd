<?php
/**
 * نفس أسلوب «مشروع صديقي»: إن وُجد MYSQL_URL يُستخرج منه كل شيء (كما يفعل Railway عادة).
 * وإلا تُستخدم MYSQLHOST / MYSQLUSER / MYSQLPASSWORD / MYSQLDATABASE / MYSQLPORT.
 * + قراءة من $_SERVER و$_ENV لأن بعض إعدادات PHP لا تعيد getenv فقط.
 */
function railway_env(string $key): string
{
    $v = getenv($key);
    if ($v !== false && $v !== '') {
        return $v;
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }
    return '';
}

// على Railway: MYSQL_URL داخلي. للتجربة من جهازك بدون MYSQL_URL استخدم MYSQL_PUBLIC_URL من لوحة MySQL.
$mysql_url = railway_env('MYSQL_URL');
if ($mysql_url === '') {
    $mysql_url = railway_env('MYSQL_PUBLIC_URL');
}

if ($mysql_url !== '') {
    $url_parts = parse_url($mysql_url);
    if (is_array($url_parts) && !empty($url_parts['host'])) {
        define('DB_HOST', (string) $url_parts['host']);
        define('DB_PORT', isset($url_parts['port']) ? (string) (int) $url_parts['port'] : '3306');
        define('DB_USER', (string) ($url_parts['user'] ?? 'root'));
        $rawPass = $url_parts['pass'] ?? '';
        define('DB_PASSWORD', $rawPass !== '' ? rawurldecode((string) $rawPass) : '');
        define('DB_NAME', isset($url_parts['path']) ? ltrim((string) $url_parts['path'], '/') : 'railway');
    }
}

if (!defined('DB_HOST')) {
    define('DB_HOST', railway_env('MYSQLHOST') ?: 'localhost');
    define('DB_PORT', railway_env('MYSQLPORT') ?: '3306');
    define('DB_USER', railway_env('MYSQLUSER') ?: 'root');
    define('DB_PASSWORD', railway_env('MYSQLPASSWORD') ?: railway_env('MYSQL_ROOT_PASSWORD'));
    define('DB_NAME', railway_env('MYSQLDATABASE') ?: railway_env('MYSQL_DATABASE') ?: 'railway');
}

define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('CAN_REGISTER', 'none');
define('DEFAULT_ROLE', 'member');
define('SECURE', false);
define('DEBUG', true);
