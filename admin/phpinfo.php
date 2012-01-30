<?php
session_start ();
require_once 'functions.php';
require_once 'config.php';
require_once 'connect.php';
require_once 'isUser.php';
// SEND EMAIL FUNCTION

// db connection
$dbConn = connect_db();

// Is user connected? Is admin? no? go home then.
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
    if ($arrUser['type'] != 'admin') go_home();
} else { go_home(); }

phpinfo();
?>
