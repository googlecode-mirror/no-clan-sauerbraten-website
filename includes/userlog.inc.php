<form action="<?php if (isset($_SERVER["REDIRECT_STATUS"]) && ($_SERVER["REDIRECT_STATUS"]==404)) echo '/index.php';?>" method="post" accept-charset="utf-8" id="userlog">
    <?php if (empty ($arrUser)){?>
        <input type="text" name="username" id="username" value="<?php if (!empty($username_form)) echo $username_form; else echo "username"?>" onclick="this.value='';this.style.color='#cccccc';"/>
        <input type="password" name="password" id="password" value="********" onclick="this.value='';this.style.color='#cccccc';"/>
        &nbsp;&nbsp;<input type="submit" name="submitLog" title="logIn" value=" "/>
        <a style="font-weight: normal;" href="<?php echo rurl().'/register.php';?>" title="user resgistration" onclick="return confirm('Registration at our site does not mean you are a clan member or that you may use -NC- tag to play.');">&nbsp;register</a>
    <?php }else{?>
    	<span>Welcome <a href="<?php echo rurl();?>/user-settings.php" title="User settings"><strong><?php echo $arrUser['username'];?></strong></a></span>
    	&nbsp;&nbsp;
    	<input type="submit" class="out" name="submitQuit" title="quit" value=" "/>
    <?php }?>
</form>
