<?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once('init.php');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // التحقق من البيانات
    if (empty($username) || empty($password)) {
        $error = 'اسم المستخدم وكلمة المرور مطلوبان';
    } elseif (strlen($username) < 3) {
        $error = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل';
    } elseif (strlen($password) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } elseif ($password !== $confirm_password) {
        $error = 'كلمة المرور وتأكيد كلمة المرور غير متطابقين';
    } else {
        // استخدام دالة createAdmin من User class
        $result = $User->createAdmin($username, $password, $full_name, $email);
        
        if ($result['success']) {
            $success = 'تم إضافة المشرف بنجاح!';
            // تفريغ الحقول
            $username = $full_name = $email = '';
        } else {
            $error = $result['error'];
        }
    }
}

// جلب قائمة المشرفين
$admins = $User->getAllAdmins();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>إدارة المشرفين - لوحة التحكم</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .page-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 20px 25px rgba(0,0,0,0.15);
        }
        
        .page-header h1 {
            margin: 0;
            color: #1f2937;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            margin-top: 15px;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 25px rgba(0,0,0,0.15);
            margin-bottom: 25px;
        }
        
        .card h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            width: 100%;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #f9fafb;
            padding: 15px;
            text-align: right;
            font-weight: 700;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-users-cog"></i>
                إدارة المشرفين
            </h1>
            <a href="index.php" class="btn-back">
                <i class="fas fa-arrow-right"></i>
                العودة للوحة التحكم
            </a>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-user-plus"></i> إضافة مشرف جديد</h3>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i> اسم المستخدم *
                            </label>
                            <input type="text" 
                                   name="username" 
                                   class="form-control" 
                                   placeholder="اسم المستخدم"
                                   value="<?= htmlspecialchars($username ?? ''); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-id-card"></i> الاسم الكامل
                            </label>
                            <input type="text" 
                                   name="full_name" 
                                   class="form-control" 
                                   placeholder="الاسم الكامل"
                                   value="<?= htmlspecialchars($full_name ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-lock"></i> كلمة المرور *
                            </label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control" 
                                   placeholder="كلمة المرور"
                                   required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-lock"></i> تأكيد كلمة المرور *
                            </label>
                            <input type="password" 
                                   name="confirm_password" 
                                   class="form-control" 
                                   placeholder="تأكيد كلمة المرور"
                                   required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i> البريد الإلكتروني
                    </label>
                    <input type="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="البريد الإلكتروني"
                           value="<?= htmlspecialchars($email ?? ''); ?>">
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-plus-circle"></i>
                    إضافة مشرف
                </button>
            </form>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-list"></i> قائمة المشرفين</h3>
            
            <?php if (count($admins) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم المستخدم</th>
                            <th>الاسم الكامل</th>
                            <th>البريد الإلكتروني</th>
                            <th>تاريخ الإنشاء</th>
                            <th>آخر تسجيل دخول</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><?= $admin->id; ?></td>
                            <td><strong><?= htmlspecialchars($admin->username); ?></strong></td>
                            <td><?= htmlspecialchars($admin->full_name ?? '-'); ?></td>
                            <td><?= htmlspecialchars($admin->email ?? '-'); ?></td>
                            <td><?= date('Y/m/d h:i A', strtotime($admin->created_at)); ?></td>
                            <td><?= $admin->last_login ? date('Y/m/d h:i A', strtotime($admin->last_login)) : '-'; ?></td>
                            <td>
                                <?php if ($admin->is_active): ?>
                                    <span class="badge badge-active">نشط</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">غير نشط</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p style="text-align: center; color: #6b7280; padding: 40px;">
                <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i><br>
                لا يوجد مشرفين
            </p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>