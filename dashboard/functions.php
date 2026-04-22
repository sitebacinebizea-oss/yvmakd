<?php
require_once __DIR__ . '/config.php';
//Functions file
//Application Name
$app_name = 'airlines';
//----------------------------------------------
//Database connection data (نفس القيم في config.php — مشروع تجريبي)
$host_name  = DB_HOST;
$username   = DB_USER;
$password   = DB_PASSWORD;
$db_name    = DB_NAME;
$port       = DB_PORT;
//----------------------------------------------
//Connect to database
$db_connection = mysqli_connect($host_name, $username, $password, $db_name, $port);

// التحقق من الاتصال
if (!$db_connection) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

// تعيين ترميز الأحرف
mysqli_set_charset($db_connection, 'utf8mb4');
//----------------------------------------------
//Create Tables If Not Exist Any Table
$count = 'SELECT count(*) AS total FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = "'.$db_name.'"';
$result = mysqli_query($db_connection, $count);

if ($result) {
    $r = mysqli_fetch_assoc($result);
    
    if($r['total'] < 1){
        $create_tbl_users = "CREATE TABLE users(
            id INT(99) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
            user_name text NOT NULL,
            code text NOT NULL,
            approve text NOT NULL,
            password text NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $services = "CREATE TABLE services(
            id INT(99) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
            region text NOT NULL,
            services text NOT NULL,
            player text NOT NULL,
            duration text NOT NULL,
            gender text NOT NULL,
            payment text NOT NULL,
            the_date text NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if(mysqli_query($db_connection, $create_tbl_users)) {
            echo "تم إنشاء جدول users بنجاح<br>";
        } else {
            echo "خطأ في إنشاء جدول users: " . mysqli_error($db_connection) . "<br>";
        }
        
        if(mysqli_query($db_connection, $services)) {
            echo "تم إنشاء جدول services بنجاح<br>";
        } else {
            echo "خطأ في إنشاء جدول services: " . mysqli_error($db_connection) . "<br>";
        }
    }
} else {
    echo "خطأ في الاستعلام: " . mysqli_error($db_connection);
}
//----------------------------------------------
?>
