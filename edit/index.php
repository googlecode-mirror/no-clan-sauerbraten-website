<?php
session_start ();
require_once '../admin/functions.php';
require_once '../admin/config.php';
require_once '../admin/connect.php';
require_once '../admin/isUser.php';

// db connection
$dbConn = connect_db();


// Is user connected? Is editor or admin? no? go home then.
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password']))
{
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
	if ($arrUser['type'] != 'admin' XOR $arrUser['idEditor'] == $arrUser['idUser']){ go_home(); }

} else { go_home(); }

// Logout
if (!empty ($_POST['submitQuit'])){ // Good Bye User Session
	include '../includes/logout.php';
}


// If we have done anything, show the info
if (!empty($_GET['done'])){
	switch ($_GET['done']){
		case 'postDel':
			$get_info = 'Your post has been removed.';
			break;
		
		case 'postEdit':
			$get_info = 'Your post has been edited.';
			break;
			
		case 'postAdd':
			$get_info = 'Your new post has been added.';
			break;
		
		case 'ready':
			$get_info = 'Your post has ben marked as ready. An admin will check and publish the post. Be patient...';
			break;
	}
}

// DELETE
if (!empty($_GET['del']))
{
    $del = mysql_real_escape_string($_GET['del']);
    // Where are the images of the post?
    $query = "SELECT date, imgs FROM prePosts WHERE idPost = '$del'";
    $result = mysql_query ($query, $dbConn);
    $row = mysql_fetch_assoc ($result);
    
    $imgs = $row['imgs'];
    $dirImgs = date2dateDir($row['date']);
    unset($query, $result, $row);
    
    // ... got it! Then delete the images...
    $path = "../data/images/posts/$dirImgs";
    $images = glob($path.'/{'.$imgs.'*.jpg}', GLOB_BRACE);
    foreach ($images as $image){ @unlink($image); }
    
    // ... and the post on database
    $query  = "DELETE FROM prePosts WHERE idPost = {$_GET['del']}";
    $result = mysql_query($query, $dbConn);
    unset($query, $result);
    
    //... and the comments (poor users...)
    $query  = "DELETE FROM comments WHERE postId = {$_GET['del']}";
    $result = mysql_query($query, $dbConn);
    unset($query, $result);
    
    header( "Location: index.php?done=postDel" );
    die;
}

if (!empty($_GET['publish'])){
	$idPost = mysql_real_escape_string($_GET['publish']);
	$query = "UPDATE prePosts SET finished = 'yes' WHERE idPost = '$idPost' LIMIT 1";
	$result = mysql_query($query, $dbConn);
	unset($idPost, $query);
	header( "Location: index.php?done=ready" );
    die;
}

// Get the editor list of posts in $arrPosts
$arrPosts = array();
$userId = $arrUser['idUser'];
$query = "SELECT idPost, title, finished FROM prePosts WHERE prePosts.userId = '$userId' ORDER BY date DESC LIMIT 0, 20";
if($result = mysql_query ($query, $dbConn)){
	while ($row = mysql_fetch_assoc ($result)) array_push($arrPosts, $row);
}
unset ($query, $result, $row);

// Get the admin list of posts in $arrPublished
$arrPublished = array();
//$query = "SELECT idPost, title, date_pub, DATE_FORMAT(date_pub, '%b %d, %Y') AS human_date_pub FROM posts WHERE userId = '$userId' ORDER BY date_pub DESC LIMIT 0, 20";
$query = "SELECT idPost, title, summary, DATE_FORMAT(date_pub, '%b %d, %Y') AS date_pub, posts.userId, COUNT(idComment) AS n_comm FROM posts LEFT JOIN comments ON comments.postId = posts.idPost GROUP BY idPost HAVING posts.userId = '$userId' ORDER BY date_pub DESC LIMIT 20";
if($result = mysql_query ($query, $dbConn)){
	while ($row = mysql_fetch_assoc ($result)) array_push($arrPublished, $row);
}
unset ($query, $result, $row);


$page_title = "NC: EDITOR ZONE" // used at includes/head.inc.php
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <?php include rdir().'/edit/includes/head.inc.php';?>
    <!-- Fancy -->
    <?php include rdir().'/admin/includes/fancy.inc.php'?>
    <!-- /Fancy -->
    <style type="text/css">
    button.pubButton{padding: 0; margin: 0;}
    </style>
</head>
<body style="background: url('../css/art/wrapperBackAdmin.png') no-repeat scroll center 0px; background-color: #000;">
    <div id="wrapper">
	<div id="container">

	    <div id="header">
	    </div><!-- /header -->
	    
	    <div id="menu">
		    <?php include '../includes/menu.inc.php';?>
		    <?php include '../includes/userlog.inc.php';?>
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
			
		    <!-- sidepanel include -->
		    <div><?php include 'includes/sidepanel.inc.php';?></div></div><!-- /"sidepanel" -->
		    

		    <div id="main">
			
			<?php if(!empty($get_info)){?>
				<div class="get_info"><p><?php canput($get_info);?></p></div>
			<?php }?>
			<div style="float: right;"><a href="post-manager.php" title="Create a new post"><button style="font-weight: bold; margin:0;">NEW POST</button></a></div>
			<h1><?php echo $arrUser['username']?>'s posts</h1>
			<h2>Working on...</h2>
			<table style="width:100%;">
			    <tr>
				<th>Title</th>
				<th>State</th>
				<th>action</th>
			    </tr>
			<?php $i = 0; foreach ($arrPosts as $p) { ?>
			    <tr <?php if ($i%2==0) echo 'class="i" ';?>>
				<td><em><a class="fancy" href="<?php echo rurl().'/edit/showpost.php?idPost='.$p['idPost'];?>"><?php echo $p['title'];?></a></em></td>
				<td>
					<?php if ($p['finished'] == 'yes'){ echo "pending"; }
					else { echo '<a href="'.rurl().'/edit/index.php?publish='.$p['idPost'].'"><button class="pubButton">READY!</button></a>'; }
					?>
				</td>
				<td style="vertical-align: center;">
				    <?php if ($p['finished'] != 'yes'){ ?>
						<a href="<?php echo rurl();?>/edit/post-manager.php?idPost=<? echo $p['idPost']; ?>" title="EDIT"><img style="padding: 3px;" src="edit-icon.png"/></a>
				    <?php } ?>
				    <a href="<?php echo rurl();?>/edit/index.php?del=<? echo $p['idPost']; ?>" onclick="return confirm('Confirm DELETE\nPost will be removed.');" title="DELETE"><img style="padding: 3px;" src="delete-icon.png"/></a>
				</td>
			    </tr>
			<?php $i++; }?>
			</table>
			
			<h2>Published</h2>
			<?php if (empty($arrPublished)){
				echo '<p>No published post yet</p>';
			}else{?>
				<table style="width: 100%;">
					<?php $i = 0; foreach ($arrPublished as $p){
						$title = $p['title'];
						$link = rurl().'/post/'.$p['idPost'].'/'.friendly_str($p['title']);
						$date = $p['date_pub'];
						$comms = $p['n_comm'].' comments';
						$extract = cut_string($p['summary'], 25)
					?>
					<tr <?php if ($i%2==0) echo 'class="i"';?> style="padding: 0;">
						<td style="width: 6em;"><?php echo $date;?></td>
						<td style="padding-right: 1em;">
							<?php echo
								'<a target="_blank" href="'.$link.'" title="'.$title.'">'.$title.'</a>
								<span style="font-size: 0.8em; color: #405090;">&nbsp;'.$extract.'</span>';
							?>
						</td>
						<td style="width: 6em;"><?php echo '<a style="font-weight:normal;" target="_blank" href="'.$link.'#comments" title="Check the comments of the post">'.$comms.'</a>';?></td>
						
					</tr>
					
					
				<?php $i++; }?>
				</table>
			<?php }?>
		</div><!-- /main -->
	    </div><!-- /content-->
	    
	    <div id="footer"></div> <!-- /footer -->
	    
	</div><!-- /container -->
    </div><!-- /wrapper -->
</body>

</html>
