<?php
	// Our logout, also known as `the session destroyer`.
	session_unset();
	session_destroy();

	$location = rurl();
	header("Location: $location");
	die;
?>
