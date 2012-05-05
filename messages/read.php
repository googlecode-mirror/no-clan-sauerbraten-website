<?php
session_start ();
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/connect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/isUser.php';

// db connection
$dbConn = connect_db();
define('LIMIT_SHOW','10');

// LOGIN SENT
if (!empty($_POST['submitLog'])) include $_SERVER['DOCUMENT_ROOT'].'/includes/login.php';
elseif (!empty ($_POST['submitQuit'])) include $_SERVER['DOCUMENT_ROOT'].'/includes/logout.php';
// Is user connected?
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
}else{ go_home(); }
// ONLY ADMINS, MEMBERS AND FRIENDS ARE ALLOWED.
if (!empty($arrUser) && $arrUser['type'] == 'user') { go_home(); }

// Preprocess $_GET['msg'] parameter and flag errors if found
    if (isset($_GET['msg'])) {
		$idMessage=mysql_real_escape_string($_GET['msg']);
        $idUser=$arrUser['idUser'];
    
        $q="SELECT * FROM messages WHERE messages.to='$idUser' AND
            idMessage='$idMessage' LIMIT 1";
        $r=mysql_query($q, $dbConn);
    
        // Did we actually get anything?
        if (mysql_num_rows($r)!=1) $error['notfound']='Message not found!';
        else {
			$row=mysql_fetch_array($r);
		    $from=$row['from'];
            $time=date("M j, Y - H:i", strtotime($row['time']));
            $subject=stripslashes($row['subject']);
            $message=stripslashes($row['message']);
            $is_read=$row['is_read'];
			// We found it, so if it is a new message, mark as read.
            if ($is_read=='0') {
                $q="update messages set is_read='1'
                    where idMessage='$idMessage'";
                $r=mysql_query($q, $dbConn);
            }
        }
    } else $error['badcall']='Missing parameters';

// page info
$page_title = "NoClan: Read Message"; // used at 'includes/head.inc'
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php include $_SERVER['DOCUMENT_ROOT'].'/includes/head.inc.php';?>
</head>

<!-- Load javascript timers to update page -->
<body onload='StartUp(<?php
 	    if (!empty($arrUser) && $arrUser['type'] != 'user' ) echo '1'; 
	?>)'>
	<div id="wrapper">
		<div id="container">

			<div id="header">
			</div><!-- /header -->
			
			<div id="menu">
				<?php
				include $_SERVER['DOCUMENT_ROOT'].'/includes/menu.inc.php';
				include $_SERVER['DOCUMENT_ROOT'].'/includes/userlog.inc.php';
				?>
			</div>
				
			<div id="content">
				<div id="sidepanel">

					<!-- ERRORS? -->
				    <?php if (!empty($error)){?>
				    <div class="error">
				    	<h3>Problems:</h3>
						<?php foreach ($error as $e){?>
						<p><?php echo $e;?></p>
						<?php }?>
					</div>
					<?php }?>
					<!-- /errors -->
                    <?php include $_SERVER['DOCUMENT_ROOT'].'/includes/sidepanel.inc.php';?>
				</div><!-- /"sidepanel" -->

				<div id="main">

<div id="message">
<?php
//Show the message
if (empty($error)) {

	$img = get_user_pic($from, 64);
	
	//since we actually found it, load sender's username
	// no error checking, but do we really have to?
	$username_query="SELECT username, idUser FROM users WHERE
	                 idUser='$from' LIMIT 1";
	$r3=mysql_query($username_query, $dbConn);
	$usernameArr=mysql_fetch_array($r3);
	$username=$usernameArr['username'];
			
	
	// show sender's photo
    echo '<div class ="header">';
    echo '<img class="userpic" src="'.$img.
         '" alt="'.$username.'" title="'.$username.'"/>';
    echo "<p>from</p>";
    echo "<h2>$username</h2>";
    echo "<p>On $time</p>";
    echo '</div>';
    
    echo '<div class ="body">';
    echo "<p><strong>Subject:</strong> $subject</p>";
    echo "<p>$message</p>";        
    $rurl=rurl();    
    echo '<a class="fancy_mini_main" style="float: right;" href="'.$rurl.'/messages/message.php?reply='.$idMessage.'" title="Reply to '.$username.'">Reply</a>';    
	echo '</div>';
}

?>
</div>

				</div><!-- /main -->

			</div><!-- /content-->
			
			<div id="footer">
			</div> <!-- /footer -->

		</div><!-- /container -->
	</div><!-- /wrapper -->

</body>

</html>
