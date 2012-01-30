<?php
/* script included in list.php to delete message */

    $idDelete=mysql_real_escape_string($_GET['del']);
    $idUser=$arrUser['idUser'];
    
    // Delete only if it belongs to the current user.
    $q="DELETE FROM messages WHERE messages.to='$idUser' AND
        idMessage='$idDelete' LIMIT 1";
    $r=mysql_query($q, $dbConn);
    
    // Did we actually delete anything?
    if (($a=mysql_affected_rows())==1)
        $deleted='Message has been deleted!';
    else $error['deleted']="Message not found.";
?>
