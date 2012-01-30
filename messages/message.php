<?php
session_start ();
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/connect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/isUser.php';

// db connection
$dbConn = connect_db();

// Is user connected?
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
}else{ go_home(); }
// ONLY ADMINS, MEMBERS AND FRIENDS ARE ALLOWED.
if (!empty($arrUser) && $arrUser['type'] == 'user') { go_home(); }

// If the user is sending a message...
if (!empty($_POST['message_post'])) include rdir().'/messages/send.php';
elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
         //else try to GET recepient
         if (!empty($_GET['to'])) $write_to = $_GET['to'];
         // if trying to reply, get source message
         if (!empty($_GET['reply']) && isset($arrUser)) {
			 $idMessage = $_GET['reply'];
			 $idUser=$arrUser['idUser'];
			 $q="SELECT messages.from, subject, message, time FROM messages
			 WHERE idMessage='$idMessage' AND messages.to='$idUser'
			 LIMIT 1";
			 $r=mysql_query($q, $dbConn);
			 if (mysql_num_rows($r)==1) {
				 $row=mysql_fetch_array($r);
				 $write_to=$row['from'];
				 $subject='RE: '.$row['subject'];
				 $time=$row['time'];
				 
				 //since we actually found it, load sender's username
                 // no error checking, but do we really have to?
	             $username_query="SELECT username FROM users WHERE
	                             idUser='$write_to' LIMIT 1";
	             $r2=mysql_query($username_query, $dbConn);
	             $usernameArr=mysql_fetch_array($r2);
	             $write_to=$usernameArr['username'];
				 $message_head="\n\n--------------------------------------------\n
On $time, $write_to wrote:\n\n";
				 $message=$message_head.strip_tags($row['message']);
		     }
	     } 
     }

// page info
$page_title = "NoClan: Messages"; // used at 'includes/head.inc'
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
                
                        $q = "select idUser, username from users WHERE type!='user' order by idUser";
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
                    <input type="text" style="width: 100%" class="textfield"
                           name="subject" id="subject" value="<?php if (isset($subject)) echo $subject;?>" /><br />
                            
                    <label for="message">Message:</label><br />
                    <textarea name="message" id="message" style="width: 100%; height: 5em;"
                              onkeydown="if(this.value.length >= 2800){this.value = this.value.substring(0,2799); alert('Text up to 2799 chars'); return false; }"
                              ><?php if (isset($_POST['message'])) echo $_POST['message']; else if (isset($message)) echo $message?></textarea><br />
                                
                    <input type="submit" name="message_post" value="Send" />
                </fieldset>
        	</form><?php } // end of form show
            
        } else {  // or user is not logged in.
            echo 'Sorry: You need to be logged in to send messages.';
        }?>
        
	</div><!-- /main -->
</body>

</html>
