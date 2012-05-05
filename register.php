<?php
session_start ();
require_once 'admin/functions.php';
require_once 'admin/config.php';
require_once 'admin/connect.php';
require_once 'admin/isUser.php';

// db connection
$dbConn = connect_db();

// Is user connected?
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password']))
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);

// LOGIN SENT
if (!empty($_POST['submitLog'])) {
	include './includes/login.php';
}elseif (!empty ($_POST['submitQuit'])){ // Good Bye User Session
	include './includes/logout.php';
}


// Captcha
include_once rdir().'/captcha/securimage.php';
// Send emails
include_once rdir().'/admin/send_mail.php';
// Country list
include_once rdir().'/admin/includes/country_list.php';

// GET THE CAPTCHA IMAGE
$securimage = new Securimage();

// CLEAN PREUSERS TABLE. They had 4 days to complete registration process
$query = "DELETE FROM preusers WHERE DATE(date) < DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY)";
$result = mysql_query($query, $dbConn);

// ACTIVATION CODE IS RECIEVED
if(!empty($_GET['actCode']))
{
	$code = mysql_real_escape_string($_GET['actCode']);
	// GET PREUSER DATA
	$query = "SELECT idPreuser, username, email, first_name, last_name, country, pass, homepage FROM preusers WHERE code = '$code'";
	$result = mysql_query($query, $dbConn);
	
	if (!empty($result) && is_array($row = mysql_fetch_assoc($result))){

		extract($row, EXTR_PREFIX_ALL, "u");

	}else $error['preuser'] = 'The activation link is invalid or out of date';
	
	if(empty($error)){		

		// INSERT PREUSER DATA ON USERS TABLE
		$query = "INSERT INTO users (username, email, first_name, last_name, country, pass, homepage) VALUES 
		('$u_username', '$u_email', '$u_first_name', '$u_last_name', '$u_country', '$u_pass', '$u_homepage')";
		$result = mysql_query($query, $dbConn);
		
		// Remove Preuser
		$query = "DELETE FROM preusers WHERE idPreuser = '$u_idPreuser'";
		$result = mysql_query($query, $dbConn);
		
		// Finish all
		$location = rurl().'/register.php?activation=done';
		header( "Location: $location" );
	}

}

// FROM NOW ON, WE USE THIS
$reg_username = '';
$reg_email = '';
$reg_country = '';
$captcha_code = '';
$reg_first_name = '';
$reg_last_name = '';
$reg_homepage = '';

// REGISTRATION SENT
if (!empty($_POST['submitRegister']))
{
	if (!empty($_POST['reg_username']))
	{
		$reg_username = trim($_POST['reg_username']);

		// Kick NC trolls
		if (substr(strtolower($reg_username),0,4) == '-nc-' || substr(strtolower($reg_username),0,2) == 'nc') {
			
			$error['NC'] = 'The NC tag is only for members and trolls. And we don\'t want trolls here.';
			$reg_username = '';
			
		}else{
			// Check if username exists ...
			$reg_username = mysql_real_escape_string($reg_username); // We have username stored HERE
			// at users ...
			$query = "SELECT username FROM users WHERE username = '$reg_username'";
			$result = mysql_query($query, $dbConn);
			$username = mysql_fetch_assoc($result);
			// or preusers
			$query = "SELECT username FROM preusers WHERE username = '$reg_username'";
			$result = mysql_query($query, $dbConn);
			$preusername = mysql_fetch_assoc($result);
			
			if (!empty($username) || !empty($preusername)){
				$error['user'] = 'The username is in use';
				$reg_username = '';
			}
			
			unset($query, $result, $username, $preusername);
		}
	
	}else{ $error['reg_username'] = 'An valid username is needed.'; }
	
	// email
	if (!empty($_POST['reg_email']) && is_email_valid($_POST['reg_email'])){
		
		$reg_email = mysql_real_escape_string($_POST['reg_email']);
		
		// Check if email exists ...
		$reg_email = mysql_real_escape_string($reg_email); // We have username stored HERE
		// at users ...
		$query = "SELECT email FROM users WHERE email = '$reg_email'";
		$result = mysql_query($query, $dbConn);
		$userEmail = mysql_fetch_assoc($result);
		// or preusers
		$query = "SELECT email FROM preusers WHERE username = '$reg_email'";
		$result = mysql_query($query, $dbConn);
		$preuserEmail = mysql_fetch_assoc($result);
		
		if (!empty($userEmail) || !empty($preuserEmail)){
			$error['email'] = 'The e-mail is already in use.';
			$reg_email = '';
		}
		
	}else{ $error['reg_email'] = 'A valid email address is needed.'; }
	
	// Password
	if (!empty($_POST['reg_pass']) && !empty($_POST['reg_repass']))
	{
		// same passwords
		if(strlen($_POST['reg_pass']) < 8){ $error['shortpass'] = 'Pick a longer password, please. At least 8 characters.'; }
		elseif ($_POST['reg_pass'] != $_POST['reg_repass']){ $error['pass'] = 'The passwords do not match.'; }
		else{
			$reg_pass = get_pass($_POST['reg_pass']); // We have password;
		}
				
	}else { $error['password'] = 'A password is required.'; }
	
	// country
	if (!empty($_POST['reg_country'])){
		foreach ($country_list as $country){
			if($_POST['reg_country'] == $country){
				$reg_country = mysql_real_escape_string($_POST['reg_country']); // We have country.
			} 
		} 
	} else { $reg_country = ''; }
	
	// Captcha
	if (!empty($_POST['captcha_code'])){
		
		if ($securimage->check($_POST['captcha_code']) == false) { $error['captcha'] = 'The captcha code is wrong.'; }
		
	}else{ $error['captcha_code'] = 'You have to enter the captha code.'; }
	
	// not required
	if (!empty($_POST['reg_first_name']))	{ $reg_first_name 	= mysql_real_escape_string($_POST['reg_first_name']); }
	if (!empty($_POST['reg_last_name']))	{ $reg_last_name 	= mysql_real_escape_string($_POST['reg_last_name']); }
	if (!empty($_POST['reg_homepage']))		{ $reg_homepage 	= mysql_real_escape_string($_POST['reg_homepage']); }
	
	// STORE TEMPORAL USERDATA AND SEND A VALIDATION CODE
	if (empty($error))
	{
		// GET A VALIDATION CODE
		$code = get_random_string().time().get_random_string();
		
		// INSERT ON PREUSERS
		$query = "INSERT INTO preusers (username, pass, email, first_name, last_name, country, homepage, code) VALUES ('$reg_username', '$reg_pass', '$reg_email', '$reg_first_name', '$reg_last_name', '$reg_country', '$reg_homepage', '$code')";
		$result = mysql_query($query, $dbConn);
		unset ($query, $result);
		
		// SEND A MAIL WITH THE VALIDATION URL
		$to = strip_tags($reg_email);
		$subject = "No Clan: Complete your registration process";
		$link = rurl().'/register.php?actCode='.$code;
		
		$nc = rurl();
		$body = trim("Hi $reg_username!<br/>\n
					To complete your registration process at No Clan site 
					you have to activate your account by clicking the following link:<br/>\n
					<a href='$link'>$link</a><br/>\n
					<br/>\n
					(or copy&paste the url on your browser)<br/>\n<br/>\n
					And please, know that <strong>registration at our site does not mean you are a clan member or that you may use -NC- tag to play.</strong><br/>\n<br/>\n
					Thank you for your registration.<br/>\n
					No Clan - <a href='$nc'>$nc</a><br/>\n<br/>\n");
		if (send_mail($to, $subject, $body)){
			
			$location = rurl().'/register.php?code=sent';
			header( "Location: $location" );
			
		}else{
			
			$error['send_mail'] = 'There was a problem on the validation email process. Did you sent a valid email?';
			
		}
	}

}

// page info
$page_title = "NoClan: Registration"; // used at 'includes/head.inc'

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
						<?php foreach ($error as $e){?>
						<p><?php echo $e;?></p>
						<?php }?>
					</div>
					<?php }?>
					<!-- /errors -->
					
					<div><!-- sidepanel include -->
                        <?php include rdir().'/includes/sidepanel.inc.php';?>
                    </div>
				</div><!-- /"sidepanel" -->

				<div id="main">
					
				<?php if (!empty($_GET['code']) && $_GET['code'] == 'sent'){ ?>
				
					<div style="background: url('css/art/white10.png'); padding: 1em;">
						<h1>REGISTRATION IS ALMOST COMPLETE</h1>
						<p>Check you email. We have sent you instructions for complete the
						registration process and validate your account.</p>
						<p>REMEMBER: You have 2 days to complete registration.</p>
						<p>See you soon,</p><p><strong>-No Clan-</strong></p>
					</div>
					
				<?php } elseif (!empty($_GET['activation']) && $_GET['activation'] == 'done'){ ?>
				
					<div style="background: url('css/art/white10.png'); padding: 1em;">
						<h1>Registration is complete!</h1>
						<p>Your account at <strong>noclan.nooblounge.net</strong> is activated.</p>
						<p>Once you log in, you can update your user info (the link is your username).</p>
						<p>Thank you!</p><p><strong>-No Clan-</strong></p>
					</div>
					
				<?php } else { ?>

					<h1>Registration</h1>
					
					<div id="register">
					
						<form action="register.php" method="post">
							
							<div style="float: left">
								<label for="reg_username">Username <span style="color: red">*</span></label><br/>
								<input name="reg_username" type="text" value="<?php canput($reg_username)?>" />
							</div>
							
							<div style="float: right">
								<label for="reg_email">email <span style="color: red">*</span></label><br/>
								<input name="reg_email" type="text" autocomplete="off" value="<?php canput($reg_email);?>" />
							</div>
							
							<div style="float: left">
								<label for="reg_pass">Password <span style="color: red">*</span></label><br/>
								<input name="reg_pass" style="width: 280px;" type="password" autocomplete="off" value="" />
							</div>
							
							<div style="float: right">
								<label for="reg_repass">Password confirmation <span style="color: red">*</span></label><br/>
								<input name="reg_repass" style="width: 280px;" type="password" autocomplete="off" value="" />
							</div>
							
							
							<div style="width: 280px; float: left;">
								<label for="captcha_code">Enter the captcha code <span style="color: red">*</span></label><br/>
								<img id="captcha" src="captcha/securimage_show.php" alt="CAPTCHA Image" style="border: solid 1px #600000; margin: 6px 2px;"/><br/>
								<input type="text" style="width: 12em;" name="captcha_code" maxlength="10"/><a style="float: right;" href="#" onclick="document.getElementById('captcha').src ='captcha/securimage_show.php?' + Math.random(); return false"> (reload)</a><br/>
							</div>
							
							<div style="float: right; width: 285px;">
								
								<label for="reg_country">Country <span style="color: red">*</span></label><br/>
								<?php /*
								<input name="reg_country" type="text" value="<?php canput($reg_country);?>" />
								*/?>
								<select name="reg_country">
									<option value=''>Select your country</option>
									<option value=''></option>
								<?php foreach ($country_list as $country){?>
									<option value="<?php echo $country;?>" <?php if($country == stripslashes($reg_country)) echo 'selected = "selected"';?>><?php echo $country; ?></option>';
								<?php }?>
								</select>
								
							</div>
							
							<div style="float: left; clear: left;">
								<label for="reg_first_name">Firstname</label><br/>
								<input name="reg_first_name" type="text" value="<?php canput($reg_first_name)?>" />
							</div>
							
							<div style="float: right;">
								<label for="reg_last_name">Lastname</label><br/>
								<input name="reg_last_name" type="text" value="<?php canput($reg_last_name)?>" />
							</div>
							<div style="clear: both;">
								<label for="reg_homepage">Homepage</label><br/>
								<input name="reg_homepage" style="width: 616px;" type="text" value="<?php canput($reg_homepage)?>" />
							</div>
							<br>
							<p style="font-style: italic; color: #153040;" >
								<strong>
								We noticed some problems with the captcha and the country selection and did some fixes. If you still cannot get it to work, please tell us in the game.
								</strong><br/><br/>
								
								1. Inputs with the <span style="color: red">*</span> mark are required. <br/>
								2. You need a valid email to complete registration process.<br/>
								3. By sending this info, you agree with the <a target="_blank" href="license-and-terms.php" title="License agreement and Terms of use">license agreement.</a><br/>
								4. <strong>Registration at our site does not mean you are a clan member or that you may use -NC- tag to play.</strong>
							</p>
							
							<div style="text-align: center">
								<input type="submit" name="submitRegister" value="SEND! to complete user registration process" />
							</div>
						</form>
					</div>
					
				<?php } ?>
				
				</div><!-- /main -->
			</div><!-- /content-->
			<div id="footer">
				<?php include 'includes/footer.inc.php';?>
			</div> <!-- /footer -->
		</div><!-- /container -->
	</div><!-- /wrapper -->

</body>

</html>
