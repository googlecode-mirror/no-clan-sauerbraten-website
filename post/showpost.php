<?php
session_start ();
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/functions.php';
require_once rdir().'/admin/connect.php';
require_once rdir().'/admin/isUser.php';

// db connection
$dbConn = connect_db();

// LOGIN SENT
if (!empty($_POST['submitLog'])) {
	include rdir().'/includes/login.php';
} elseif (!empty ($_POST['submitQuit'])) { // Good Bye User Session
	include rdir().'/includes/logout.php';
}

// Is user connected? Get the userArray (updates the login date at DB too).
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])) {
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
}

// This page needs idPost
if (empty($_GET['idPost'])){ go_home(); } 
else {   
    // GET THE POST
    $idPost = mysql_real_escape_string($_GET['idPost']);
    $query = "SELECT idPost, summary, summary_img, postFor, title, date_pub, userId, body, tags, username FROM posts INNER JOIN users ON userId = idUser WHERE idPost = '$idPost'";
    $result = mysql_query ($query, $dbConn);
    if($row = mysql_fetch_assoc ($result))
    {      
		extract(strip_slashes_arr($row), EXTR_PREFIX_ALL , 'p');
		unset($query, $result, $row, $arrPost);
    
		// FILTER USERS. If this post is not for all, we need to take measures
		if ($p_postFor != 'all'){
			if (empty($arrUser) || ( !empty($arrUser) && $arrUser['type'] == 'user') ){ go_home(); }
		}
	}else { go_home(); }

	/* * * * * * * * * * *
	 * THE COMMENTS STUFF
	 * * * * * * * * * * */
	$old = 300; // seconds to allow edition
	$edition = false; // Show or not the edition comment form

	function is_recent($timestamp, $seconds){
		$now = time();
		$when = strtotime($timestamp);
		if(($now - $when) < $seconds){ return true; }
		else { return false; }
	}

	// BRING BACK THE COMMENTS OF THE POST
	$query = "SELECT idComment, userId, username, content, date FROM comments INNER JOIN users ON userId = idUser WHERE postId = '$idPost' ORDER BY comments.date ASC";
	$result = mysql_query($query, $dbConn);
	//...and arrage it in $arrComments
	$arrComments = array();
	while ( $row = mysql_fetch_assoc ($result)) { array_push( $arrComments, strip_slashes_arr($row)); }
	unset($query, $result, $row);
	$c = count($arrComments); // number of comments. Used on the page.

	// SET THE USER OPTIONABLE COMMENTS: Get the last comment user sent to put the edit/delete buttons in.
	$optionable_comments = array(); // idUser => idComent
	// Foreach user that has comments here, get the last comment
	if (!empty($arrComments)){
		foreach ($arrComments as $candidate){
			if (is_recent($candidate['date'], 360)){
				$optionable_comments[$candidate['userId']] = $candidate['idComment'];
			}
		}
	}

	// DELETE COMMENT or SET EDITION MODE ON
	if ( ( !empty($_POST['submitDelComm']) || !empty($_POST['submitEditComm']) ) && !empty($_POST['the_key'])){

		if (!empty($arrUser) && ( array_search($_POST['the_key'], $optionable_comments) == $arrUser['idUser'] || $arrUser['idUser'] == $p_userId ))
		{
			if (!empty($_POST['submitDelComm'])){
				$k = mysql_real_escape_string($_POST['the_key']);
				$query  = "DELETE FROM comments WHERE idComment = '$k'";
				$result = mysql_query($query, $dbConn);

				$location = rurl().$_SERVER['REQUEST_URI'];
				header("Location: $location");
				die;
			}
			// Set edition mode on that comment
			if (!empty($_POST['submitEditComm'])){ $edition = $_POST['the_key']; }

		}else{$error['validation'] = 'What are you trying to do?';}
	}

	// COMMENT SENT
	if ( !empty($_POST['submitComm']) && !empty($_POST['comment_body']) && !empty($arrUser) )
	{
		// Arrange the content
		$comm_userId = $arrUser['idUser'];
		
		if (!empty($_POST['comment_body']))
		{
			if (!are_tags_closed($_POST['comment_body'])) $error['comm'] = 'Some tags are unclosed. Fix your message before send it again.';
			else $comm_content = process_content($_POST['comment_body']);
				
			if (empty($comm_content)) $error['comm'] = 'No content, no comment';
			else $comm_content = mysql_real_escape_string($comm_content);
		
		} else { $error['comm'] = 'You have to write something if you want to comment.'; }
		
		// Store comment
		if (empty($error))
		{
			if (empty($_POST['the_key']))
			{	// Adding a new comment			
				$query = "INSERT INTO comments (userId, postId, content) VALUES ('$comm_userId', '$p_idPost', '$comm_content');";
				$result = mysql_query($query, $dbConn);
				unset ($comm_content, $comm_userId, $query, $result);
				
				$location = rurl().$_SERVER['REQUEST_URI'].'#comments';
				header("Location: $location");
				die;	
			}
			elseif(!empty($_POST['the_key']))
			{	// Updating a comment
				if (!empty($arrUser) && (array_search($_POST['the_key'], $optionable_comments) == $arrUser['idUser'] || $arrUser['idUser'] == $p_userId) )
				{
					$k = mysql_real_escape_string($_POST['the_key']);
					$query = "UPDATE comments SET content = '$comm_content' WHERE idComment= '$k'";
					$result = mysql_query($query, $dbConn);
					
					unset($k, $query, $result);
									
					$location = rurl().$_SERVER['REQUEST_URI'].'#comments';
					header("Location: $location");
					die;
				}else { $error['comm'] = 'What are you trying to do?'; }
			}
		}
	}

	// page info
	$page_title = $p_title; // used at 'includes/head.inc'
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" itemscope itemtype="http://schema.org/Blog">

<head>
	<?php include rdir().'/includes/head.inc.php';?>
	<meta name="keywords" content="NoClan, NC, sauerbraten, No Clan, <?php echo $p_tags;?>"/>
	<!-- FACEBOOK LIKE -->
	<?php
	$social_title = 'NO CLAN';
	$social_image = 'http://astrafo.dyndns.org/images/NC-fb-100x100.jpg';
	$social_description = 'No Clan: Sauerbraten Clan Since 2011';
	$social_url = 'http://astrafo.dyndns.org';
	?>
	<!-- FaceBook opengraph TAGS-->
	<meta property="og:title" content="<?php echo $social_title;?>" />
	<meta property="og:url" content="<?php echo $social_url;?>" />
	<meta property="og:image" content="<?php echo $social_image;?>" />
	<meta property="og:type" content="website" />
	<meta property="og:site_name" content="<?php echo $social_description;?>" />
	<meta property="fb:admins" content="100003397471644" />
</head>

<body  <?php if (!empty($error['comm']) || $edition){ ?>onload="javascript:document.getElementById('comment_form').scrollIntoView(true); window.scrollBy(0, -(screen.height/4))"<?php }?>>

<!-- G+ TAGS-->
<span itemprop="name" style="display: none"><?php echo $p_title;?></span>
<span itemprop="description" style="display: none"><?php echo $p_summary;?></span>
<img itemprop="image" src="<?php if (!empty($p_summary_img)) echo $p_summary_img; else echo 'http://astrafo.dyndns.org/images/NC-fb-100x100.jpg';?>" style="display: none"/>

	<div id="wrapper">
		<div id="container">

			<div id="header"><?php include rdir().'/includes/header.inc.php';?></div><!-- /header -->
			
			<div id="menu">
				<?php include rdir().'/includes/menu.inc.php';?>
				<?php include rdir().'/includes/userlog.inc.php';?>
			</div>
				
			<div id="content">
				<div id="sidepanel"><?php include rdir().'/includes/sidepanel.inc.php';?></div><!-- /"sidepanel" -->

				<div id="main">
                    <!-- ARTICLE -->
                    <div class="article">
                        <h1 class="title"><a href="<?php echo rurl().'/post/'.$p_idPost.'/'.friendly_str($p_title);?>" title="<?php echo $p_title;?>"><?php echo $p_title;?></a></h1>
                        <img style="float: left; margin-right: 0.5em; margin-top: 12px;" src="<?php echo get_user_pic($p_userId, 32)?>"/><p class="info">by <a class="fancy" href="<?php echo rurl();?>/user/show_userinfo.php?id=<?php echo $p_userId;?>"><?php echo $p_username?></a><br/>on <?php echo date("M j, Y", strtotime($p_date_pub));?>
                            <span style="float: right;">
                                <strong><?php if ($c == 0) echo "No comments yet"; else echo $c;?></strong>
                                
                                <?php if ($c > 0){
                                    echo '<a href="'.rurl().$_SERVER['REQUEST_URI'].'#comments">';
                                    if ($c == 1) echo 'comment</a>'; else echo 'comments</a>';
                                }?>
                            </span>
                        </p>                       
                        <div class="content"><?php canput($p_body, 'no body');?></div>
                        
                        <div class="postSocial">
							<?php $shareURL = 'http://astrafo.dyndns.org/post/'.$p_idPost.'/'.friendly_str($p_title);?>
							<!-- G+ items -->
							<div class="gPlus" style="float: left;">
								<!-- G+ button -->
								<g:plusone href="<?php echo $shareURL;?>" size="medium" count="box"></g:plusone>
								<!-- /G+ button -->
							</div>
							
							<div class="fBook" style="float: right;">
								<?php
								$fb_title=urlencode($p_title);
								$fb_url=urlencode($shareURL);
								$fb_summary=urlencode(strip_tags($p_summary));
								if(!empty($p_summary_img))
									$fb_image=urlencode($p_summary_img);
									else $fb_image=urlencode(rurl().'/images/NC-fb-100x100.jpg');
								$FBimgCode = '<img title="share on Facebook" alt="share on Facebook" src="'.rurl().'/images/fbShare_21.png" style="border: none;"/>';
								?>
								<a onClick="window.open('http://www.facebook.com/sharer.php?s=100&amp;p[title]=<?php echo $fb_title;?>&amp;p[summary]=<?php echo $fb_summary;?>&amp;p[url]=<?php echo $fb_url; ?>&amp;&amp;p[images][0]=<?php echo $fb_image;?>','sharer','toolbar=0,status=0,width=548,height=325');" href="javascript: void(0)"><?php echo $FBimgCode;?></a>
							</div>
						</div>
						
                    </div>
                    <!-- /ARTICLE -->
                    
                    <!-- COMMENTS -->
                    <div id="comments">
                        <h2>Comments</h2>                    
                        <?php $i = 1; foreach ($arrComments as $comm){ 
						extract($comm, EXTR_PREFIX_ALL, 'c');
						$userInfoPage = rurl().'/user/show_userinfo.php?id='.$c_userId;
						if ($comm['username'][strlen($c_username)-1] == 's') { $atitle = $c_username."'"; } else { $atitle = $c_username."'s"; }
						?>
						
                        <div class="comm" id="comm_<?php echo $c_idComment;?>">
						
						<?php if ($c_idComment == $edition){?>
						
                            <form action="" class="commentarea" method="post" id="comment_form">
								<?php if (!empty($error['validation'])) { ?>
									<div class="error" id="error"><p><?php echo $error['comm']; ?></p></div>
								<?php } ?>
								<div class="info"><label for="comment_body">Edition</label></div>
								<textarea name="comment_body" rows="3" style="background: url('<?php echo get_user_pic($arrUser['idUser'], 48);?>') no-repeat scroll 5px 5px; background-color: #e0e0e0;" onkeydown="if(this.value.length >= 1452){this.value = this.value.substring(0,1450); alert('Ups! Too much text for a comment.'); return false; }"><?php echo strip_tags($c_content, "<strong>"."<em>"."<b>"."<i>")?></textarea>
								<input type="hidden" style="display: none;" name="the_key" value="<?php echo $c_idComment;?>"/>
								<input type="submit" name="submitComm" value="&nbsp;&nbsp;SEND&nbsp;&nbsp;"/>
								<input type="submit" name="submitComm" value="CANCEL"/>
							</form>
							
						<?php } else { ?>
                        
                            <div class="info">
                                <span class="comm_num">#<?php echo $i;?></span> 
                                <span class="comm_user">
									<a class="fancy" href="<?php echo $userInfoPage;?>" title="<?php echo $atitle;?> info">
									<?php echo $c_username;?></span>
									</a>
                                <?php // OPTIONS: EDIT AND DELETE
								if ( !empty($arrUser) && ( array_search($c_idComment, $optionable_comments) == $arrUser['idUser'] || $arrUser['idUser'] == $p_userId ))
								{
									$edit_img_src = rurl().'/post/edit-icon.png';
									$delete_img_src = rurl().'/post/delete-icon.png';
									$submitEdit_style = "background: url('$edit_img_src') no-repeat;";
									$submitDelete_style = "background: url('$delete_img_src') no-repeat;";
									?>
									<span class="comm_options">
										<form action="<?php echo rurl().'/post/'.$p_idPost.'/'.friendly_str($p_title);?>" method="post">
											<input name="the_key" type="hidden" style="display: none;"value="<?php echo $c_idComment;?>" />
											<input name="submitDelComm" style="<?php echo $submitDelete_style; ?>" type="submit" value="&nbsp;" title="Delete this comment" onclick="return confirm('Freedom of speech is more sacred than any faith... \n Please, confirm you want to delete the comment.');" />
											<input name="submitEditComm" style="<?php echo $submitEdit_style; ?>" type="submit" value="&nbsp;" title="Edit this comment"/>
										</form>
									</span>
								<?php } // if ( !empty($arrUser) )
								if (date("Y", strtotime($c_date)) == date("Y")){ $date_format = "M j, H:i"; }else 	$date_format = "M j, Y - H:i";
								?>
								<span class="comm_date"><?php echo date($date_format, strtotime($c_date));?></span>
                            </div>
                    
                            <div class="body">
                                <a class="fancy" href="<?php echo $userInfoPage;?>" title="<?php echo $atitle;?> info">
									<img src="<?php echo get_user_pic($c_userId, 48);?>" title="<?php echo $c_username;?>"/>
								</a>
                                <div><?php echo $c_content;?></div>
                            </div>
                            
						<?php }//END IF EDITION ?>
                        </div>                    
                        <?php $i++;} 
                        if (!empty($arrUser) && !$edition){ ?>
                        <div>
							<form action="" class="commentarea" method="post" id="comment_form">
								<?php if (!empty($error['comm'])) { ?>
									<div class="error" id="error"><p><?php echo $error['comm']; ?></p></div>
								<?php } ?>
								<label for="comment_body">Leave a comment</label>
								<textarea name="comment_body" rows="3" style="background: url('<?php echo get_user_pic($arrUser['idUser'], 48);?>') no-repeat scroll 5px 5px; background-color: #e0e0e0;" onkeydown="if(this.value.length >= 1452){this.value = this.value.substring(0,1450); alert('Ups! Too much text for a comment.'); return false; }"><?php if (!empty($comm_content)) canput($comm_content);?></textarea>
								<input type="submit" name="submitComm" value="SEND!"/>
							</form>
						</div>                        
                        <?php } elseif (!$edition) { ?>
                        <p style="text-align: center;"><br/>Only registered users and NC members can comment.<br/>
                        <a href="<?php echo rurl();?>/register.php">Register here.</a><br/></p>
                        <?php } ?>
                    </div><!-- /comments -->
                    
				</div><!-- /main -->
			</div><!-- /content-->
			<div id="footer"><?php include rdir().'/includes/footer.inc.php';?></div> <!-- /footer -->
		</div><!-- /container -->
	</div><!-- /wrapper -->
</body>
</html>
