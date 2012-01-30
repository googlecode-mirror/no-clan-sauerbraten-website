<?php
session_start ();
require_once 'admin/config.php';
require_once 'admin/connect.php';
require_once 'admin/functions.php';
require_once 'admin/isUser.php';
// db connection
$dbConn = connect_db();

// Is user connected? If not, kick out of here.
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);   
}else {
    $location = rurl();
    header("Location: $location");
	die;
}

// LOGIN SENT
if (!empty($_POST['submitLog'])) {
	include './includes/login.php';
}elseif (!empty ($_POST['submitQuit'])){ // Good Bye User Session
	include './includes/logout.php';
}

// SET idUser
$idUser = $arrUser['idUser'];

// SUBMIT USER DATA
if (!empty ($_POST['submitUserdata']))
{	

    // VAR CONTROL & ERROR
    if (!empty($_POST['username']))
    {
        $username = mysql_real_escape_string($_POST['username']); // Username from form set here
        
        // Hey, do you want to change your name?
        if ($arrUser['username'] != $_POST['username'])
        {
			// Look at users and preusers for this new username
			$query = "SELECT idUser FROM users WHERE username = '$username'";
			$result = mysql_query($query, $dbConn);
			$at_users = mysql_fetch_array($result);
			
			$query = "SELECT idPreuser FROM preusers WHERE username = '$username'";
			$result = mysql_query($query, $dbConn);
			$at_preusers = mysql_fetch_array($result);
			
			if (!empty($at_users) || !empty($at_preusers)){
				$error['changename'] = 'The user name you want to change is already in use.';
			}
			unset ($query, $result, $at_users, $at_preusers);
		}
    
	}
    else { $error['username'] = 'An username is needed'; }
    
    
    if (!empty($_POST['email']) && is_email_valid($_POST['email']))
    {
		$email = mysql_real_escape_string($_POST['email']);
		
		// Hey, do you want to change the email?
		if($arrUser['email'] != $_POST['email'])
		{
			// look at users and preusers for this new email
			$query = "SELECT idUser FROM users WHERE email = '$email'";
			$result = mysql_query($query, $dbConn);
			$at_users = mysql_fetch_assoc($result);

			$query = "SELECT idPreuser FROM preusers WHERE email = '$email'";
			$result = mysql_query($query, $dbConn);
			$at_preusers = mysql_fetch_assoc($result);
			
			if (!empty($at_users) || !empty($at_preusers)){
				$error['changemail'] = 'The email you want to change is already in use.';
			}
			unset ($query, $result, $at_users, $at_preusers);
		}
		
	}else $error['email'] = 'A valid email is required.';
        
        
        
    if (!empty($_POST['first_name']))
        $first_name = mysql_real_escape_string($_POST['first_name']);
        else $first_name = '';
        
    if (!empty($_POST['last_name']))
        $last_name = mysql_real_escape_string($_POST['last_name']);
        else $last_name = '';
        
    if (!empty($_POST['country']))
        $country = mysql_real_escape_string($_POST['country']);
        else $error['country'] = 'A country is needed.';
                
    if (!empty($_POST['homepage']) && $_POST['homepage'] != 'http://')
        $homepage = mysql_real_escape_string($_POST['homepage']);
        else $homepage = 'http://';
        
    if (!empty($_POST['location']))
        $location = mysql_real_escape_string($_POST['location']);
        else $location = '';
        
    if (!empty($_POST['about'])){
        $about = process_mini_editor($_POST['about']);
        $about = mysql_real_escape_string($about);
    }else $about = '';
      
    if (!empty($_POST['notify']) && $_POST['notify'] == '1'){
        $notify = '1';
	}else $notify = '0';
     
	if (!empty($_POST['picasaUser'])){
		$picasaUser = mysql_real_escape_string($_POST['picasaUser']);
	}else $picasaUser = '';
    
    // No errors? Go on
    if (empty($error))
    {
        // First, check if user wants to change the password
        if (!empty($_POST['currentPass']))
        {
            $currentPass = get_pass($_POST['currentPass']);
            
            // Is the sent current password the same we have stored?
            if ($arrUser['pass'] != $currentPass){

                $error['currentPass'] = 'The current password you sent does not match with your password.';              
            
            }else{
                
                // Ok, you want to change the password. Let's see if we have new passwords...
                if (!empty($_POST['newPass']) && !empty($_POST['newRePass'])){
                    // ... and are the same
                    if ($_POST['newPass'] != $_POST['newRePass']){
                        $error['rePassword'] = 'New passwords you sent do not match';
                    }else{
                        // creating the new password
                        $newPass = get_pass($_POST['newPass']);                       
                    }
                    
                }else{$error['changePass'] = 'New password is needed in order to change password';}
            }
        }
                
        // Still no errors? really? ok, go on
        if (empty($error))
        {
            // Preparing the query without password changes...
            if (empty($newPass)){
                $query = "UPDATE users SET username = '$username', first_name = '$first_name', last_name = '$last_name', 
                email = '$email', country = '$country', homepage = '$homepage', location = '$location', about = '$about',
                notify = '$notify', picasaUser = '$picasaUser' WHERE idUser = $idUser";
                
                // UPDATE TABLE AND SESSION
                $result = mysql_query ($query, $dbConn);
                $_SESSION['NC_user'] = $username;
            
            }else{
                // .. or with the new password
                $query = "UPDATE users SET username = '$username', first_name = '$first_name', last_name = '$last_name', 
                email = '$email', country = '$country', homepage = '$homepage', location = '$location', about = '$about', 
                notify = '$notify', pass = '$newPass', picasaUser = '$picasaUser' WHERE idUser = $idUser";

                // UPDATE TABLE and SESSION
                $result = mysql_query ($query, $dbConn);
                $_SESSION['NC_password'] = $newPass;
            }
                        
			$location = rurl().'/user-settings.php?done=update';
			header("Location: $location");
			die;
			
        }
    }
}

/* * * * * * * * * * 
 * IMAGE MANAGEMENT 
 * * * * * * * * * */

// FILES
$dirTmpUser = 'data/tmp_data/'.$arrUser['idUser'];
$dirImgUser = 'images/users';
$imgName = $arrUser['idUser'].'-img128.png';
$urlImgUser = $dirImgUser.'/'.$imgName;
$urlTmpImg = $dirTmpUser.'/'.$arrUser['idUser'].'-temp_img.jpg';

if (!is_dir($dirTmpUser)) mkdir($dirTmpUser, 0777);

// PIXELS
// max witdh for the image for work on the crop (not the one user uploads)
$sourceWidth = 620;
// width of the result image cropped
$newWidth = 128;
$newHeight = 128;

// SUBMIT UPLOAD
if (!empty($_POST['submitUpload']))
{
    // Need a dir to work with
    if (!is_dir($dirTmpUser)) mkdir($dirTmpUser, 0777);
    
    if (is_uploaded_file($_FILES['file']['tmp_name']) && $_FILES['file']['error'] == 0)
    {
        // remember the image user has upload
            $imgsrc = $_FILES['file']['tmp_name'];
            $ftype = $_FILES['file']['type'];
            
            // is file small enough?
            if ($_FILES['file']['size'] > 5242880){
                
                $error['filesize'] = 'Uploaded image is too big to handle';
                
            }else{
                // ok, file is small enough, so...
                // Check the upload and make a picture of that if you can you evil server
                    
				switch ($ftype){
					case 'image/jpeg':	$source=imagecreatefromjpeg($imgsrc); break;
					case 'image/png' :	$source=imagecreatefrompng($imgsrc);  break;
					default: $error['format']='Image format not supported';
				}
                        
				if (empty ($error)){
						// Resize the image to a working width
						$img = resize_img($source, $sourceWidth);
						// Destroy the source
						imagedestroy($source);
						// Leave a copy on the user temp dir
						imagejpeg($img, $urlTmpImg);
						imagedestroy($img);
						// Reload
						header( "Location: user-settings.php?uploaded=true" );
						die;                    
                } 
            }
            
    }else{
        $error['uploading'] = 'Error uploading image.';
    }
}

// SUMBIT CANCEL
if (!empty($_POST['submitCancel']))
{//me carrego lo directori temporal del usuari
        if (is_dir($dirTmpUser)) delete_dir($dirTmpUser);
        header( "Location: user-settings.php" );
        die;
}

// SUBMIT CROP
if (!empty ($_POST['submitCrop'])){
        // coords of the image
        $x1 = $_POST["x1"];
        $y1 = $_POST["y1"];
        $x2 = $_POST["x1"];
        $y2 = $_POST["y1"];
        
        $width  = ($_POST['x2']-$_POST['x1']);
        $height = ($_POST['y2']-$_POST['y1']);
        
        // Draw an image to make the cropping from the tmp image on the tmp userdir
        $img = imagecreatefromjpeg($urlTmpImg);

        // Create a pseudo-image with new dimensions to draw over the cropped one
        $newImg = imagecreatetruecolor($newWidth, $newHeight);
        
        // Resize and crop
        imagecopyresampled($newImg, $img, 0, 0, $x1, $y1, $newWidth, $newHeight, $width, $height);
        
        // rm old user image
        if (file_exists($urlImgUser)) unlink($urlImgUser);
        // Save user image              
        imagepng($newImg, $urlImgUser);
        
        // Save the image with other dimensions
        $dimensions = array(96, 64, 48, 32, 24);
        foreach ($dimensions as $dim){
                $path = 'images/users/'.$arrUser['idUser'].'-img'.$dim.'.png';
                if (file_exists($path)) unlink($path);
                // Create a pseudo-image with new dimensions to draw over the cropped one
                $img = imagecreatefrompng($urlImgUser);
        $newImg = imagecreatetruecolor($dim, $dim);
        // Resize
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $dim, $dim, 128, 128);
        // Save user image              
            imagepng($newImg, $path);
        } 

        // RM all tmp GD images
        imagedestroy($newImg);
        imagedestroy($img);

        // RM tmp dirs
        if (is_dir($dirTmpUser)) delete_dir($dirTmpUser);

        header( 'Location: user-settings.php' );
        die;
}



// Get all user info and put it on $user
$query = "SELECT idUser, username, pass, email, type, first_name, last_name, country, DATE_FORMAT (date_created, '%b %D, %Y') AS date_created, date_modified, notify, homepage, location, about, picasaUser FROM users WHERE idUser='$idUser'";
$result = mysql_query ($query, $dbConn);
$user = mysql_fetch_array ($result);
unset($id, $query, $result);
// Convert the $user array to single vars
$user = strip_slashes_arr($user);
extract($user);

// page info
$page_title = "NoClan's $username info page"; // used at 'includes/head.inc'
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php include $_SERVER['DOCUMENT_ROOT'].'/includes/head.inc.php';?>
<script src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js" type="text/javascript"></script>
<script src="https://ajax.googleapis.com/ajax/libs/scriptaculous/1.9.0/scriptaculous.js" type="text/javascript"></script>
<script src="js/cropper/cropper.js" type="text/javascript"></script>
<script type="text/javascript" charset="utf-8">
	function onEndCrop( coords, dimensions ) {
		$( 'x1' ).value = coords.x1;
		$( 'y1' ).value = coords.y1;
		$( 'x2' ).value = coords.x2;
		$( 'y2' ).value = coords.y2;
		$( 'width' ).value = dimensions.width;
		$( 'height' ).value = dimensions.height;
	}
	Event.observe(window,'load',function() { 
			new Cropper.ImgWithPreview('testImage',	{ 
					minWidth: 128, 
					minHeight: 128,
					ratioDim: { x: 128, y: 128 },
					displayOnInit: true,
					onEndCrop: onEndCrop,
					previewWrap: 'previewArea'
			});});
</script>
<script type="text/javascript" src="js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<?php include('includes/tinyMce.inc.php')?>	
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
				    	<h3>Problems:</h3>
						<?php foreach ($error as $e){?>
						<p><?php echo $e;?></p>
						<?php }?>
					</div>
					<?php }?>
					<!-- /errors -->
					
					<div class="pic128">
    					<?php if (!empty($_GET['uploaded']) && empty($error)){?>
    					<div id="previewArea"></div>
    					<?php }else{
    					$userPage = rurl().'/user/'.$username.'/';?>
    					<a href="<?php echo $userPage;?>" title="<?php echo $username?>'s info page"><img src="<?php echo get_user_pic($idUser, 128);?>"/></a>
    					<?php }?>
					</div>
					
					<div>
						<?php if (empty($_GET['uploaded'])){?>
    					<form action="" method="post" enctype="multipart/form-data" style="margin-top: 1em;">
					    <label for="file">Chage your picture</label><br/>
    					    <label for="submitUpload" style="font-weight: normal; font-size: 0.8em;">Must be JPG or PNG and &lt; 5Mb.</label>
    					    <input type="file" name="file" size="18" style="width: 250px;" style="float: right;"/><br/>
    					    <input type="submit" name="submitUpload" value="Upload Image" class="submit"/>
    					</form>
    					<?php }else{?>
    					<h2>Select the area from the image you have upload to save your profile image.</h2>
    					<?php }?>
					</div>
											
				</div><!-- /"sidepanel" -->

				<div id="main">
				<h1><?php echo $username;?></h1>
				<p>Since <?php echo $date_created.' // '.$type;?></p>
				
				<?php if (empty($_GET['uploaded'])){?>
				
					<form action="" method="post">
						
						<div style="float: left; margin-right: 40px;">
							<label for="username">Username:</label><br/>
							<input name="username" type="text" maxlength="29" value="<?php canput($username);?>"/><br/>
							<label for="first_name">Firstname:</label><br/>
							<input name="first_name" type="text" maxlength="19" value="<?php canput($first_name);?>"/><br/>
							<label for="last_name">Lastname:</label><br/>
							<input name="last_name" type="text" maxlength="39" value="<?php canput($last_name);?>"/><br/>		
						</div>
						
						<div>
							<label for="email">e-mail:</label><br/>
							<input name="email" type="text" maxlength="79" value="<?php canput($email);?>"/><br/>							
							<label for="country">Country:</label><br/>
							<input name="country" type="text" maxlength="29" value="<?php canput($country);?>"/><br/>
							<label for="location">Location:</label><br/>
							<input name="location" type="text" maxlength="49" value="<?php canput($location);?>"/><br/>
						</div>
						
						<label for="homepage">Homepage:</label><br/>
						<input name="homepage" type="text" maxlength="99" style="width: 607px" value="<?php canput($homepage, 'http://');?>"/><br/>
						<label for="about">About me:</label><br/>
						
						<div class="editor">
						    <textarea name="about" class="miniEditor" style="width: 607px"><?php canput($about);?></textarea><br/>
						</div>
						
						<?php if ($arrUser['type'] == 'member' || $arrUser['type'] == 'admin'){?>
						<!-- Notifications -->
						<label for="notify">Notifications</label><br/>
						<input name="notify" style="float: left; margin-top: 2px;" type="checkbox" value="1" <?php if ($notify == '1') echo "checked=\"checked\"";?>/>
						<p>&nbsp;Notify me by email about my messages or polls.</p>
						<!-- /Notifications -->
						<?php }?>
						
						<!-- Change password -->
						<h2>Change password</h2>
						<p><em>If the password fields are left empty, current password remains.</em></p>
						<div style="float: left; margin-right: 28px;">
							<label for="currentPass">Current Password:</label><br/>
							<input name="currentPass" type="password" autocomplete="off" style="width: 180px;"/><br/>
						</div>
						<div style="float: left; margin-right: 28px;"">
							<label for="newPass">New password:</label><br/>
							<input name="newPass" type="password" autocomplete="off" style="width: 180px;"/><br/>
						</div>
						<div>
							<label for="newRePass">Repeat new password:</label><br/>
							<input name="newRePass" type="password" autocomplete="off" style="width: 180px;"/><br/>
						</div>
						<!-- /Change password -->
						
						<?php if ($arrUser['type'] == 'member' || $arrUser['type'] == 'admin'){?>
						<!-- PICASA SETTINGS -->
						<h2>Picasa Account</h2>
						<label for="picasaUser">Your user at <a target="_blank" href="https://picasaweb.google.com">picasa</a></label><br/>
						<input name="picasaUser" type="text" style="width: 10em; text-align: right; padding-right: 3px;" value="<?php canput($picasaUser, '');?>"/><em>@google.com</em><br/>
						<p><em>If you have a picasa account you can add a <strong>public album</strong>
						named as <strong>NC</strong>. All pictures on that album will be automagically
						displayed at the NC Gallery.</em></p>
						<!-- /PICASA SETTINGS -->
						<?php }?>
						
						<input type="submit" name="submitUserdata" value="Update my info!"/>			
					</form>
					
				<?php }else{?>
										
					<div id="testWrap">
						<img src="<?php echo $urlTmpImg;?>" alt="test image" id="testImage"/>
					</div>
					<br/>
					<form id="results" method="post">
						<input type="hidden" name="x1" id="x1"/>
						<input type="hidden" name="y1" id="y1" />
						<input type="hidden" name="x2" id="x2"/>
						<input type="hidden" name="y2" id="y2" />
						<input type="hidden" name="width" id="width"/>
						<input type="hidden" name="height" id="height"/>
						<div style="float: right;">
							<input name="submitCrop" type="submit" value="SAVE"/>
							<input name="submitCancel" type="submit" value="CANCEL"/>
						</div>
					</form>
					
				<?php }?>
				
				</div><!-- /main -->
			</div><!-- /content-->
			
			<div id="footer">
			<?php include 'includes/footer.inc.php';?>
			</div> <!-- /footer -->

		</div><!-- /container -->
	</div><!-- /wrapper -->

</body>

</html>
