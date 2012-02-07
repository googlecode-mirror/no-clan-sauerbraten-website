<?php
session_start ();
require_once 'admin/config.php';
require_once 'admin/connect.php';
require_once 'admin/functions.php';
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

// page info
$page_title = "NO CLAN"; // used at 'includes/head.inc'

/* * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * PAGINATION
 * $amount = number of posts per page
 * $page = the current page
 * $totalPages = the number of the WEB pages of index
 * * * * * * * * * * * * * * * * * * * * * * * * * * * */ 
$amount = 5; // Show 4 summaries for each page
// Get the page number or frontpage (page 1)
if (!empty($_GET['page'])) { $page = (int) mysql_real_escape_string($_GET['page']); } else { $page = 1; }
// get the offset
$offset = $page * $amount - $amount +1;

// echo "<h1>offset = $offset / page = $page</h1>";

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

// GET THE LAST POST IF WE ARE AT FRONTPAGE
if ($page == 1)
{
	if (!empty($arrUser) && $arrUser['type'] != 'user') {
		// for members and friends
		$query = "SELECT idPost, title, postFor, date_pub, posts.userId, summary, summary_img, body, username, COUNT(idComment) AS numc FROM posts INNER JOIN users ON posts.userId = users.idUser LEFT JOIN comments ON comments.postId = posts.idPost GROUP BY idPost HAVING date_pub <= CURRENT_TIMESTAMP ORDER BY date_pub DESC LIMIT 0,1";
		//$query = "SELECT idPost, title, date_pub, posts.userId, body, username, (SELECT COUNT(idComment) FROM comments WHERE comments.postId = idPost) AS numc FROM posts INNER JOIN users ON posts.userId = users.idUser WHERE date_pub <= CURRENT_TIMESTAMP ORDER BY date_pub DESC LIMIT 0,1";
	} else {
		// for all
		$query = "SELECT idPost, title,  postFor, date_pub, posts.userId, summary, summary_img, body, username, COUNT(idComment) AS numc FROM posts INNER JOIN users ON posts.userId = users.idUser LEFT JOIN comments ON comments.postId = posts.idPost GROUP BY idPost HAVING date_pub <= CURRENT_TIMESTAMP AND posts.postFor = 'all' ORDER BY date_pub DESC LIMIT 0,1";
	}
	$result = mysql_query($query, $dbConn);
	$post = mysql_fetch_assoc($result);
	$post = strip_slashes_arr($post);
}

// GET THE SUMMARY LIST
if (!empty($arrUser) && $arrUser['type'] != 'user') {
	// for members
	$query="SELECT idPost, postFor, title, summary, summary_img, date_pub, posts.userId AS ui, username, COUNT(idComment) AS n_comm FROM posts INNER JOIN users ON posts.userId = users.idUser LEFT JOIN comments ON comments.postId = posts.idPost GROUP BY idPost HAVING date_pub <= CURRENT_TIMESTAMP ORDER BY date_pub DESC LIMIT $offset, $amount";
	
} else {
	$query="SELECT idPost, postFor, title, summary, summary_img, date_pub, posts.userId AS ui, username, COUNT(idComment) AS n_comm FROM posts INNER JOIN users ON posts.userId = users.idUser LEFT JOIN comments ON comments.postId = posts.idPost GROUP BY idPost HAVING posts.postFor = 'all' AND date_pub <= CURRENT_TIMESTAMP ORDER BY date_pub DESC LIMIT $offset, $amount";
	
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

<body <?php if (isset($arrUser)) echo "onload='StartUp()'"; ?>>

<!-- G+ TAGS-->
<span itemprop="name" style="display: none"><?php echo $social_title;?></span>
<span itemprop="description" style="display: none"><?php echo $social_description;?></span>
<img itemprop="image" src="<?php echo $social_image;?>" style="display: none"/>

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
					
					<div class="content"><?php echo $post['body'];?></div>
					
					<br/><br/>

				
					
					<div class="postSocial">
					<?php $shareURL=rurl().'/post/'.$post['idPost'].'/'.friendly_str($post['title']);?>
					
					
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
						<div style="text-align: center;">
							<a style="" href="<?php echo $shareURL;?>">&bull; Read the whole post and comments &bull;</a>
						</div>
						<!-- /Read all link -->
					</div>
					
				</div>

				<h1 style="text-align: center; color: #606060; border-bottom: 1px solid #959595; margin-bottom: 1em; padding: 10px 0 5px 0; background: url('<?php echo rurl();?>/css/art/black10.png');">Previous posts</h1>
				<!-- /THE LAST POST -->
				<?php } ?>
				
				<!-- SUMMARIES -->
				<?php foreach ($arrPosts as $p) { 
				$link = rurl().'/post/'.$p['idPost'].'/'.friendly_str($p['title']);
				$shareURL = rurl().'/post/'.$p['idPost'].'/'.friendly_str($p['title']);
				
				$c = $p['n_comm'];
				if ($c == 0) $num_comms = "No comments";
				else $num_comms = $c.' <a href="'.$link.'#comments" title="'.$p['title'].'">';
				if ($c == 1){ $num_comms.= "comment</a>";}
				if ($c > 1) { $num_comms.= "comments</a>";}		
				?>
				<div class="summary">
					
					
					<p class="info">
						<a class="fancy" href="<?php echo rurl();?>/user/show_userinfo.php?id=<?php echo $p['ui']?>"><?php echo $p['username'];?> </a>
						&bull; <?php echo date("M j, Y", strtotime($p['date_pub']));?>
					</p>
					
					<h1 class="title">
						<a href="<?php echo $link;?>" title="<?php echo $p['title']?>"><?php echo $p['title']?></a>
					</h1>
					
					<?php if (!empty($p['summary_img'])){?>
						<a href="<?php echo $link;?>" title="<?php echo $p['title']?>"><img class="summary_img" src="<?php echo $p['summary_img'];?>" title="<?php echo $p['title']?>"/></a>
					<?php } ?>
					
					<div class="content"><?php echo $p['summary']?></div>
					
					<div class="footer">
						<?php echo $num_comms;?> &bull; <a href="<?php echo $link;?>" title="<?php echo $p['title']?>">Read more</a>
					</div>
				</div>
				<?php } ?>
				<!-- SUMMARIES -->
							
				<!-- /PAGINATION -->
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
				
			
			</div><!-- /main -->
	    </div><!-- /content-->
	    
	    <div id="footer">
		    <?php include 'includes/footer.inc.php';?>
	    </div> <!-- /footer -->

	</div><!-- /container -->
    </div><!-- /wrapper -->

</body>

</html>
