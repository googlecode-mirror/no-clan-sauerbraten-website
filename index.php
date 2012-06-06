<?php
session_start ();
require_once 'admin/config.php';
require_once 'admin/functions.php';
require_once 'admin/connect.php';
require_once 'admin/isUser.php';

// db connection
$dbConn = connect_db();

// LOGIN SENT
if (!empty($_POST['submitLog'])) {
        include './includes/login.php';
}elseif (!empty ($_POST['submitQuit'])){ // Good Bye User Session
        include './includes/logout.php';
}

// Is user connected? Get the userArray (updates the login date at DB too).
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
}

// SEARCH SENT
if(!empty($_POST['search_srt']) && !in_array($_POST['search_srt'], array('', 'search...')))
{
	$search_str = $search_str = mysql_real_escape_string(friendly_str($_POST['search_srt']));
	$location = rurl().'/search/'.$search_str;
	header("Location: $location");
	die;
}

// page info
$page_title = "NO CLAN"; // used at 'includes/head.inc'

/* * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * PAGINATION
 * $amount = number of posts per page
 * $page = the current page
 * $totalPages = the number of the WEB pages of index
 * 
 * $page = 0 means we are on a search result page
 * * * * * * * * * * * * * * * * * * * * * * * * * * * */ 
$amount = 5; // Show 4 summaries for each page

// Get the page number or frontpage (page 1)
if (!empty($_GET['page'])) { $page = (int) mysql_real_escape_string($_GET['page']); } else { $page = 1; }

// get the offset
$offset = $page * $amount - $amount +1;

// is this a search?
// ALTER TABLE `posts` ADD FULLTEXT (`title` ,`body` ,`summary` ,`tags`);
if (!empty($_GET['search'])){
	$page = 0;
	$search_str = mysql_real_escape_string(str_replace('-', ' ', friendly_str($_GET['search'])));
}



// Get the total pages
if (!empty($arrUser) && $arrUser['type'] != 'user') { $query = "SELECT COUNT(idPost) AS count FROM posts"; } // for members
else { $query = "SELECT COUNT(idPost) AS count FROM posts WHERE postFor = 'all'"; } // for all

$result = mysql_query($query, $dbConn);
$row = mysql_fetch_assoc($result);

if ($row['count']%$amount == 0) { $totalPages = (int)($row['count']/$amount); }
else { $totalPages = (int)($row['count']/$amount+1); }

unset($query, $result, $row);

// Pagination on the HTML
$linkto = rurl().'/page/';
$from = $page - 5;
$to = $page + 5;

if ($from < 1){$to = $to + (int)$from*(-1);}
if ($to > $totalPages){$from = $from + ($totalPages - $to);}
if ($from < 1){$from = 1;}
if ($to > $totalPages){$to = $totalPages;}

/* * * * * * * * * * * * * * * * * * * * * * * * * * * */

// GET THE LAST POST IF WE ARE AT FRONTPAGE (or we are in a search page)
if ($page > 0)
{
	if ($page == 1)
	{
		$query = "SELECT idPost, title, postFor, date_pub, posts.userId, summary, summary_img, body, username, COUNT(idComment) AS numc FROM posts INNER JOIN users ON posts.userId = users.idUser LEFT JOIN comments ON comments.postId = posts.idPost GROUP BY idPost HAVING date_pub <= CURRENT_TIMESTAMP ";
		
		if (empty($arrUser) || ( !empty($arrUser) && $arrUser['type'] == 'user')) {
			$query .= "AND posts.postFor = 'all' ";
		}
		
		$query .= "ORDER BY date_pub DESC LIMIT 0,1";

		$result = mysql_query($query, $dbConn);
		$post = mysql_fetch_assoc($result);
		$post = strip_slashes_arr($post);
	}

		// GET THE SUMMARY LIST
		$query="SELECT idPost, postFor, title, summary, summary_img, date_pub, posts.userId AS ui, username, COUNT(idComment) AS n_comm FROM posts INNER JOIN users ON posts.userId = users.idUser LEFT JOIN comments ON comments.postId = posts.idPost GROUP BY idPost HAVING date_pub <= CURRENT_TIMESTAMP ";

		if (empty($arrUser) || ( !empty($arrUser) && $arrUser['type'] == 'user')) {
			$query .= "AND posts.postFor = 'all' ";
		}

		$query .= "ORDER BY date_pub DESC LIMIT $offset, $amount";
}
else // we are in a search page
{
	// Searches in posts table and stores the info as a list of summaries (reusing the code from summary below)
	$query="SELECT result.idPost, result.postFor, result.title, result.summary, result.summary_img, result.date_pub, result.userId AS ui, username, COUNT(idComment) AS n_comm FROM (SELECT idPost, postFor, title, summary, summary_img, date_pub, userId, MATCH (posts.tags, posts.title, posts.summary, posts.body) AGAINST('$search_str') as rel FROM posts HAVING rel != 0) AS result INNER JOIN users ON result.userId = users.idUser LEFT JOIN comments ON comments.postId = result.idPost GROUP BY idPost HAVING result.date_pub <= CURRENT_TIMESTAMP ";
	if (empty($arrUser) || ( !empty($arrUser) && $arrUser['type'] == 'user')) {
		$query .= "AND result.postFor = 'all' ";
	}
	$query .= "ORDER BY result.rel DESC LIMIT 0, 10";
	
	// Arranges a search in comments -> $arr_search_comments
	$c_query = "SELECT result.idComment, result.userId, result.postId, result.date, result.content, username, title, postFor FROM (SELECT idComment, postId, userId, date, content, MATCH (comments.content) AGAINST('$search_str') AS rel FROM comments HAVING rel != 0) AS result INNER JOIN users ON result.userId = users.idUser INNER JOIN posts ON result.postId = posts.idPost ";
	
	if (empty($arrUser) || ( !empty($arrUser) && $arrUser['type'] == 'user')) {
		$c_query .= "WHERE postFor = 'all' ";
	}
	
	$c_query .= "ORDER BY result.rel DESC LIMIT 0,10";
	
	$result = mysql_query($c_query, $dbConn);
	$arr_search_comments = array();
	while ( $row = mysql_fetch_assoc ($result)) { array_push( $arr_search_comments, strip_slashes_arr($row)); }
	unset($c_query, $result, $row);
	
	// Maybe we are looking for users? -> $arr_search_users 
	$u_query = "SELECT username, idUser, type, first_name, DATE_FORMAT (date_created, '%b %D, %Y') AS date_created, about, location, country FROM users WHERE username LIKE '%$search_str%'";
	
	$result = mysql_query($u_query, $dbConn);
	$arr_search_users = array();
	while ( $row = mysql_fetch_assoc ($result)) { array_push( $arr_search_users, strip_slashes_arr($row)); }
	unset($u_query, $result, $row);
}

$result = mysql_query($query, $dbConn);
//...and arrage it in $arrPosts
$arrPosts = array();
while ( $row = mysql_fetch_assoc ($result)) { array_push( $arrPosts, strip_slashes_arr($row)); }
unset($query, $result, $row);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" itemscope itemtype="http://schema.org/Blog">

<head>
        <?php include $_SERVER['DOCUMENT_ROOT'].'/includes/head.inc.php';?>
        <!-- BEING SOCIAL -->
        <?php
        $social_title = 'NO CLAN';
        $social_image = rurl().'/images/NC-fb-100x100.jpg';
        $social_description = 'No Clan: Sauerbraten Clan Since 2011';
        $social_url = rurl();
        ?>
        <!-- FaceBook opengraph TAGS-->
        <meta property="og:title" content="<?php echo $social_title;?>" />
        <meta property="og:url" content="<?php echo $social_url;?>" />
        <meta property="og:image" content="<?php echo $social_image;?>" />
        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="<?php echo $social_description;?>" />
        <meta property="fb:admins" content="100003397471644" />
</head>

<!-- Load javascript timers to update page -->
<body onload='StartUp(<?php
 	    if (!empty($arrUser) && $arrUser['type'] != 'user' ) echo '1'; 
	?>)'>

<!-- G+ TAGS-->
<span itemprop="name" style="display: none"><?php echo $social_title;?></span>
<span itemprop="description" style="display: none"><?php echo $social_description;?></span>
<img itemprop="image" src="<?php echo $social_image;?>" style="display: none" alt="social button"/>

    <div id="wrapper">
        <div id="container">

            <div id="header"><?php include rdir().'/includes/header.inc.php';?></div><!-- /header -->
            
            <div id="menu">
                    <?php include 'includes/menu.inc.php';?>
                    <?php include 'includes/userlog.inc.php';?>
            </div>
                    
            <div id="content">
				<div id="sidepanel">
					
					<!-- ERRORS? -->
					<?php if (!empty($error)){?>
					<div class="error">
					<h3>Login problems:</h3>
							<?php foreach ($error as $e){?>
							<p><?php echo $e;?></p>
							<?php }?>
					</div>
					<?php }?>
					<!-- /errors -->
					
					<div><?php include rdir().'/includes/sidepanel.inc.php';?></div>
					
				</div><!-- /"sidepanel" -->

				<div id="main">
					
				<!-- THE LAST POST (only if we're at frontpage)-->
				<?php if ($page == 1) { ?>
					<div class="article" style="margin-bottom: 0;">
						
						<h1 class="title"><a href="<?php echo rurl().'/post/'.$post['idPost'].'/'.friendly_str($post['title']);?>" title="<?php echo $post['title'];?>"><?php echo $post['title'];?></a></h1>
						
						<p class="info">by <a class="fancy" href="<?php echo rurl();?>/user/show_userinfo.php?id=<?php echo $post['userId'];?>"><?php echo $post['username'];?></a>, on <?php echo date("M j, Y", strtotime($post['date_pub']));?>
						
						<span style="float: right;">
								<strong><?php if ($post['numc'] == 0) echo '<a href="'.rurl().'/post/'.$post['idPost'].'/'.friendly_str($post['title']).'#comments">'."No comments".'</a>'; else echo $post['numc'];?></strong>
								
								<?php if ($post['numc'] > 0){
										echo '<a href="'.rurl().'/post/'.$post['idPost'].'/'.friendly_str($post['title']).'#comments">';
										if ($post['numc'] == 1) echo 'comment</a>'; else echo 'comments</a>';
								}?>
								
						</span>
						
						</p>
						<?php $shareURL=rurl().'/post/'.$post['idPost'].'/'.friendly_str($post['title']);?>
						
						<div class="content">
							<?php echo $post['body'];?>
						</div>
						
						<div class="postSocial">
						<?php // SOCIAL BUTTONS ARE ONLY FOR PUBLIC POSTS
						if ($post['postFor'] == 'all'){?>
								<div class="gPlus" style="float: left;">
										<g:plusone href="<?php echo $shareURL;?>" size="medium" count="box"></g:plusone>
								</div>
																				
								<div class="fBook" style="float: right;"><?php
										$fb_title=urlencode($post['title']);
										$fb_url=$shareURL;
										$fb_summary=urlencode(strip_tags($post['summary']));
										if(!empty($post['summary_img']))
												$fb_image=urlencode($post['summary_img']);
												else $fb_image=urlencode(rurl().'/images/NC-fb-100x100.jpg');
										$FBimgCode = '<img title="share on Facebook" alt="share on Facebook" src="'.rurl().'/images/fbShare_21.png" style="border: none;"/>';
										?>
										<a onClick="window.open('http://www.facebook.com/sharer.php?s=100&amp;p[title]=<?php echo $fb_title;?>&amp;p[summary]=<?php echo $fb_summary;?>&amp;p[url]=<?php echo $fb_url; ?>&amp;&amp;p[images][0]=<?php echo $fb_image;?>','sharer','toolbar=0,status=0,width=548,height=325');" href="javascript: void(0)"><?php echo $FBimgCode;?></a>
								</div>
						<?php } // END OF SOCIAL BUTTONS ARE ONLY FOR PUBLIC POSTS?>
						
							<!-- Read all link -->
							<div style="text-align: center; font-size: 1.2em;">
								<a style="" href="<?php echo $shareURL;?>">&bull; Read the whole post and comments &bull;</a>
							</div>
							<!-- /Read all link -->
							
						</div>
							
					</div>

					<h2 style="text-align: center; color: #606060; border-top: 1px solid #959595; border-bottom: 1px solid #a5a5a5; margin: 1em 0 0.5em 0; padding: 5px 0 3px 0; background: url('<?php echo rurl();?>/css/art/white05.png');">Previous posts</h1>
					<!-- /THE LAST POST -->
				<?php } // End of the first Article ?>
					
					<!-- SUMMARIES OR SEARCH RESULT -->
					
					
				<?php // SEARCH RESULT TITLE AND OPTIONS
				if ($page == 0){?>
					<h1>Search Results</h1>
					<p>
						<?php
						echo '<em><strong>'.str_replace('-', ' ', $search_str).'</strong></em>&nbsp;&nbsp;&gt;&nbsp;&nbsp;';
						echo 'Posts: <strong>'.count($arrPosts).'</strong>&nbsp;&nbsp;|&nbsp;&nbsp;';
						echo 'Comments: <strong>'.count($arr_search_comments).'</strong>&nbsp;&nbsp;|&nbsp;&nbsp;';
						echo 'Users: <strong>'.count($arr_search_users).'</strong>';
						echo '<span style="float: right;"><a href="<?php echo rurl();?>" title="return to home page">[Back Home]</a></span>';
						?>
					</p>
					<hr />
					<br />
				<?}?>
					
					<?php if (!empty($arrPosts) && $page == 0 && count($arrPosts) > 4) echo '<div id="posts-found">'; // expand search result div 
					
					foreach ($arrPosts as $p)
					{ 
						$link = rurl().'/post/'.$p['idPost'].'/'.friendly_str($p['title']);
						$shareURL = rurl().'/post/'.$p['idPost'].'/'.friendly_str($p['title']);
					
						$c = $p['n_comm'];
						if ($c == 0) $num_comms = "No comments";
						else $num_comms = $c.' <a href="'.$link.'#comments" title="'.$p['title'].'">';
					
						if ($c == 1){ $num_comms.= "comment</a>";}
						if ($c > 1) { $num_comms.= "comments</a>";}
						
						if ($page != 0) $summary_class = 'summary'; else $summary_class = 'search_result';
						?>

						<div class="<?php echo $summary_class;?>">
							<?php if ($page != 0){?>
								<p class="info">
									<a class="fancy" href="<?php echo rurl();?>/user/show_userinfo.php?id=<?php echo $p['ui']?>"><?php echo $p['username'];?> </a>
									&bull; <?php echo date("M j, Y", strtotime($p['date_pub']));?>
								</p>
							<?php }?>
							
							<h1 class="title">
								<a href="<?php echo $link;?>" title="<?php echo $p['title']?>"><?php echo $p['title']?></a>
							</h1>

							<?php if (!empty($p['summary_img'])){?>
								<a href="<?php echo $link;?>" title="<?php echo $p['title']?>"><img class="summary_img" src="<?php echo $p['summary_img'];?>" title="<?php echo $p['title']?>"/></a>
							<?php } ?>
							
							<?php if ($page == 0){?>
							<p class="info">
								<a class="fancy" href="<?php echo rurl();?>/user/show_userinfo.php?id=<?php echo $p['ui']?>"><?php echo $p['username'];?> </a>
								&bull; <?php echo date("M j, Y", strtotime($p['date_pub']));?> &bull;
								<?php echo $num_comms;?> &bull; <a href="<?php echo $link;?>" title="<?php echo $p['title']?>">Read more</a>
							</p>
							<?php }?>
							
							<div class="content"><?php
								if ($page == 0) echo cut_string($p['summary'], 120);
								else echo $p['summary'];?>
							</div>
							
							<?php if ($page != 0){?>
								<div class="footer">
									<?php echo $num_comms;?> &bull; <a href="<?php echo $link;?>" title="<?php echo $p['title']?>">Read more</a>
								</div>
							<?php }?>
						</div>
					<?php } // END foreach summary ?>

					<?php if (!empty($arrPosts) && $page == 0 && count($arrPosts) > 4){
						echo '</div>'; // END expand search result div
						
						// expander button
						echo '<div id="expand-posts" onclick=\'document.getElementById("posts-found").style.height="auto";this.style.display="none";\'>';
						echo '<button>Show more posts</button>';
						echo '</div>';
						
					}?>
					<!-- /SUMMARIES and search posts result -->
					
					
					<?php // USERS and COMMENTS FOUND! (or not)
					if ($page == 0)
					{
						if (!empty($arr_search_users))
						{
							echo '<h2>Users</h2>';
							foreach ($arr_search_users as $u)
							{
								extract(strip_slashes_arr($u), EXTR_PREFIX_ALL, 'u');
								$u_pic = get_user_pic($u_idUser, 64);
								switch($u_type)
								{
									case 'member':
										$u_type = '<strong>&bull;NC&bull;</strong> member ';
										break;
									case 'admin':
										$u_type = '<strong>&bull;NC&bull;</strong> member ';
										break;
									case 'friend':
										$u_type = 'Friend of NC ';
										break;
									case 'user':
										$u_type = 'NC user ';
										break;
								}
								
								echo '<div class="user_found">';
								
								echo '<a class="fancy" href="'.rurl().'/user/show_userinfo.php?id='.$u_idUser.'">';
								echo '<img src="'. $u_pic .'" alt="'. $u_username .'" title="'. $u_username .'"/>';
								echo '</a>';
								
								echo '<h3 style="padding: 0; margin: 0;">';
								echo '<a class="fancy" href="'.rurl().'/user/show_userinfo.php?id='.$u_idUser.'">'.$u_username.'</a>';
								echo '</h3>';
								
								echo '<span class="location">'.$u_location.' ('.$u_country.')</span><br />';
								echo '<span class="label">'.$u_type.' since '.$u_date_created.'</span>';
								echo '<br /><span><a class="more" href="'.rurl().'/user/'.$u_username.'/" title="Visit '.friendly_str($u_username).'\'s profile">-more info-</a></span>';
								
								echo '</div>';
							}
						}
						
						if (!empty($arr_search_comments)) echo '<h2 style="clear: both;">Comments</h2>';
						
						foreach ($arr_search_comments as $c)
						{
							extract(strip_slashes_arr($c), EXTR_PREFIX_ALL , 'c');
							$c_pic= get_user_pic($c_userId, 32);
							$c_title = '<a href="'.rurl().'/post/'.$c_postId.'/'.friendly_str($c_title).'">'.$c_title.'</a>';
							$c_content = cut_string($c_content, 60);
							$c_content = '<a class="acomm" href="'.rurl().'/post/'.$c_postId.'/'.friendly_str($c_title).'#comm_'.$c_idComment.'">'.$c_content.'</a>';
							$c_date = date("M j/Y", strtotime($c_date));
							
							echo '<div class="comment_found">';
							echo '<p><img src="'. $c_pic .'" alt="'. $c_username .'" title="'. $c_username .'"/>';
							echo '<a class="fancy" href="'.rurl().'/user/show_userinfo.php?id='.$c_userId.'">'.$c_username.'</a>, on '.$c_title.': ';    
							echo '<em>'.$c_content.'</em>';
							echo '<span style="color: #505050; font-size: 0.8em;">&nbsp;&nbsp;('.$c_date.')</span>';
							echo '</p></div>';
							
						}
					} // end USERS and COMMENTS FOUND!?>
					
					<!-- PAGINATION -->
					<?php if ($page != 0){?>
						<div id="paginator" align="center"><ul>
						<?php if ($page != 1) echo'<a href="'.$linkto.($page -1).'">';?>
								<li class="prev"><div class="rightarr"></div>Newer posts</li>
						<?php if ($page != 1) echo '</a>';?>
						
						<?php for ($i = $from; $i <= $to; $i++){ ?>
								<li <?php if($page == $i) echo 'class="selected"'?>>
										<?php if($page == $i) echo $i;
										else echo '<a href='.$linkto.$i.' title="go to page '.$i.'">'.$i.'</a>';?>
								</li>
						<?php } ?>
						
						<?php if ($page != $totalPages) echo'<a href="'.$linkto.($page +1).'">';?>
								<li class="next"><div class="leftarr"></div>Older posts</li>
						<?php if ($page != $totalPages) echo'</a>';?>   
						</ul>
						<?php if($from != 1){?>
								<p style="text-align: center; margin-top: 0.5em;"><a href="<?php echo rurl();?>"><?php echo get_the_flag(32, 'blue');?><br/>back home</a></p>
						<?php }?>
						</div>
						<!-- /pagination -->
					<?php }?>
				
				</div><!-- /main -->
            </div><!-- /content-->
            
            <div id="footer">
                    <?php include 'includes/footer.inc.php';?>
            </div> <!-- /footer -->

        </div><!-- /container -->
    </div><!-- /wrapper -->

</body>

</html>
