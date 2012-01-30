<?php
session_start ();
require_once '../admin/config.php';
require_once '../admin/connect.php';
require_once '../admin/functions.php';
require_once '../admin/isUser.php';

// db connection
$dbConn = connect_db();

// LOGIN SENT
if (!empty($_POST['submitLog'])) {
	include '../includes/login.php';
}elseif (!empty ($_POST['submitQuit'])){ // Good Bye User Session
	include '../includes/logout.php';
}

// Is user connected? Get the userArray (updates the login date at DB too).
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
    
    // update date_modified
    $id = $arrUser['idUser'];
    $d = date("Y-m-d H:i:s");
    $query = "UPDATE users SET date_modified = '$d' WHERE idUser = username='$id'";
    $result = mysql_query ($query, $dbConn);
}

// Get the selected user data
if(!empty ($_GET['name'])){
    
    $name = mysql_real_escape_string($_GET['name']);
    $query = "SELECT idUser, username, type, first_name, last_name, country, DATE_FORMAT (date_created, '%b %D, %Y') AS date_created, date_modified, homepage, about, location FROM users WHERE username='$name'";
    $result = mysql_query ($query, $dbConn);
    
    if ($user = mysql_fetch_array ($result)){
     
		// array $user to single vars
		$user = strip_slashes_arr($user);
		extract($user);
		// and userpic
		$userpic = get_user_pic($idUser, 128);
		unset ($user, $arr, $u);
		unset($query, $result);
    
    } else { go_home(); die; }
}

// page info
$page_title = "NoClan: Home"; // used at 'includes/head.inc'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php include '../includes/head.inc.php';?>
</head>

<body>
	<div id="wrapper">
		<div id="container">

			<div id="header">
			</div><!-- /header -->
			
			<div id="menu">
				<?php include '../includes/menu.inc.php';?>
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
					
					<?php include '../includes/userSideInfo.php'?>
					
					<!-- Last seen online -->
					<?php include '../includes/lastlogin.inc.php';?>
					<!-- /lastsenn -->
					
				</div><!-- /"sidepanel" -->

				<div id="main">
				
				<div id="showuser">
					
					<div id="userHeader">
    					<img class="userpic" src="<?php echo $userpic?>"/>
    					<h1><?php echo $username?><span class="label">(<?php echo $type;?>)</span></h1>					
    					<h2><?php echo $first_name.' '.$last_name.' ('.$country.')';?></h2>
    					<p><strong>&bull;NC&bull;</strong> member since <?php echo $date_created;?>.</p>
    					<p><?php echo $username?> is from <?php echo $location;?></p>
    					<p>
    						<span style="font-weight: bold; color: #606060">Homepage:</span>
    						<a style="font-weight: normal;" href="<?php echo $homepage;?>" title="<?php echo $homepage;?>" target="_blank"><?php echo $homepage;?></a>
    					</p>
    				</div>
    				
    				<div id="userAbout">
						<h2>About <?php echo $username;?>
						<?php if (isset($arrUser) && $idUser == $arrUser['idUser']){?>
						<span class="label"><a href="<?php echo rurl();?>/user-settings.php">[edit my info]</a></span>
						<?php }?>
						</h2>
						<div class="aboutUser">
							<?php echo $about;?>
						</div>
					</div>
										
					<!-- Message list here?
					<div id="messages"></div>
					-->
    			
    			</div><!-- /showuser -->	
				
				</div><!-- /main -->

			</div><!-- /content-->
			
			<div id="footer">
				<?php include '../includes/footer.inc.php';?>
			</div> <!-- /footer -->

		</div><!-- /container -->
	</div><!-- /wrapper -->

</body>

</html>
