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

// Is user connected? Get the userArray (updates the login date at DB too).
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
}



// Captcha
include_once rdir().'/captcha/securimage.php';
// Send emails
include_once rdir().'/admin/send_mail.php';

// GET THE CAPTCHA IMAGE
$securimage = new Securimage();

// FROM NOW ON, WE USE THIS
$cName = ''; $cEmail = ''; $cSubject = ''; $cContent = ''; $cDate = ''; $cBody = '';

// REGISTRATION SENT
if (!empty($_POST['submitContact']))
	{
		if (!empty($_POST['cName'])){$cName = trim($_POST['cName']); }
		else{ $error['cName'] = 'A name is needed to send this contact form.'; }
		
		// email
		if (!empty($_POST['cEmail']) && is_email_valid($_POST['cEmail'])){
			$cEmail = $_POST['cEmail'];
		}else{ $error['cEmail'] = 'A valid email address is needed.'; }
		
		// subject
		if (!empty($_POST['cSubject'])){$cSubject = trim($_POST['cSubject']); }
		else{ $error['cSubject'] = 'Subject is needed.'; }
		
		// body
		if (!empty($_POST['cBody'])){

			$cBody = trim($_POST['cBody']);

		}else{ $error['cBody'] = 'No message, no contact. Please, write something if you want to contact.'; }
		
		// Captcha
		if (!empty($_POST['captcha_code'])){
			
			if ($securimage->check($_POST['captcha_code']) == false) { $error['captcha'] = 'The captcha code is wrong.'; }
			
		}else{ $error['captcha_code'] = 'You have to enter the captha code.'; }
		
		if (empty($error))
		{
			// SEND A MAIL WITH THE VALIDATION URL
			$to = array('annpanagiotidis@gmail.com', 'astrafo02@gmail.com');
			$subject = "NC CONTACT: $cName // $cSubject";
			$date = date("F d, Y / l");
			$cBody = process_content($cBody);
			$body = trim("$date<br/>\n<br/>\n
						Name: <strong>$cName</strong><br/>\n
						email: <strong>$cEmail</strong><br/>\n
						Subject: <strong>$cSubject</strong><br/>\n
						<br/>\n<br/>\n
						$cBody
						<br/>\n<br/>\n");
						
			if (send_mail($to, $subject, $body)){
				
				$location = rurl().'/contact.php?done=sent';
				header( "Location: $location" );
				
			}else{
				
				$error['send_mail'] = 'There was a problem on the validation email process. Did you sent a valid email?';
				
			}
		}
	}

// page info
$page_title = "NoClan: Contact"; // used at 'includes/head.inc'

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
					<div><!-- sidepanel include -->
                        <?php include rdir().'/includes/sidepanel.inc.php';?>
                    </div>
				</div><!-- /"sidepanel" -->

				<div id="main">

					<!-- ERRORS? -->
				    <?php if (!empty($error)){?>
				    <div style="color: #ffffff; border: solid 1px red; padding: 0.5em 1em; margin-bottom: 1em; background: url('<?php echo rurl();?>/css/art/black60.png');">
						<?php foreach ($error as $e){?>
						<p><?php echo $e;?></p>
						<?php }?>
					</div>
					<?php }?>
					<!-- /errors -->
					
				<?php if (!empty($_GET['done']) && $_GET['done'] == 'sent'){ ?>
				
					<div style="background: url('css/art/white10.png'); padding: 1em;">
						<h1>Your message has been sent</h1>
						<p>NC Team will contact you as soon as possible.</p>
						<p>Thank you, and see you soon.</p><p><strong>- NC Team -</strong></p>
					</div>
					
				<?php } else { ?>
					<h1>Contact NC Team</h1>
					
					<div id="register">
					
						<form action="contact.php" method="post">
							
							<div style="float: left;">
								<label for="cName">Name</label><br/>
								<input name="cName" type="text" value="<?php canput($cName);?>" />
							</div>
							<div style="float: right;">
								<label for="cEmail">email</label><br/>
								<input name="cEmail" type="text" value="<?php canput($cEmail);?>" />
							</div>
							
							<div style="clear: both;">
								<label for="cSubject">Subject</label><br/>
								<input name="cSubject" style="width: 616px;" type="text" value="<?php canput($cSubject);?>" />
							</div>
							
							<label for="body">Message</label><br/>
							<textarea name="cBody" style="width: 616px;" rows="8"><?php canput($cBody);?></textarea><br/>
							
							<div style="width: 285px; clear: both;">
								<img id="captcha" src="captcha/securimage_show.php" alt="CAPTCHA Image" style="border: solid 1px #600000; margin: 6px 2px;"/>
								<br/>
								<label for="captcha_code">Enter the captcha code <span style="color: red">*</span><a style="float: right;" href="#" onclick="document.getElementById('captcha').src ='captcha/securimage_show.php?' + Math.random(); return false"> (reload)</a></label><br/>
								<input type="text" style="width: 12em;" name="captcha_code" maxlength="10"/><br/>
							</div>
							<input type="submit" name="submitContact" value="&nbsp;SEND!&nbsp;" />
						</form>
						
						<p style="font-style: italic; color: #153040;" >
							1. You need a valid email to contact us.<br/>
							2. The information sent using this webform won't be stored at any database nor be published.<br/>
							3. You can take a look at the <a target="_blank" href="license-and-terms.php" title="License agreement">license agreement.</a>
						</p>
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
