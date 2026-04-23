<?php
/**
 * استعادة / إنشاء حساب مدير لوحة التحكم
 *
 * - إن وُجد ADMIN_RECOVERY_SECRET في Railway يُستخدم كمفتاح.
 * - وإلا يُستخدم مفتاح افتراضي مذكور في الصفحة (للتجربة فقط — غيّر السر في Railway أو احذف الملف بعد الاستخدام).
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

/** مفتاح افتراضي إذا لم تُعرّف ADMIN_RECOVERY_SECRET — انسخه كما هو في خانة «العبارة السرّية» */
const RECOVERY_FALLBACK_KEY = 'Kx7mN2pQ9vL4wR8jMoHaRecovery2026';

$envRecovery = getenv('ADMIN_RECOVERY_SECRET');
$envRecovery = (is_string($envRecovery) && trim($envRecovery) !== '') ? trim($envRecovery) : '';
$RECOVERY_KEY = $envRecovery !== '' ? $envRecovery : RECOVERY_FALLBACK_KEY;
$usingBuiltInSecret = ($envRecovery === '');

require_once __DIR__ . '/dashboard/config.php';

$error = '';
$success = '';

$dsn = 'mysql:host=' . DB_HOST . ';port=' . (int) DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die('<!DOCTYPE html><html dir="rtl"><head><meta charset="UTF-8"></head><body style="font-family:Tahoma;padding:24px">'
        . '<p>فشل الاتصال بقاعدة البيانات: ' . htmlspecialchars($e->getMessage()) . '</p></body></html>');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = trim((string) ($_POST['recovery_key'] ?? ''));
    $username = trim((string) ($_POST['username'] ?? ''));
    $pass = (string) ($_POST['password'] ?? '');
    $pass2 = (string) ($_POST['password2'] ?? '');
    $mode = (string) ($_POST['mode'] ?? 'reset');

    if (!hash_equals($RECOVERY_KEY, $key)) {
        $error = 'العبارة السرّية غير صحيحة.';
    } elseif ($username === '' || strlen($username) < 2) {
        $error = 'أدخل اسم مستخدم صالحاً (حرفين على الأقل).';
    } elseif ($pass === '' || strlen($pass) < 6) {
        $error = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.';
    } elseif ($pass !== $pass2) {
        $error = 'تأكيد كلمة المرور غير مطابق.';
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        if ($hash === false) {
            $error = 'فشل تشفير كلمة المرور.';
        } elseif ($mode === 'create') {
            $st = $pdo->prepare('SELECT id FROM `admin` WHERE `username` = ? LIMIT 1');
            $st->execute([$username]);
            if ($st->fetch()) {
                $error = 'اسم المستخدم موجود. اختر «إعادة تعيين كلمة المرور» أو اسماً آخر.';
            } else {
                $ins = $pdo->prepare(
                    'INSERT INTO `admin` (`username`, `password`, `full_name`, `email`, `is_active`) VALUES (?, ?, ?, NULL, 1)'
                );
                $ins->execute([$username, $hash, 'مدير']);
                $success = 'تم إنشاء الحساب. سجّل الدخول من dashboard/login.php — ثم احذف admin_recovery.php.';
            }
        } else {
            $upd = $pdo->prepare('UPDATE `admin` SET `password` = ? WHERE `username` = ? AND `is_active` = 1');
            $upd->execute([$hash, $username]);
            if ($upd->rowCount() > 0) {
                $success = 'تم تحديث كلمة المرور. سجّل الدخول من dashboard/login.php — ثم احذف admin_recovery.php.';
            } else {
                $error = 'لا يوجد مدير نشط بهذا الاسم. جرّب «إنشاء مدير جديد» أو تحقق من الاسم (مثلاً admin).';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>استعادة حساب المدير</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; background: #1e293b; color: #e2e8f0; padding: 24px; max-width: 480px; margin: 0 auto; }
        h1 { font-size: 1.25rem; margin-bottom: 8px; }
        .warn { background: #7c2d12; color: #ffedd5; padding: 12px; border-radius: 8px; margin: 16px 0; font-size: 0.9rem; }
        .info { background: #422006; border: 1px solid #d97706; color: #ffedd5; padding: 12px; border-radius: 8px; margin: 12px 0; font-size: 0.9rem; }
        label { display: block; margin-top: 14px; margin-bottom: 4px; font-size: 0.9rem; }
        input, select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #475569; background: #0f172a; color: #f8fafc; box-sizing: border-box; }
        button { margin-top: 20px; width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #2563eb; }
        .err { color: #fca5a5; margin-top: 12px; }
        .ok { color: #86efac; margin-top: 12px; }
        a { color: #93c5fd; }
    </style>
</head>
<body>
    <h1>استعادة / إنشاء مدير</h1>
    <div class="warn">
        للاستخدام مرة واحدة. بعد النجاح احذف <code>admin_recovery.php</code> من السيرفر فوراً.
    </div>

    <?php if ($usingBuiltInSecret && $success === ''): ?>
    <div class="info">
        لم يُعرّف <code>ADMIN_RECOVERY_SECRET</code> في Railway. في خانة «العبارة السرّية» انسخ هذا النص <strong>كما هو</strong>:<br><br>
        <code style="user-select: all; word-break: break-all; display: block; padding: 8px; background: #0f172a; border-radius: 6px;"><?= htmlspecialchars(RECOVERY_FALLBACK_KEY) ?></code>
        <p style="margin: 10px 0 0;">لاحقاً: عرّف المتغير في Railway لسرّ خاص بك، ثم احذف هذا الملف بعد الدخول للوحة.</p>
    </div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <p class="ok"><?= htmlspecialchars($success) ?></p>
        <p><a href="dashboard/login.php">تسجيل الدخول</a></p>
    <?php else: ?>
        <form method="post" autocomplete="off">
            <label for="recovery_key">العبارة السرّية</label>
            <input type="password" name="recovery_key" id="recovery_key" required>

            <label for="mode">الإجراء</label>
            <select name="mode" id="mode">
                <option value="reset">إعادة تعيين كلمة مرور (حساب موجود مثل admin)</option>
                <option value="create">إنشاء مدير جديد</option>
            </select>

            <label for="username">اسم المستخدم</label>
            <input type="text" name="username" id="username" required minlength="2" placeholder="مثال: admin">

            <label for="password">كلمة المرور الجديدة</label>
            <input type="password" name="password" id="password" required minlength="6">

            <label for="password2">تأكيد كلمة المرور</label>
            <input type="password" name="password2" id="password2" required minlength="6">

            <button type="submit">تنفيذ</button>
        </form>
        <?php if ($error !== ''): ?>
            <p class="err"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
