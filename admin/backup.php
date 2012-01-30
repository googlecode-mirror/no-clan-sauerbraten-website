<?php
session_start ();
require_once 'functions.php';
require_once 'config.php';
require_once 'connect.php';
require_once 'isUser.php';
// SEND EMAIL FUNCTION
include_once 'send_mail.php'; // for sending emails

// db connection
$dbConn = connect_db();

// Is user connected? Is admin? no? go home then.
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
    if ($arrUser['type'] != 'admin') go_home();
} else { go_home(); }

$page_title = "NC: BACKUP DB" // used at includes/head.inc.php
?>

<?php
				
    if (($_SERVER['REQUEST_METHOD']=='GET') && (isset($_GET['backup']))) {
		$backupfile = '../data/tmp_data/noclan' . date("Y-m-d") . '.sql';
		system("mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $backupfile");
		system("gzip $backupfile");
		$backupfile.='.gz';

		// Mail the file
		// Addresses during normal mode
		$to = array('mail1@mail.com', 'mail2@mail.com');  
		$subject = 'noclan.nooblounge.net mysql backup';
		$body = "This email contains the <strong>noclan.nooblounge.net</strong> mysql database backup.<br />\n"
				."Date created: ". date("Y-m-d") ."<br />\n"
				."Have a nice day!<br />\n<br />\n"
				."<strong>The -NC- Team</strong>";
		$att = $backupfile;
		if (send_mail($to, $subject, $body, $att)) echo 'The backup email was successfully sent! '.$backupfile;
		else echo 'There was an error in sending the backup email.';

		// Delete the file from your server
		unlink($backupfile);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <?php include rdir().'/admin/includes/head.inc.php';?>
    <!-- Fancy -->
    <?php include rdir().'/admin/includes/fancy.inc.php'?>
    <!-- /Fancy -->
</head>
<body style="background: url('../css/art/wrapperBackAdmin.png') no-repeat scroll center 0px; background-color: #000;">
    <div id="wrapper">
	<div id="container">

	    <div id="header">
	    </div><!-- /header -->
	    
	    <div id="menu">
		    <?php include 'includes/menu.inc.php';?>
		    <?php include '../includes/userlog.inc.php';?>
	    </div>
		    
	    <div id="content">
		<div id="sidepanel">
	
		    <!-- ERRORS? -->
		    <?php if (!empty($error)){?>
		    <div class="error">
			<h3>Login problems:</h3>
			<?php foreach ($error as $e){?>
			<p><?php echo $e;?></p>
			<?php }?>
		    </div>
		    <?php }?>
		    <!-- /errors -->
			
		    <!-- sidepanel include -->
		    <div><?php include rdir().'/admin/includes/sidepanel.inc.php';?></div></div><!-- /"sidepanel" -->

		    <div id="main">
			<a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?backup=yes">Backup now!</a>
		    </div><!-- /main -->
	        </div><!-- /content-->
	    
	    <div id="footer"></div> <!-- /footer -->
	    
	</div><!-- /container -->
    </div><!-- /wrapper -->
</body>

</html>

