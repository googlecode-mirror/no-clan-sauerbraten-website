<?php

session_start ();
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/functions.php';
require_once rdir().'/admin/connect.php';
require_once rdir().'/admin/isUser.php';

// db connection
$dbConn = connect_db();

// LOGIN SENT
if (!empty($_POST['submitLog'])) {
	include rdir().'/includes/login.php';
} elseif (!empty ($_POST['submitQuit'])) { // Good Bye User Session
	include rdir().'/includes/logout.php';
}

// Is user connected? Get the userArray (updates the login date at DB too).
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])) {
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
}

// page info
$page_title = "NC: Gallery";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<base href="<?php echo rurl();?>/gallery/"/>	
<title><?php echo $page_title;?></title>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<meta name="Language" content="EN"/>
<meta name="Keywords" content="NoClan, No Clan, sauerbraten, cube2"/>
<meta name="Description" content="Sauerbraten Clan, since 2011"/>
<meta name="Distribution" content="Global"/>
<meta name="Robots" content="All"/>
<link rel="shortcut icon" href="<?php echo rurl();?>/favicon.ico">
<!-- STYLE -->
<link href='http://fonts.googleapis.com/css?family=Orbitron:400,500,700,900|Aldrich|Gochi+Hand' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="<?php echo rurl();?>/css/default.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo rurl();?>/css/style.css"/>
<!-- /style -->

<!-- pwi -->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo rurl();?>/js/jquery.fancybox/jquery.fancybox-1.3.4.css"/>
<script type="text/javascript" src="<?php echo rurl();?>/js/jquery.fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link   href="css/pwi.css" rel="stylesheet" type="text/css"/>
<script src="js/jquery.pwi.js" type="text/javascript"></script>
<!-- /pwi -->

<!-- pwi config -->
	<?php
	
	$fancy_conf = "'overlayColor':'#000','overlayOpacity':0.6,'padding':1,'titlePosition':'outside','showCloseButton':false";
	
	// FOR SPECIFIC USER
	if (!empty($_GET['user']) && !empty($_GET['idUser'])){
		
		$username = mysql_real_escape_string($_GET['user']);
		$idUser = mysql_real_escape_string($_GET['idUser']);
		
		$query = "SELECT idUser, username, picasaUser FROM users WHERE (users.type = 'member' OR users.type = 'admin') AND username = '$username' AND idUser = '$idUser'";
		$result = mysql_query($query, $dbConn);
		if($row = mysql_fetch_array($result)){ extract(strip_slashes_arr($row)); }
		else go_home();
		
	?>

		<script type="text/javascript">
			$(document).ready(function() {
				$("#member_pics").pwi({
					username: '<?php echo $picasaUser;?>',
					mode: 'album',
					album: 'NC',
					maxResults: 16,
					thumbCrop: 1,
					showPager: 'both',
					popupExt: function(photos){
						photos.fancybox({
							<?php echo $fancy_conf;?>
						});
					}
				});				
			});
		</script>
		<style type='text/css'>.pwi_pager{display: ;}</style>
	
	<?php }else{
	// FOR INDEX
		$picasaUsers = array();
		$query = "SELECT idUser, username, picasaUser FROM users WHERE (users.type = 'member' OR users.type = 'admin') AND users.picasaUser != ''";
		$result = mysql_query($query, $dbConn);
		while ($row = mysql_fetch_array($result)){
			array_push($picasaUsers, strip_slashes_arr($row));
		}
		
		
		
		echo '<script type="text/javascript">';
		echo '$(document).ready(function() {';
		foreach ($picasaUsers as $pu){
			echo '$("#'.$pu['username'].'_container").pwi({';
			echo 'username: \''.$pu['picasaUser'].'\',';
			echo 'mode: \'album\',';
			echo 'album: \'NC\',';
			echo 'maxResults: 4,';
			echo 'thumbCrop: 1,';
			echo 'popupExt: function(photos){';
			echo 'photos.fancybox({';
			echo $fancy_conf;
			echo '});';
			echo '}';
			echo '});';
		}
		echo '});</script>';
		echo "<style type='text/css'>.pwi_pager{display: none;}</style>";
	}
?>
<!-- /pwi config -->

<!-- timers & auto-updates -->
<script language="javascript" type="text/javascript" src="/js/ajax-general.js"></script>
<!-- /timers & auto-updates -->

</head>

<body  <?php if (!empty($error['comm'])){ ?>onload="javascript:document.getElementById('comment_form').scrollIntoView(true); window.scrollBy(0, -(screen.height/4))"<?php }?>>
	<div id="wrapper">
		<div id="container">

			<div id="header"><?php include rdir().'/includes/header.inc.php';?></div><!-- /header -->
			
			<div id="menu">
				<?php include rdir().'/includes/menu.inc.php';?>
				<?php include rdir().'/includes/userlog.inc.php';?>
			</div>
				
			<div id="content">
				<div id="sidepanel"><?php include rdir().'/includes/sidepanel.inc.php';?></div><!-- /"sidepanel" -->

				<div id="main">
					
				<?php if (empty($_GET['user'])){?>
					
					<h1 style="text-align: center"><?php echo get_the_flag(24, 'blue');?>&nbsp;&nbsp;·NC· GALLERY&nbsp;&nbsp;<?php echo get_the_flag(24, 'red');?></h1>
					
					<?php foreach ($picasaUsers as $pu){?>
					
					<div class="picasa">
						<h2>
							<a href="<?php echo $pu['idUser'].'/'.$pu['username'];?>">
								<?php echo $pu['username'];?>
							</a>
							<span><a href="<?php echo $pu['idUser'].'/'.$pu['username'];?>">view all</a></span>
						</h2>
							
						<div id="<?php echo $pu['username']?>_container"></div>
					</div>
					
					<?php }
					
				}else { ?>
					<p>
						<a href="<?php echo rurl().'/gallery/';?>" title="back to the gallery index">Back to the Gallery Index</a>
					</p>
					<h1 style="text-align: center"><?php echo get_the_flag(24, "blue").'&nbsp;&nbsp;·NC· '.$username.'&nbsp;&nbsp;'.get_the_flag(24, "red");?></h1>
					<div class="picasa">
						<div id="member_pics"></div>
					</div>
					<p style="text-align: center">
						<a href="<?php echo rurl().'/gallery/';?>" title="back to the gallery index">
							Back to the Gallery Index<br/><br/><?php echo get_the_flag(24, 'blue');?>
						</a>						
					</p>
					<?php }?>
					
				</div><!-- /main -->
			</div><!-- /content-->
			<div id="footer"><?php include rdir().'/includes/footer.inc.php';?></div> <!-- /footer -->
		</div><!-- /container -->
	</div><!-- /wrapper -->
	<!-- Load javascript timers to update page -->
    <script>StartUp(<?php
 	    if (!empty($arrUser) && $arrUser['type'] != 'user' ) echo '1'; 
	    ?>)</script>
</body>

</html>
