<?php
/*
 *   forgot-password.php
 *   link to this file appears when there is a failed login attempt.
 *   step 1: fill your account's email. A resetCode link is sent there.
 *   step 2: the resetCode is submitted. You are prompted for a new pass.
 *   step 3: the password is updated.
 * 
 */
  
session_start ();
require_once 'admin/functions.php';
require_once 'admin/config.php';
require_once 'admin/connect.php';
require_once 'admin/isUser.php';

// db connection
$dbConn = connect_db();

// Is user connected? If so, why change password? Kick them out!
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])) {
    	$location = rurl();
		header( "Location: $location" );
}

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

// CLEAN pass_reset TABLE. They had 3 hours to complete password reset.
$query = "DELETE FROM pass_reset WHERE pass_reset.time < SUBTIME(CURRENT_TIMESTAMP, '03:00:00')";
$result = mysql_query($query, $dbConn);

// GET THE CAPTCHA IMAGE
$securimage = new Securimage();

// STEP 1. password reset request was given
if(!empty($_POST['email']))
{
	
	// Captcha correct?
	if (!empty($_POST['captcha_code'])){
		
		if ($securimage->check($_POST['captcha_code']) == false) { $error['captcha'] = 'The captcha code is wrong.'; }
		
	}else{ $error['captcha_code'] = 'You have to enter the captcha code.'; }
	
	// check if email belongs to a user
	$email = mysql_real_escape_string($_POST['email'], $dbConn);
	$q='SELECT username, email FROM users WHERE email="'.$email.'" LIMIT 1';
	if ($r=mysql_query($q, $dbConn)) {
		$row=mysql_fetch_array($r);
	    if (is_array($row)) {		
			//found an account with a matching email.
			$username=$row['username'];
			$email=$row['email'];
		} else $error['nouser']='This email doesn\'t belong to a registered user.';
	}else $error['mysql'] = 'Bad database connection.';
	
	if(empty($error)){		

        // mysql fields to complete are id, time, email, resetcode
		$code = get_random_string().time().get_random_string();
		$q="INSERT INTO pass_reset values (NULL, CURRENT_TIMESTAMP, '$email', '$code')";
		if ($r=mysql_query($q, $dbConn)) {
			$headers =  'From: No Clan <sauerbraten.no.clan@gmail.com>' . "\n"
					   .'Reply-To: webmaster@example.com' . "\n"
                       .'X-Mailer: PHP/' . phpversion() . "\n"
			           .'MIME-Version: 1.0' . "\n"
					   .'Content-type: text/html; charset=iso-8859-1' . "\r\n"; 
			$to = strip_tags($email);
		    $subject = "No Clan: Password reset request";
			$link = rurl().'/'.htmlentities($_SERVER['PHP_SELF']).'?resetCode='.$code;
		
			$nc = rurl();
			$body = trim("Hi $username!<br/>\n
						You seem to have forgotten your account's details.
						Your registered username is: $username<br />\n
						To select a new password, you have to follow this link:<br />\n
						<a href='$link'>$link</a><br />\n
						<br />\n
						(or copy&paste the url on your browser)<br />\n
						Please take note that this code is temporary and
						only lasts for 3 hours. After this period, you will
						have to request a new password reset.<br />\n
						<br />\n
						If you didn't request a password reset and you
						want to keep your current password, you may ignore
						this email.<br />\n
						<br />\n
						See you soon.<br/>\n
						No Clan - <a href='$nc'>$nc</a><br/>\n<br/>\n");
			if (mail($to, $subject, $body, $headers, '-f sauerbraten.no.clan@gmail.com')){
			
				// Finish all
				$request='sent';
			} else $error['bademail']='We couldn\'t send the email.';

		} else $error['mysql']='Request failed. Have you already requested a password reset?';
	}
}

// STEP 2. they followed the link in their email
if (!empty($_GET['resetCode']))
{
	$code=mysql_real_escape_string($_GET['resetCode'], $dbConn);
	$q='SELECT idUser, username FROM users where email=(SELECT email FROM pass_reset
	    WHERE resetcode="'.$code.'")';
	if ($r=mysql_query($q, $dbConn)) {
		if (mysql_num_rows($r)==1) {
			$row=mysql_fetch_array($r);
			extract($row);
			$request='resetCode';
		} else $error['toolate']='The reset code was not found. Usually the request is deleted after 3 hours.';
	} else $error['mysql']='Error quering database.';
}

// STEP 3. They filled the form for new password
if (!empty($_POST['newPassword'])) {
	
	//passwords match?
	if ((!empty($_POST['pass1'])) && (!empty($_POST['pass2'])) && ($_POST['pass1']==$_POST['pass2'])){
        if(strlen($_POST['pass1']) < 8){ $error['shortpass'] = 'Pick a longer password, please. At least 8 characters.'; }
		else{
			$reg_pass = get_pass($_POST['reg_pass']); // We have password;
		}
	} else {
		$error['pass']='You must select a new password and fill it
	                       in both input boxes';
	    $request='resetCode';
	}
	                       
	//code is ok?
	if (!empty($_POST['code'])) $code=mysql_real_escape_string($_POST['code'], $dbConn);
	else $error['code']='The resetCode was not passed along with the passwords.';
	                      
	if (empty($error)) {
		//the code exists in pass_reset table?
		$q='SELECT email FROM pass_reset WHERE resetcode="'.$code.'"';
		if ($r=mysql_query($q, $dbConn)) {
				if (mysql_num_rows($r)==1) {
					$row=mysql_fetch_array($r);
					$email=$row['email'];
					$q='UPDATE users set pass="'.$newPass.'" where email="'.$email.'"';
					if ($r=mysql_query($q, $dbConn)) {
						// we are done, delete the pass_reset code
						$q="DELETE FROM pass_reset where email='$email'";
						$r=mysql_query($q, $dbConn);
						$request='complete';
					} else $error='Error updating user\'s password.';
				} else $error['notfound']='User has not requested a password change.';
		} else $error['mysql']='Bad database connection';
	}
}

// page info
$page_title = "NoClan: Password recovery"; // used at 'includes/head.inc'

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php include $_SERVER['DOCUMENT_ROOT'].'/includes/head.inc.php';?>
</head>

<body>
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
					
				<?php if (!empty($request) && $request == 'sent'){ ?>
				
					<div style="background: url('css/art/white10.png'); padding: 1em;">
						<h1>PASSWORD RESET REQUEST ACCEPTED</h1>
						<p>Check you email. We have sent you your username and
						   instructions about reseting your password at
						   <?php echo $email; ?>.</p>
						<p>See you soon,</p><p><strong>-No Clan-</strong></p>
					</div>
					
				<?php 
				
				} else if (!empty($request) && $request == 'resetCode'){ ?>
				
					<div id="recover">
						<h1>PASSWORD CHANGE</h1>
						<p>Please enter your new password in the fields below.</p>

						<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
							
							<div style="float: middle">
								<label for="pass1">Enter new password:<span style="color: red">*</span></label><br/>
								<input name="pass1" type="password" value="" />
							</div>
							
							<div style="float: middle">
								<label for="pass2">Re-enter new password:<span style="color: red">*</span></label><br/>
								<input name="pass2" type="password" value="" />
							</div>
							
							<br />
							<div style="text-align: middle">
								<input type="hidden" name="code" value="<?php echo $code; ?>"/>
								<input type="submit" name="newPassword" value="New password &rarr;" />
							</div>
						</form>
					</div>
					

		<?php	} else if (!empty($request) && $request == 'complete'){ ?>
		
					<div style="background: url('css/art/white10.png'); padding: 1em;">
						<h1>PASSWORD SUCCESSFULLY CHANGED</h1>
						<p>You successfully updated your password!</p>
						<p>Have a nice day,</p><p><strong>-No Clan-</strong></p>
					</div>
					
		<?php	} else { ?>
					
					<div id="recover">
						<h1>PASSWORD RECOVERY</h1>
						<p>If you have forgotten your username or your password, you may be able to login
						   again by filling the form below and following the instructions.</p>
						<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
							
							<div style="float: middle">
								<label for="email">My email is <span style="color: red">*</span></label><br/>
								<input name="email" type="text" value="" />
							</div>

							
  						    <div style="width: 280px; float: middle">
								<label for="captcha_code">Type the code shown <span style="color: red">*</span></label><br/>
								<img id="captcha" src="captcha/securimage_show.php" alt="CAPTCHA Image" style="border: solid 1px #600000; margin: 6px 2px;"/><br/>
								<input type="text" style="width: 12em;" name="captcha_code" maxlength="10"/><a style="float: right;" href="#" onclick="document.getElementById('captcha').src ='captcha/securimage_show.php?' + Math.random(); return false"> (reload)</a><br/>
							</div>
							<div style="text-align: middle">
								<input type="submit" name="submitRegister" value="Reset password &rarr;" />
							</div>
						</form>
						<br />
						<p>If you don't remember your email, or have lost
						   your access to it, you will have to <a href='/register.php'>register again here</a>.</p>
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
