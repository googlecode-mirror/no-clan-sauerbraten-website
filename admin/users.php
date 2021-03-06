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
	
	//CSRF prefention
	if (!empty($_SESSION['CSRF'])) $csrf=$_SESSION['CSRF'];
	else die('Possible CSRF #1 detected. Please retry or contact NC administration.');
	if (!empty($_GET['rnd'])) $rnd=$_GET['rnd'];
	else die('Possible CSRF #2 detected. Please retry or contact NC administration.');
	
	//filter for showing specific groups of users
	if (!empty($_GET['filter'])) {
		if ($_GET['filter']!='All') {
			$wanted_type=mysql_real_escape_string($_GET['filter']);
		}
		$order=' ORDER BY type DESC';
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
	 * INFO 
	 * * * * * * */
	if (!empty($_GET['info'])) {
		if ($_GET['info']=='login') {
			$info='date_modified';
			$order=' ORDER BY date_modified DESC';
		}
		if ($_GET['info']=='registered') {
			$info='date_created';
			$order=' ORDER BY idUser';
		}
	} else $info='date_modified';
	
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
	$q='SELECT idUser, username, type, '.$info.', limbo, limbo_reason, idEditor FROM users LEFT JOIN editors ON idUser = idEditor';
	// if we have a 'filter by type', add that to the query
	if (isset($wanted_type)) {
		//if members' list was requested, show the admins as well.
		if ($wanted_type=='member') $q.=' WHERE type=\'admin\' OR type=\'member\'';
		else $q.=" WHERE type='$wanted_type'";
	}
	else $wanted_type='';

	if (!isset($order)) $order=' ORDER BY idUser';

	if ($r=mysql_query($q, $dbConn)) { ?>
		<p><table id="User list" style="width: 100%;">
			<tr><th style="text-align: center;">#</th><th>Username</th>
				<th style="text-align: center;"><select name="type" id="type" onchange="UserFilter(this.value)">
							<option value="All"   <?php if ($wanted_type=='All') echo 'selected="selected"';?>>Everyone			</option>
							<option value="admin" <?php if ($wanted_type=='admin') echo 'selected="selected"';?>>Admins			</option>
							<option value="member"<?php if ($wanted_type=='member') echo 'selected="selected"';?>>Clan Members		</option>
							<option value="friend"<?php if ($wanted_type=='friend') echo 'selected="selected"';?>>Friends			</option>
							<option value="user"  <?php if ($wanted_type=='user') echo 'selected="selected"';?>>Registered Users	</option>
						</select></th>
				<th style="text-align: center;">
					<select name="UserInfo" id="UserInfo" onchange="javascript: UserInfo(this)">
						<option value="registered" <?php if ($info=='date_created')  echo 'selected="selected"'; ?>>Registration</option>
						<option value="login" <?php if ($info=='date_modified')  echo 'selected="selected"'; ?>>Latest login</option>
					</select>
				</th>
				<th style="text-align: center;">Limbo</th>
				<th style="text-align: center;">Editor</th>
			<?php
			$i = 1;
			while ($row=mysql_fetch_array($r)) {
				extract($row); ?>
			    <tr <?php if ($i%2==0) echo 'class="i"';?>><td style="text-align: center;"><?php echo $i;   ?></td>
								
				<td style="text-align: left;"><?php echo htmlentities($username); ?></td>
				<td style="text-align: center;"><select name='<?php echo htmlentities($username); ?>' id='<?php echo $idUser; ?>'  onchange="javascript: UpdateUser(this);">
							   
					<option value="admin"  <?php if ($type=='admin')  echo 'selected="selected"'; ?>>Admin</option>
					<option value="member" <?php if ($type=='member') echo 'selected="selected"'; ?>>Clan member</option>
					<option value="friend" <?php if ($type=='friend') echo 'selected="selected"'; ?>>NC friend</option>
					<option value="user"   <?php if ($type=='user')   echo 'selected="selected"'; ?>>Registered user</option> 
								   
				</select></td>
				<td style="text-align: center;">
					<?php
						if (isset($date_created)) echo htmlentities($date_created);
						if (isset($date_modified)) echo htmlentities($date_modified);
					?>
				</td>
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
