<?php
	session_start();
	require_once '../admin/functions.php';
	require_once rdir().'/admin/config.php';
	require_once rdir().'/admin/connect.php';
	require_once rdir().'/admin/isUser.php';

	// db connection
	$dbConn = connect_db();

	// Is user connected? Is admin? no? go home then.
	if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
		$arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
	} else { go_home(); }

    $idUser=$arrUser['idUser'];
    $q="SELECT idMessage FROM messages WHERE messages.to='$idUser' AND is_read='0'";
    $r=mysql_query($q, $dbConn);
    $messages=mysql_num_rows($r);
    echo '<p><a href="'.rurl().'/messages/">Messages ('.$messages.')</a>
          &bull; <a href="'.rurl().'/messages/full_message.php">Send a message</a></p>';
?>
