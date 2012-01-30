<?php
function isOldUser ( $user, $password, $db_connection)
{
    /* CHECK WHAT KIND OF USER IS TRYING TO LOG IN
     * Asumes $user and $password are arranged
     * Returns the userdata array or false
     */
	
	// Do we have real data here?
	if ($user =='' || $password=='') return false;
		
	// Check for that user on the db
	$query = "SELECT idUser, username, oldPass, type FROM users WHERE username='$user'";
	$result = mysql_query ($query, $db_connection);
	
	if (!empty ($result)){
	    
		$user_row = mysql_fetch_array ($result);
		unset($query, $result);
		
		// Are we talking about the same password?
		if ( $user_row ['oldPass'] == $password) return $user_row;
		else return false;
			
	}else return false;	
}
?>
