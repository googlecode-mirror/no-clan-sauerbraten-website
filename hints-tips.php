<?php
session_start ();
require_once 'admin/config.php';
require_once 'admin/connect.php';
require_once 'admin/functions.php';
require_once 'admin/isUser.php';

// db connection
$dbConn = connect_db();

// LOGIN SENT
if (!empty($_POST['submitLog'])) {
	include './includes/login.php';
}elseif (!empty ($_POST['submitQuit'])){ // Good Bye User Session
	include './includes/logout.php';
}

// Is user connected? Get the userArray (updates the login date at DB too).
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
}

// page info
$page_title = "NoClan: Hints &amp; tips"; // used at 'includes/head.inc'
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php include $_SERVER['DOCUMENT_ROOT'].'/includes/head.inc.php';?>
</head>
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
				
				<div><?php include rdir().'/includes/sidepanel.inc.php';?></div>
			</div><!-- /"sidepanel" -->

			<div id="main">
				
				<h1>Hints &amp; tips to become better</h1>
				<p><b>Speed:</b> Jumping gains a 30% speedboost while in the air. Moving forwards or backwards without strafing also gains a 30% speedboost. Forward and jumping speedboosts are cumulative. I personally rifle jump backwards without pressing left or right and it's almost like double speed. Playing like that, I've been frequently asked if I hack, but I don't.
				</p><p><b>Escape with flag:</b> Running away with the flag like above, gives you a great advantage: You can rifle-jump shooting at your enemies! Never run forward to your base with the red flag. Turning your back to the enemies is considered a big mistake, as they'll most probably get you! Try being unpredictable, but don't overdo it. Never forget that in capture the flag games the goal is to get the red flag back your base.
				</p><p><b>KNOW YOUR MAPS!</b> I can't stress this enough! There are great routes, great places and great moves in every map that may give you a significant advantage against your opponents. Knowing them and using them when needed may win a losing game.</p>

				<p><b>Minimap:</b> You can go berserk shooting all reds that come across and playing as in Single Player mode OR you can also watch the minimap and turn your attention where help is wanted, either protecting your teammate with the flag or ambushing the opposing flag holder. Many are the times the two flag holders kill each other and both flags are there to be taken. This may be the easiest way to get them both.
				</p><p><b>Teamwork:</b> Try keeping a balance between personal gains and team gains. Always support the flag carrier if they are close to you. Support them all the way to your base if there is no one else, even if you were at the enemy base. Be their wingman. If they do get killed, chances are you'll score. How many times have you felt alone holding the flag in a team of 10-12 players? Don't do the same to your teammates, as it can give the enemy team easy points.
				</p><p><b>Base:</b> No Clan members should be against base camping. But if you see your base packed with reds, stay there (and if you die, go back again) until there are no more enemies. There's no point attacking, getting the flag and returning home to see 4 reds shooting at you.
				</p>
				
				<h2>Related posts:</h2>
				<p>&bull; <a href="<?php echo rurl();?>/post/162/support-the-flag-holder">Support the flag holder!</a> - Tips for a nice teamplay (by Istha).</p>
				<p>&bull; <a href="<?php echo rurl();?>/post/149/stats-inside-your-hud-script">Stats inside your HUD</a> - Script for having realtime stats on your screen (by Lilleg).</p>
				<p>&bull; <a href="<?php echo rurl();?>/post/161/crosshairs">Crosshairs</a> - Download our NC Crosshairs (by Panda).</p>
			</div><!-- /main -->
	    </div><!-- /content-->
	    
	    <div id="footer">
		    <?php include 'includes/footer.inc.php';?>
	    </div> <!-- /footer -->

	</div><!-- /container -->
    </div><!-- /wrapper -->

</body>
</html>
