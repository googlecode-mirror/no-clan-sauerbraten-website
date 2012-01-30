<?php
date_default_timezone_set('Europe/Berlin');

// CONSTANT VARIABLES FOR DATABASE CONNECTION
define( 'DB_SERVER', '*******' );
define( 'DB_NAME', '*******');
define( 'DB_USER', '*******');
define( 'DB_PASS', '*******');

// ENCRYPTERS
define('SALT_1',  '********');
define('SALT_2',   '*******)');
define('HASH_KEY', '********');

// CONSTANT VARIABLES FOR OFFICIAL EMAIL
define('NOCLAN_EMAIL',  '********');
define('NOCLAN_EMAIL_PASS',  '********');

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  FUCKING MAGIC-QUOTES
 *  Avoid some pain in the ass if server has
 *  fucking magic-quotes enabled.
 */

if (get_magic_quotes_gpc()) {
    function undoMagicQuotes($array, $topLevel=true) {
        $newArray = array();
        foreach($array as $key => $value) {
            if (!$topLevel) {
                $key = stripslashes($key);
            }
            if (is_array($value)) {
                $newArray[$key] = undoMagicQuotes($value, false);
            }
            else {
                $newArray[$key] = stripslashes($value);
            }
        }
        return $newArray;
    }
    $_GET = undoMagicQuotes($_GET);
    $_POST = undoMagicQuotes($_POST);
    $_COOKIE = undoMagicQuotes($_COOKIE);
    $_REQUEST = undoMagicQuotes($_REQUEST);
}

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
?>
