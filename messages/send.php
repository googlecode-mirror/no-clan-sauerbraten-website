<?php
/*
   This script is called from message.php when the user has hit the 'Send'
   button.
*/

include_once '../admin/send_mail.php'; // for sending emails

    //Sanitize inputs
    if (isset($_POST['to_user'])) $to_user=mysql_real_escape_string($_POST['to_user']);
    else $error['to_user']='Please, select a recipient.';

    if (isset($_POST['subject'])) $subject=mysql_real_escape_string($_POST['subject']);

    if (isset($_POST['message']) && ($_POST['message']!="")) {
        if (!are_tags_closed($_POST['message'])) $error['message']='Tags need to be closed.';
        $message=mysql_real_escape_string(process_content($_POST['message']));
    } else $error['message']='Message cannot have an empty body.';

    //load sender's id
    if (isset($arrUser)) $idUser=$arrUser['idUser']; else $error['user']="Bad user!";

    //check to see if recipient exists
    $q="SELECT email, idUser, notify FROM users WHERE username='$to_user' LIMIT 1";
    if ((!$r=mysql_query($q, $dbConn)) || (mysql_num_rows($r)==0)) $error['db']='Error locating recepient\'s data.';
    else { // else load the recepient's data
        $arr=mysql_fetch_array($r);
        $notify=$arr['notify'];
        $recepient=$arr['idUser'];
        $to=$arr['email'];
    }

    if (isset($error)==0)
    {
        // everything ok, send the message
        $q="INSERT into messages values (NULL, '$recepient', '$idUser', CURRENT_TIMESTAMP, '$subject', '$message', '0')";
        if (($r=mysql_query($q, $dbConn))==0) $error['send']="Error sending the message.";
        
        if (($notify==1) && (isset($error)==0)) { //notify the recepient of his new incoming message
            $subject='New message at noclan.nooblounge.net';
            $body="Hey $to_user!<br />\n".
                  "You have a new message at No Clan's site! To read it go to http://noclan.nooblounge.net<br />\n".
                  "Have a nice day!";
            if (!send_mail($to, $subject, $body, '')) $error['email']='There was an error in sending the notification email.';
            
        }
        $sent=1;
    }

?>
