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
$page_title = "NoClan: FAQ"; // used at 'includes/head.inc'

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
    				<div>
    					<h1 style="color: #901010">Frequently Asked Questions</h1>
					<h3>What is No Clan?</h3>
					<p>No Clan is a group of players in the Sauerbraten game. Unlike normal clans, we enjoy the game and the community. We like being competitive, but winning isn't our first priority.</p>
					<h3>I like your site! If I register, will I become an NC member?</h3>
					<p>No. Registering allows you to comment on our posts. We like the feedback and we want to hear from you. That's all.</p>
					<h3>So, how do I become an NC? I want to join the clan!</h3>
					<p>Well, the first question you should ask yourself is why join No Clan? If you want to join a clan, then No Clan is not for you. But if you really still want to join, you have to be highly competitive in the aspects of fragging, scoring and teamwork, helpful to others in the Sauerbraten community and friendly. If you think you've got what we need, play with us. But don't ask. We'll tell you.</p>
					<h3>I'm thinking of cheating in order to join the clan, heheh!</h3>
					<p>First of all, this is not a question! Second, we hate cheats. By cheating, you cheat your own life! We will notice you and chances are you'll get kicked off the server. If you're already in the clan, it's automatic ban from the team. Also, if we ever suspected you of cheating, even if you didn't, you can't join.</p>
					<h3>So, I am confused. Can I join the clan or not?</h3>
					<p>No.</p>
					<p>For more information, browse the site, especially the <a href="<?php echo rurl();?>/about.php" title="NoClan Ethics">Philosophy section</a>.</p>
    				</div>
				</div><!-- /main -->

			</div><!-- /content-->
			
			<div id="footer">
			</div> <!-- /footer -->

		</div><!-- /container -->
	</div><!-- /wrapper -->

</body>

</html>
