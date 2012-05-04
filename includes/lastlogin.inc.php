<?php
//Search for the 10 latest logins
$query = "SELECT idUser, username, date_modified FROM users WHERE users.type = 'member' OR users.type = 'admin' ORDER BY date_modified DESC LIMIT 10";

if ($result = mysql_query ($query, $dbConn)) {

	//and if we have actual results, show them
	$users=mysql_num_rows($result);
	if ($users>0)
	{
        $now=time();
        
        ?>
        <div id="last_seen"><h3>Last seen online</h3><?php

        while ($logins = mysql_fetch_array($result))
		{
			$u=$logins['username'];
			$t=$logins['date_modified'];
			$i=$logins['idUser'];
			$hl = how_long_since($t);
			$userInfoPage = rurl().'/user/show_userinfo.php?id='.$i;
			if ($u[strlen($u)-1] == 's') { $atitle = $u."'"; } else { $atitle = $u."'s"; }
			
			// set the user pic (idUser and pixels w/h)
			$img = get_user_pic($i, 24);
			
			//show as online users users who requested pages within the last 90 seconds (panda is a pain in the ass)
			$unix_time=strtotime($t);
			?>
			
			<div <?php if ($now-90<$unix_time) echo "class=\"online\"";?>>
			
				<a class="fancy" href="<?php echo $userInfoPage;?>" title="<?php echo "$atitle";?> info">
					<img class="userpic" src="<?php echo $img;?>" alt="<?php echo $u;?>"/>
				</a>

				<?php if (isset($arrUser) && ($arrUser['type'] == 'member' || $arrUser['type'] == 'admin')){?>
					<a class="fancy_mini_main"  href="<?php echo rurl();?>/messages/message.php?to=<?php echo $u;?>" title="<?php echo "Send a message to $u";?>">
						<img style="float: right; border: none; margin: 0;" src="<?php echo rurl();?>/images/mail.png" alt="message"/>
					</a>
				<?php }?>	
				
				<h1><a class="fancy" href="<?php echo $userInfoPage;?>" title="<?php echo "$atitle";?> info"><?php echo $u;?></a></h1>
				
				<?php if ($now-90<$unix_time){?>
					<p class="online">online</p>
				<?php }else{?>
					<p style="color: #253040;"><?php echo $hl;?></p>
				<?php }?>
					
			</div>
			<?php }}?>
		</div>
		<?php } unset($result, $query);
?>
