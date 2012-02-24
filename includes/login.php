<?php

/* Faulty string to hex, that i used in noclan site 1.0.    */
/* It fails to add a leading zero in values less than 0x10. */
/* We are to safely remove it after all members have logged */
/* in with their new and more tastier passwords (more salt).*/
function strtohex($string)
{
    $hex='';
    for ($i=0; $i < strlen($string); $i++)
    {
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}


    // Variable arrangement ($username and $password) and $error settings
    if (!empty($_POST['username']) && $_POST['username'] != 'username') $username_form = mysql_real_escape_string($_POST['username']);
    else $error['username']="Please, enter your username.";
        
    if (!empty($_POST['password']) && $_POST['password'] != '********') {

        $password = get_pass($_POST['password']);
                
    } else {
    	$error['password']="Please, enter your password.";
    }
    
    // No errors? All arrranged? Bring back the userdata at $arrUser if that guy exists
    if (empty($error))
    {
        /* START OLD PASSWORD PATCH */
        require_once $_SERVER['DOCUMENT_ROOT'].'/admin/isOldUser.php';
	    // The faulty strtohex function in use... So that it produces the results of 1.0
        $oldPassword = strtohex(hash_hmac('sha256', $_POST['password'], 'c#haRl891', true));
    
        if ($arrUser = isOldUser($username_form, $oldPassword, $dbConn)){
            // Update the table with the new password
    		$idUser = $arrUser['idUser'];
    		// turn 0 the oldPass & update the new pass
            $query = "UPDATE users SET pass = '$password', oldPass='0' WHERE idUser = '$idUser'";
    		$result = mysql_query($query, $dbConn);			
    	}
    	/* END OLD PASWORD PATCH */	
    
        // Mount the user array (or false)
    	if ($arrUser = isUser($username_form, $password, $dbConn)){
    		if (empty($error)) { // $error means user is in limbo (Panda check this. Could it be from other reasons?)
				// and update last seen users (date_modify)
				$d = date("Y-m-d H:i:s");
				$query = "UPDATE users SET date_modified = '$d' WHERE idUser = {$arrUser['idUser']}";
				$result = mysql_query($query, $dbConn);
    	    
				// SESSION init
				$_SESSION['NC_user'] = $arrUser['username'];
				$_SESSION['NC_password'] = $arrUser['pass'];
				//$_SESSION['CSRF']=substr(md5(uniqid(rand( ), true)), 10, 15);
			
				// Location is the actual page except, obvioulsy, for the register page
				if (preg_match("/register.php/i", $_SERVER['REQUEST_URI'])){
					$location = rurl();
				}else{$location = rurl().$_SERVER['REQUEST_URI'];}
    		
				header("Location: $location");
				die;
    		} else $arrUser=false;
    	
    	}elseif (empty($error['limbo'])){
			
			$error['userpass'] = 'User or password do not match.<br />
			<span style="color: #c0c0c0;">If you forgot your username or password, please
			<a href="/forgot-password.php"> click here.</a></span>';
		}
    }
?>
