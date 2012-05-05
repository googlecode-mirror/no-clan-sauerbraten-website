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
$page_title = "NoClan: Philosophy"; // used at 'includes/head.inc'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php	include 'includes/head.inc.php';
?>
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
    					<h1>About No Clan and our Philosophy</h1>
    					<h2>Some brief history first</h2>
    					<p>In June 2011, on nooblounge.net, one player, seeing that another was playing good, proposed him to make a group or clan for people who really did't want a clan. They decided for a self negating clan-name, the simplest clan-tag... and the group was born.</p>
    					<p>For some reason we've yet to discover, others wanted to join.</p>
        				<h2>No Clan?</h2>
        				<p>No Clan started and still is a fun clan, but then it's far more from it.</p>
        				<p>Ask for clan wars (cw), ask for duels... Chances are we'll say no. Why? We have a 'don't know, don't care' attitude towards cw/duels. We see it as a pointless contest. Sauerbraten is a game, we get on line and PLAY to have fun. This is a great getaway from everyday's stress.</p>
        				<p>Why stress yourself further to prove you're better than the next player? You can just help your team each round to win and feel great, because you've done the right thing. That's all there is.</p>
     					<h2>But as we said, we're more than this.</h2>
     					<p>We're a democratic movement. Members of No Clan may challenge or accept a challenge for a cw/duel if they feel like it. The only thing we do ask from our members is fair play. This promotes the clan and the game.</p>
        				<p>If you want pwn people (which is actually a typing error for 'own'), No Clan is not for you. Join a normal clan, as there are lots of good clans with people that get 60 to 80 frags/game. We do not measure things in our underwear. We respect enemies and friends. And that's what we are in No Clan: friends. Beat that.</p>
    				</div>
				</div><!-- /main -->

			</div><!-- /content-->
			
			<div id="footer">
				<?php include 'includes/footer.inc.php';?>
			</div> <!-- /footer -->

		</div><!-- /container -->
	</div><!-- /wrapper -->

</body>

</html>
