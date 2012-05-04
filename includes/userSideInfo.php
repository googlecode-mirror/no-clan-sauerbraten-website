<?php if (!empty($arrUser))
{
    $up = get_user_pic($arrUser['idUser'], 64);
?>
<div id="sideUserInfo" style="overflow: hidden; margin-bottom: 1em;">
	<div style="overflow: hidden;">
	    <a href="<?php echo rurl().'/user/'.$arrUser['username'].'/';?>" title="<?php echo $arrUser['username'];?>'s zone"><img src="<?php echo $up?>" alt="your photo"/></a>
	    <h1><?php echo $arrUser['username']?><span class="label">(<?php echo $arrUser['type'];?>)</span></h1>					
	    <p><?php echo $arrUser['country'];?></p>
		<p><?php if ($arrUser['type'] == 'member' || $arrUser['type'] == 'admin'){
				echo "<strong>&bull;NC&bull;&nbsp;</strong>";
			}?>since <?php echo $arrUser['date_created'];?>
		</p>
	</div>
	
	<?php if ($arrUser['type'] == 'member' || $arrUser['type'] == 'admin'){?>
	<div style="text-align: center; padding: 5px; background: url('<?php echo rurl().'/css/art/black10.png';?>'); margin-top: 3px;">
	    <?php
	    $idUser=$arrUser['idUser'];
	    $q="SELECT idMessage FROM messages WHERE messages.to='$idUser' AND is_read='0'";
	    $r=mysql_query($q, $dbConn);
	    $messages=mysql_num_rows($r);
	    echo '<div id="messages">
	          <p><a href="'.rurl().'/messages/">Messages ('.$messages.')</a>
	          &bull; <a href="'.rurl().'/messages/full_message.php">Send a message</a></p>
	          </div>';
	    ?>
	</div>
    <?php } ?>

</div>

<?php } ?>
