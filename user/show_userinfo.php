<?php
session_start ();
require_once '../admin/config.php';
require_once '../admin/connect.php';
require_once '../admin/functions.php';
require_once '../admin/isUser.php';

// db connection
$dbConn = connect_db();

// Is user connected? Get the userArray (updates the login date at DB too).
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password']))
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
    
// Get the selected user data
if(!empty ($_GET['id'])){
    
    $id = mysql_real_escape_string($_GET['id']);
    $query = "SELECT idUser, username, type, first_name, last_name, country, DATE_FORMAT (date_created, '%b %D, %Y') AS date_created, date_modified, homepage, about, location FROM users WHERE idUser='$id'";
    $result = mysql_query ($query, $dbConn);
    
    if (empty($result)){

        $error['noUser'] = "The requested user doesn't exist";
    
    }else{    
        $user = mysql_fetch_array ($result);
        unset($query, $result);
            
        // array $user to single vars
        $user = strip_slashes_arr($user);
        extract($user);
        unset ($user, $arr, $u);
        // and userpic
        $userpic = get_user_pic($idUser, 128);
    }
    
}else{ $error['noUser'] = "The requested user doesn't exist";}

// page info
$page_title = "NoClan: Home"; // used at 'includes/head.inc'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title><?php echo $page_title;?></title>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
    <meta name="Language" content="EN"/>
    <meta name="Robots" content="none"/>
    <link href='http://fonts.googleapis.com/css?family=Orbitron:400,500,700,900|Aldrich|' rel='stylesheet' type='text/css'/>
    <link rel="stylesheet" type="text/css" href="<?php echo rurl();?>/css/frame_style.css"/>
</head>

<body>
	<div id="main">

    <?php if (!empty($error)){?>
	    <div class="error">
			<?php foreach ($error as $e){?>
			<h2><?php echo $e;?></h2>
			<?php }?>
		</div>

	<?php }else{?>
			
		<div id="showuser">				
			<div id="userHeader">
				<img class="userpic" src="<?php echo $userpic?>"/>
				<h1><?php echo $username?><span class="label">(<?php echo $type;?>)</span></h1>					
				<h2><?php echo $first_name.' '.$last_name.' ('.$country.')';?></h2>
				<p><?php switch($type){
						case 'member':
							$i = '<strong>&bull;NC&bull;</strong> member ';
							break;
						case 'admin':
							$i = '<strong>&bull;NC&bull;</strong> member ';
							break;
						case 'friend':
							$i = 'Friend of NC ';
							break;
						case 'user':
							$i = 'NC user ';
							break;
							
				}echo $i?> since <?php echo $date_created;?>.
				</p>
				
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
				
				<div class="darkfield">
					<?php echo $about;?>
				</div>
				
			</div>	
			<br/>			
		</div><!-- /showuser -->
	<?php }?>					
	</div><!-- /main -->
</body>

</html>
