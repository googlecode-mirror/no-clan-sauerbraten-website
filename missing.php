<?php
session_start ();
require_once 'admin/functions.php';
require_once 'admin/config.php';
require_once 'admin/connect.php';
require_once 'admin/isUser.php';

// db connection
$dbConn = connect_db();

// LOGIN SENT
if (!empty($_POST['submitLog'])) {
	include './includes/login.php';
}elseif (!empty ($_POST['submitQuit'])){ // Good Bye User Session
	include './includes/logout.php';
}

// Is user connected?
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password']))
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
        
// page info
$page_title = "NoClan: Home"; // used at 'includes/head.inc'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php include $_SERVER['DOCUMENT_ROOT'].'/includes/head.inc.php';?>
</head>

<body <?php if (isset($arrUser)) echo "onload='StartUp()'"; ?>>
	<div id="wrapper">
		<div id="container">

			<div id="header"><?php include rdir().'/includes/header.inc.php';?></div><!-- /header -->
			
			<div id="menu">
				<?php include 'includes/menu.inc.php';?>
				<?php include 'includes/userlog.inc.php';?>
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
					
					<div><!-- sidepanel include -->
                        <?php include $_SERVER['DOCUMENT_ROOT'].'/includes/sidepanel.inc.php';?>
                    </div>
				</div><!-- /"sidepanel" -->

				<div id="main">
				<?php if (!empty($_POST['submitLogIn'])) echo "ENVIADO";?>
    				<div>
    					<h1><a>This page does not exist!</a></h1>
					<p>Yup, that's right! This page is really no page! This page is almost like No Clan! Except that we exist. And this page... well...</p>
<p>You either came here following an old link, we might have mistyped or forgotten something or you're trying to find something that you shouldn't.</p>
					<p>Please go back to <a href="<?php echo rurl(); ?>/index.php">our main page</a> and start again from there.</p>
					<p>Alternatively, you can <a href="<?php echo rurl(); ?>/contact.php">message us</a>, to inform us where this happened. If you are a friend of the clan, that is. Thank you.</p>
    				</div>
				</div><!-- /main -->

			</div><!-- /content-->
			
			<div id="footer">
			</div> <!-- /footer -->

		</div><!-- /container -->
	</div><!-- /wrapper -->

</body>

</html>
