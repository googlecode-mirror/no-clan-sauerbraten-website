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
$page_title = "NoClan: License &amp; terms"; // used at 'includes/head.inc'
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
				<div id="license">
					<h1>License agreement</h1>
					<p>The hyperlinks or links provided do not imply the existence of relations between <strong>noclan.nooblounge.net</strong> and the owner of the web site linked nor acceptance and approval of its contents or services by <strong>noclan.nooblounge.net</strong>.</p>
					<p><strong>noclan.nooblounge.net</strong> excludes any responsibility for sites linked from this website. Since this site <strong>noclan.nooblounge.net</strong> neither can monitor nor control the linked content, assessment and use of the information, content and services on the linked sites will depend on the prudence of the user.</p>
					<p>This site <strong>noclan.nooblounge.net</strong> will not purposefully disclose private data or information required to identify individuals. Comments submitted voluntarily by users are not personal data nor private data but public. NC may retain this information in order to maintain the consistency of the information published.</p>
					<p>Access to and use of this website does not require prior registration or registration of users. However, in exceptional cases, access to certain services (posting comments, sending messages and participation in the forum) is subject to user registration.</p>
				</div>
				<div id="terms">
					<h1>Terms of use</h1>
					<p>First of all, know that <strong>registration at our site does not mean you are a clan member or that you may use -NC- tag to play</strong></p>
					<p>Users can use the services and information provided through this website is at the expense and risk.</p>
					<p>The user will not:</p>
					<ul style="list-style: disc; padding: 0.5em 2em;">
						<li>Write or provide defamatory, racist, obscene, pornographic or offensive links, promote or incite hatred or violence of any kind or affect the privacy or the rights of children.</li>
						<li>Use any of the services offered on <strong>noclan.nooblounge.net</strong> for illicit or harmful to the rights and interests of others  purposes or that may damage or impede the normal use of services, equipment or any content stored on noclan.noobloung.net or external servers linked from <strong>noclan.nooblounge.net</strong>.</li>
						<li>Harass, threaten and obtain or disclose private information to third parties.</li>
						<li>Use <strong>noclan.nooblounge.net</strong> the promotional purposes, political campaigns, ideological or commercial (although indirect advertising) and / or to charge unjustifiably or annoy other users and readers <strong>noclan.nooblounge.net</strong>.</li>
						<li>Create multiple accounts either for promotional purposes, to simulate views or impersonate others.</li>
						<li>Failure to comply with the terms of use may lead to blocking or deleting user account, and / or editing / deleting of offending text without prior notice.</li>
					</ul>
					<br/>
					<p><strong>noclan.nooblounge.net</strong> keeps the right to modify and update the conditions of use without prior notice, if it meets the intent of improving the service or to minimize any problems.</p>
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
