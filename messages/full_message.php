<?php
session_start ();
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/connect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/isUser.php';

// db connection
$dbConn = connect_db();

// LOGIN SENT
if (!empty($_POST['submitLog'])) include $_SERVER['DOCUMENT_ROOT'].'/includes/login.php';
elseif (!empty ($_POST['submitQuit'])) include $_SERVER['DOCUMENT_ROOT'].'/includes/logout.php';
// Is user connected?
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
}else{ go_home(); }
// ONLY ADMINS, MEMBERS AND FRIENDS ARE ALLOWED.
if (!empty($arrUser) && $arrUser['type'] == 'user') { go_home(); }

// If the user is sending a message...
if (!empty($_POST['message_post'])) include rdir().'/messages/send.php';
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
         //else try to GET recepient and subject (useful for replies)
         if (!empty($_GET['to'])) $write_to = $_GET['to'];
         if (!empty($_GET['subject'])) $subject = $_GET['subject']; 
     }

// page info
$page_title = "NoClan: Send a message"; // used at 'includes/head.inc'
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php include $_SERVER['DOCUMENT_ROOT'].'/includes/head.inc.php';?>
</head>

<body>
	<div id="wrapper">
		<div id="container">

			<div id="header">
			</div><!-- /header -->
			
			<div id="menu">
				<?php
				include $_SERVER['DOCUMENT_ROOT'].'/includes/menu.inc.php';
				include $_SERVER['DOCUMENT_ROOT'].'/includes/userlog.inc.php';
				?>
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
                    <?php include $_SERVER['DOCUMENT_ROOT'].'/includes/sidepanel.inc.php';?>
				</div><!-- /"sidepanel" -->

				<div id="main">
				
        <?php //is the user logged in?
        if (isset($arrUser)){
            
            //Did he just send a message?
            if (isset($sent)) {?>
                
            <h1>Your message has been delivered</h1>
                
            <?php }else { //no? Just show the form ?>
                
            <h1>Compose a message</h1>
                    
            <form method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
                <fieldset style="padding: 1em;">
                            
                    <?php //Show drop-down with users
                        echo "Recepient: <select name='to_user'>\n";
                        echo "<option value='NULL'>Select Clan Member</option>\n";
                
                        $q = "select idUser, username from users where type!='user' order by idUser";
                        $r = mysql_query($q, $dbConn);
                        if (isset($_POST['to_user'])) $write_to=$_POST['to_user'];
                
                        while($row = mysql_fetch_array($r)) {
                            $uid = $row['idUser'];
                            $uname = $row['username'];
                            echo "<option";
                            if (!isset($write_to)) $write_to='NULL';
                            if ($uname==$write_to) echo " selected='selected'";
                                echo " value='$uname'>$uname</option>\n";
                        }
                        echo "</select><br>";
                    ?>
                
                    <!-- Show subject and message's body -->
                    <label for="subject">Subject:</label><br />
                    <input type="text" style="width: 100%" class="textfield" name="subject" id="subject" value="<?php if (isset($subject)) echo $subject;?>" /><br />
                            
                    <label for="message">Message:</label><br />
                    <textarea name="message" id="message" style="width: 100%; height: 5em;" onkeydown="if(this.value.length >= 2800){this.value = this.value.substring(0,2799); alert('Text up to 2799 chars'); return false; }"><?php if (isset($_POST['message'])) echo $_POST['message'];?></textarea><br />
                                
                    <input type="submit" name="message_post" value="Send" />
                </fieldset>
        	</form><?php } // end of form show
            
        } else {  // or user is not logged in.
            echo 'Sorry: You need to be logged in to send messages.';
        }?>
        
				</div><!-- /main -->

			</div><!-- /content-->
			
			<div id="footer">
				<?php include rdir().'/includes/footer.inc.php';?>
			</div> <!-- /footer -->

		</div><!-- /container -->
	</div><!-- /wrapper -->

</body>

</html>
