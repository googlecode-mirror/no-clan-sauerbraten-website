<?php
	session_start ();
	require_once 'functions.php';
	require_once 'config.php';
	require_once 'connect.php';
	require_once 'isUser.php';
	// db connection
	$dbConn = connect_db();

	// Is user connected? Is admin? no? go home then.
	if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
		$arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
		if ($arrUser['type'] != 'admin') go_home();
		else $userId = $arrUser['idUser'];
	} else { go_home(); }
	
	//filter for showing specific groups of users
	if (!empty($_GET['filter']) && ($_GET['filter']!='All')) {
		$wanted_type=mysql_real_escape_string($_GET['filter']);
	}
	
	/* * * * * * *
	 * LIMBO
	 * * * * * * */
	//code for getting someone out of limbo
	if (!empty($_GET['outlimbo'])) {
		$idUser=mysql_real_escape_string($_GET['outlimbo']);
		$q="UPDATE users SET limbo='out', limbo_reason='' WHERE idUser='$idUser' LIMIT 1";
		$r=mysql_query($q);
	}	
	
	//code for placing someone into limbo
	if (!empty($_GET['inlimbo']) && (!empty($_GET['reason']))) {
		$idUser=mysql_real_escape_string($_GET['inlimbo']);
		$reason=mysql_real_escape_string($_GET['reason']);
		$q="UPDATE users SET limbo='in', limbo_reason='$reason' WHERE idUser='$idUser' LIMIT 1";
		$r=mysql_query($q);
	}
	
	/* * * * * * *
	 * EDITORS
	 * * * * * * */
	//code for getting someone out of editors
	if (!empty($_GET['outEditor'])) {
		$idEditor=mysql_real_escape_string($_GET['outEditor']);
		$q="DELETE FROM editors WHERE idEditor='$idEditor' LIMIT 1";
		$r=mysql_query($q);
	}	
	
	//code for placing someone into editors
	if (!empty($_GET['inEditor'])) {
		$idEditor=mysql_real_escape_string($_GET['inEditor']);
		$q="INSERT INTO editors (idEditor) VALUES ('$idEditor')";
		$r=mysql_query($q);
	}
	
	
	// UPDATE the user type
	if ( !empty($_GET['idUser']) && !empty($_GET['type'])) {
		$type=mysql_real_escape_string($_GET['type']);
		$idUser=mysql_real_escape_string($_GET['idUser']);
		$q="UPDATE users SET type='$type' where idUser='$idUser' LIMIT 1";
		$r=mysql_query($q);
	}
		
	// Construct the query
	$q='SELECT idUser, username, type, date_modified, limbo, limbo_reason, idEditor FROM users LEFT JOIN editors ON idUser = idEditor';
	// if we have a 'filter by type', add that to the query
	if (isset($wanted_type)) $q.=" WHERE type='$wanted_type'";
	else $wanted_type='';
	$q.=' ORDER BY idUser';
		
	if ($r=mysql_query($q, $dbConn)) { ?>
		<p><table id="User list" style="width: 100%;">
			<tr><th>id</th><th>Username</th>
				<th>Type   </th><th style="text-align: center;">Latest login</th>
				<th>Limbo</th>
				<th>Editor</th>
			<?php
			$i = 0;
			while ($row=mysql_fetch_array($r)) {
				extract($row); ?>
			    <tr <?php if ($i%2==0) echo 'class="i"';?>><td style="text-align: center;"><?php echo $idUser;   ?></td>
								
				<td style="text-align: left;"><?php echo htmlentities($username); ?></td>
				<td><select name='<?php echo htmlentities($username); ?>' id='<?php echo $idUser; ?>'  onchange="javascript: UpdateUser(this);">
							   
					<option value="admin"  <?php if ($type=='admin')  echo 'selected="selected"'; ?>>Admin</option>
					<option value="member" <?php if ($type=='member') echo 'selected="selected"'; ?>>Clan member</option>
					<option value="friend" <?php if ($type=='friend') echo 'selected="selected"'; ?>>NC friend</option>
					<option value="user"   <?php if ($type=='user')   echo 'selected="selected"'; ?>>Registered user</option> 
								   
				</select></td>
				<td><?php echo htmlentities($date_modified); ?></td>
				<td>
					<?php
					$isInButton = '<div class="redButton">&nbsp;</div>';
					$isOutButton = '<div class="greenButton">&nbsp;</div>';
					$canInButton = '<div class="canRedButton" title="Kick to limbo!">&nbsp;</div>';
					$canOutButton = '<div class="canGreenButton" title="Go back to users!">&nbsp;</div>';
					if ($limbo=='in')
						 echo "$isInButton <a href='javascript:outlimbo(\"$idUser\",\"$username\");'>$canOutButton</a>";
					else echo "<a href='javascript:inlimbo(\"$idUser\",\"$username\");'>$canInButton</a> $isOutButton";
					?>
				</td>
				<td>
				<?php
					$isEditorButton =	'<a href="javascript:outEditors(\''.$idUser.'\',\''.$username.'\');">
											<div class="isEditorButton" title="No more edit for you"></div>
										</a>';
					$noEditorButton =	'<a href="javascript:inEditors(\''.$idUser.'\',\''.$username.'\');">
											<div class="noEditorButton" title="Become an editor"></div>
										</a>';
					
					if($type == 'admin'){
						echo '<img src="images/isEditor.png" title="ADMIN"/>';
					}
					elseif(!empty($idEditor)){
						echo $isEditorButton;
					}
					else{
						echo $noEditorButton;
					}
				?>
				</td>
				</tr>
				
				<?php if ($limbo=='in'){?>
				<tr><td colspan="5" class="reason"><?php if (isset($limbo_reason)) echo htmlentities($limbo_reason); ?></td></tr>
				<?php } ?>
				
	        <?php $i++; } ?> <!-- end while --> 
		   </table>
<?php }

?>
