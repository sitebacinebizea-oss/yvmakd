<?php
/** إعدادات تجريبية — عدّل القيم حسب Railway لو احتجت */

define('DB_HOST', 'mysql.railway.internal');
define('DB_USER', 'root');
define('DB_PASSWORD', 'ljxFqKdlLXvpaeTChkwXBY1GFzwau1Vf');
define('DB_NAME', 'railway');
define('DB_PORT', '3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('CAN_REGISTER', 'none');
define('DEFAULT_ROLE', 'member');

// For development only!!
define('SECURE', false);
define('DEBUG', true);
