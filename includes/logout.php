<?php
	// Our logout, also known as `the session destroyer`.
	session_unset();
	session_destroy();

	$location = rurl().$_SERVER['REQUEST_URI'];
	header("Location: $location");
	die;
?>
