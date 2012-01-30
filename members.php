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
$page_title = "NoClan: Members"; // used at 'includes/head.inc'

// GET THE MEMBERS DATA
$query = "SELECT idUser, username, type, country, first_name, last_name, date_created, homepage, picasaUser, about FROM users WHERE users.type = 'member' OR users.type = 'admin' ORDER by date_created";
$result = mysql_query($query, $dbConn);
$arrMembers = array();
while ( $row = mysql_fetch_assoc ($result)) { array_push( $arrMembers, strip_slashes_arr($row));}

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
                <div>
    					<h1 style="text-align: center;"><?php echo get_the_flag(24, 'blue').' Current No Clan members '.get_the_flag(24, 'blue');?></h1>
    					
    					<?php foreach($arrMembers as $member){
							extract($member, EXTR_PREFIX_ALL, 'm');
							$m_joined = how_long_since($m_date_created);
							$m_img_url = get_user_pic($m_idUser, 64);
							$m_info_url = rurl().'/user/'.$m_username.'/';
							$m_frame_url = rurl().'/user/show_userinfo.php?id='.$m_idUser;
							if ($m_username[strlen($m_username)-1] == 's') { $m_usernames = $m_username."'"; } else { $m_usernames = $m_username."'s";}
							if (!empty($m_picasaUser)){ $m_gallery = rurl().'/gallery/'.$m_idUser.'/'.$m_username; }
						?>
						
						<div class="member_li">
							<a href="<?php echo $m_info_url;?>" title="<?php echo $m_usernames.' info'?>">
								<img class="userpic" src="<?php echo $m_img_url; ?>" alt="<?php echo $m_username; ?>"/>
							</a>
							
							<?php if (isset($arrUser) && ($arrUser['type'] == 'member' || $arrUser['type'] == 'admin')){?>
							<!-- SEND MESSAGE -->
							<div class="msg">
								<a class="fancy_mini_main"  href="<?php echo rurl();?>/messages/message.php?to=<?php echo $m_username;?>" title="<?php echo "Send a message to $m_username";?>"><?php echo $m_username;?>
								<img class="msg" src="<?php echo rurl().'/images/mail.png';?>"/>
								</a>
							</div>
							<?php }
							
							// VIEW GALLERY 
							if (!empty($m_picasaUser)){?>
							<div class="msg" style="clear: right;">
								<a href="<?php echo rurl();?>/gallery/<?php echo $m_idUser.'/'.$m_username;?>" title="<?php echo "$m_usernames gallery";?>">pictures <img class="msg" src="<?php echo rurl().'/images/gallery.png';?>"/>
								</a>
							</div>
							<?php }?>
							
							<h2><?php echo '<a href="'.$m_info_url.'" title="'.$m_usernames.' info">'.$m_username.'</a>'; ?></h2>

							<p class="from"><?php echo $m_country; ?>.</p>
							<p><strong>-NC-</strong> member since <?php echo $m_joined; ?>.</p>
							<div class="about"><?php echo $m_about; ?></div>
						</div>
						
						
					 <?php }?>
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
