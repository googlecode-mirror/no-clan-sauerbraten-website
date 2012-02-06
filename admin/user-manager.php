<?php
/*
 *   user-manager.php
 *   This file changes the user-types (admin, member, friend, user)
 * 
 */ 
session_start ();
require_once 'functions.php';
require_once 'config.php';
require_once 'connect.php';
require_once 'isUser.php';

// db connection
$dbConn = connect_db();

// Is user connected? Is admin? no? go home then.
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
    if ($arrUser['type'] != 'admin') go_home();
    else $userId = $arrUser['idUser'];
} else { go_home(); }

// Logout
if (!empty ($_POST['submitQuit'])){ // Good Bye User Session
	include './includes/logout.php';
}

$page_title = "NC: User Manager"; // used at includes/head.inc.php

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php include rdir().'/admin/includes/head.inc.php';?>
<script language="javascript" type="text/javascript" src="<?echo rurl();?>/admin/js/user-manager.js"></script>
<style language="text/css">
div.greenButton, div.redButton,
div.canGreenButton, div.canRedButton {width: 15px; height: 15px; float: left; margin: 0 3px;}

div.greenButton{background: url('images/buttonGreen.png');}
div.redButton{background: url('images/buttonRed.png');}

div.canRedButton, div.canGreenButton{background: url('images/buttonGrey.png');}
div.canGreenButton:HOVER{background: url('images/buttonGreenHover.png');}
div.canRedButton:HOVER{background: url('images/buttonRedHover.png');}
td.reason{ font-size: 13px; color: #901010; padding: 10px}

div.isEditorButton, div.noEditorButton{width: 16px; height:16px;}
div.isEditorButton{background: url('images/isEditor.png');}
	div.isEditorButton:HOVER{background: url('images/editor_gray100.png');}
div.noEditorButton{background: url('images/editor_gray50.png');}
	div.noEditorButton:HOVER{background: url('images/isEditor.png');}
</style>
</head>

<body style="background: url('../css/art/wrapperBackAdmin.png')
             no-repeat scroll center 0px; background-color: #000;"
             onLoad="StartUp()"> <!-- on page load, create ajax handles & show all users-->
    <div id="wrapper">
	<div id="container">

	    <div id="header">
	    </div><!-- /header -->
	    
	    <div id="menu">
		    <?php include 'includes/menu.inc.php';?>
		    <?php include rdir().'/includes/userlog.inc.php';?>
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
					
						<!-- Users list -->
						<div id='users'></div>
						
		        </div><!-- /main -->
	        </div><!-- /content-->
	    
	    <div id="footer"></div> <!-- /footer -->
	    
	</div><!-- /container -->
    </div><!-- /wrapper -->
</body>

</html>

