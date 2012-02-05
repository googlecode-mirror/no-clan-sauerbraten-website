<?php
function isUser ( $user, $password, $db_connection)
{
    /* CHECK WHAT KIND OF USER IS TRYING TO LOG IN
     * Asumes $user and $password are arranged
     * Returns the userdata array or false
     */
	
	// Do we have real data here?
	if ($user =='' || $password=='') return false;
		
	// Check for that user on the db
	$query = "SELECT idUser, username, pass, type, DATE_FORMAT (date_created, '%b %y') AS date_created, country, email, limbo, limbo_reason FROM users WHERE username='$user'";
	$result = mysql_query ($query, $db_connection);
	
	if (!empty ($result)){
	    
		$user_row = mysql_fetch_array ($result);
		unset($query, $result);
		
		// Are we talking about the same password?
		if ( $user_row ['pass'] == $password){
		    if ($user_row['limbo']=='in') {
				global $error;
				$reason=$user_row['limbo_reason'];
				$error['limbo']='Your account has been temporarily disabled. Reason: '.$reason;
		    }
		    // update date_modified to have last date logs
		    $id = $user_row['idUser'];
		    $d = date("Y-m-d H:i:s");
		    $query = "UPDATE users SET date_modified = '$d' WHERE idUser = '$id'";
		    $result = mysql_query ($query, $db_connection);
	        
			//Check if login IP is the same as current user IP.
			$IP=$_SERVER['REMOTE_ADDR'];
			if (isset($_SESSION['NC_IP'])) {
				if ($_SESSION['NC_IP']!=$IP) return false;
			} else $_SESSION['NC_IP']=$IP; // <-- this only happens once, at login.

		    if(empty($error)){
				// & return the userArray without slashes
				$user_row = strip_slashes_arr($user_row);
				return $user_row;
			}else return false;
		}
		else return false;
			
	}else return false;	
}
?>
