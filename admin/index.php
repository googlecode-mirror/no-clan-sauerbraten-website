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
} else { go_home(); }

// Logout
if (!empty ($_POST['submitQuit'])){ // Good Bye User Session
	include './includes/logout.php';
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
	}
}

// DELETE
if (!empty($_GET['del']))
{
    $del = mysql_real_escape_string($_GET['del']);
    // Where are the images of the post?
    $query = "SELECT date, imgs FROM posts WHERE idPost = '$del'";
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
    $query  = "DELETE FROM posts WHERE idPost = {$_GET['del']}";
    $result = mysql_query($query, $dbConn);
    unset($query, $result);
    
    //... and the comments (poor users...)
    $query  = "DELETE FROM comments WHERE postId = {$_GET['del']}";
    $result = mysql_query($query, $dbConn);
    unset($query, $result);
    
    header( "Location: index.php?done=postDel" );
    die;
}

// Get the admin list of posts in $arrPosts
$arrPosts = array();
$userId = $arrUser['idUser'];
$query = "SELECT idPost, title, date_pub, DATE_FORMAT(date_pub, '%b %d, %Y') AS human_date_pub FROM posts WHERE userId = '$userId' ORDER BY date_pub DESC LIMIT 0, 20";
$result = mysql_query ($query, $dbConn);
while ($row = mysql_fetch_assoc ($result)) array_push($arrPosts, $row);
unset ($query, $result, $row);

$page_title = "NC: ADMIN ZONE" // used at includes/head.inc.php
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <?php include rdir().'/admin/includes/head.inc.php';?>
    <!-- Fancy -->
    <?php include rdir().'/admin/includes/fancy.inc.php'?>
    <!-- /Fancy -->
</head>
<body style="background: url('../css/art/wrapperBackAdmin.png') no-repeat scroll center 0px; background-color: #000;">
    <div id="wrapper">
	<div id="container">

	    <div id="header">
	    </div><!-- /header -->
	    
	    <div id="menu">
		    <?php include 'includes/menu.inc.php';?>
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
		    <div><?php include rdir().'/admin/includes/sidepanel.inc.php';?></div></div><!-- /"sidepanel" -->

		    <div id="main">
			
			<?php if(!empty($get_info)){?>
				<div class="get_info"><p><?php canput($get_info);?></p></div>
			<?php }?>
			
			<h2><?php echo $arrUser['username']?>'s Posts</h2>

			<table style="width:100%;">
			    <tr>
				<th>id</th>
				<th>Title</th>
				<th>Date Pub</th>
				<th>action</th>
			    </tr>
			<?php $i = 0; foreach ($arrPosts as $p) { ?>
			    <tr <?php if ($i%2==0) echo 'class="i" ';?>>
				<td><?php echo $p['idPost']; ?></td>
				<td><em><a class="fancy" href="<?php echo rurl().'/post/'.$p['idPost'].'/preview-of-the-post';?>"><?php echo $p['title'];?></a></em></td>
				<td><?php echo $p['human_date_pub']?></td>
				<td>
				    <a href="<?php echo rurl();?>/admin/post-manager.php?idPost=<? echo $p['idPost']; ?>">EDIT</a>
				    <a href="<?php echo rurl();?>/admin/index.php?del=<? echo $p['idPost']; ?>" onclick="return confirm('Confirm DELETE\nPost and comments will be removed.');">DELETE</a>
				</td>
			    </tr>
			<?php $i++; }?>
			</table>
    
		</div><!-- /main -->
	    </div><!-- /content-->
	    
	    <div id="footer"></div> <!-- /footer -->
	    
	</div><!-- /container -->
    </div><!-- /wrapper -->
</body>

</html>
