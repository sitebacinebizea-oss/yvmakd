<?php

// Include config.php file
require_once 'config.php';

// Included classes
require_once 'classes/db.php';
require_once 'classes/core.php';
require_once 'classes/user.php';

// Include functions.php file
require_once 'functions2.php';

// Check debug mode
debug_mode();

$Core = new Core();
$User = new User();

?>

