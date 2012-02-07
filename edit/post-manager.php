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


if (isset($arrUser['isEditor'])) echo "YES"; else echo "NO";
// Logout
if (!empty ($_POST['submitQuit'])){ // Good Bye User Session
	include '../includes/logout.php';
}

// We Use this
$idPost=''; $title=''; $date=''; $date_pub=''; $summary='';
$summary_img=''; $tags=''; $body=''; $userId=$arrUser['idUser'];

// EDIT    
if ( !empty($_GET['idPost'])){
    
    $idPost = mysql_real_escape_string($_GET['idPost']);
       
    // Bring back the post for editting
    $query = "SELECT idPost, title, userId, username, summary, postFor, summary_img, body, tags, date_pub, date, imgs FROM prePosts INNER JOIN users ON prePosts.userId = users.idUser WHERE idPost = $idPost";
    $result = mysql_query ($query, $dbConn);
    $row = mysql_fetch_assoc ($result);
        
    // Make sure the user who wants to edit is the post original editor
    if ($arrUser['type'] != 'admin' XOR $row['userId'] == $arrUser['idUser']) go_home();
    
    // Put the data in single vars
    extract($row);
    
    // Where are the images of this post stored?
    $dirImgs = date2dateDir($date);
    $arrImgs = glob('../data/images/posts/'.$dirImgs.'/{'.$imgs.'*.jpg}', GLOB_BRACE);
    unset($query, $result, $row);
    
}else{
	$username = $arrUser['username'];     
    // SETTING THE IMAGE (YEARMONTH) DIR AND THE IMAGES PREFIX
    if ( !empty($_GET['dirImgs']) && !empty($_GET['imgs'])){
	    $dirImgs	= $_GET['dirImgs'];
	    $imgs	= $_GET['imgs'];
	    $arrImgs = glob('../data/images/posts/'.$dirImgs.'/{'.$imgs.'*.jpg}', GLOB_BRACE);
	    
    }elseif ( !empty($_POST['dirImgs']) && !empty($_POST['imgs'])){
	    $dirImgs = $_POST['dirImgs'];
	    $imgs    = $_POST['imgs'];
	    $arrImgs = glob('../data/images/posts/'.$dirImgs.'/{'.$imgs.'*.jpg}', GLOB_BRACE);
	    
    }else{
	    $dirImgs = date('Ym');
	    $imgs = time();
	    $arrImgs = glob('../data/images/posts/'.$dirImgs.'/{'.$imgs.'*.jpg}', GLOB_BRACE);
	    
	    if(empty($_GET['idPost'])){
	    
	    header( "Location: post-manager.php?dirImgs=$dirImgs&imgs=$imgs" );
	    
	    }
    }
}

// ADD / EDIT POST
if ( !empty($_POST['submitAdd']) || !empty ($_POST['submitEdit']) || !empty($_POST['submitPreview']) || !empty($_POST['submitReload']))
{
    // Errors
    if (!empty ($_POST['submitAdd']) || !empty ($_POST['submitEdit']))
    {
	if (empty($_POST['title']))   $error['title' ]   = 'A title is needed.';
	if (empty($_POST['body']))    $error['body']     = 'Content body is needed.';
	if (empty($_POST['tags']))    $error['tags']     = 'Put some tags, please. We will get faster searches.';
	if (empty($_POST['summary'])) $error['summary' ] = 'Summary is needed.';
    }

    // Edition only
    if (!empty ($_POST['submitEdit']))
    {
	if ( !empty($_POST['idPost']) ) $idPost = mysql_real_escape_string($_POST['idPost']);
	if ( !empty($_GET['idPost']) )  $idPost = mysql_real_escape_string($_GET['idPost']);
	
	if ( empty($idPost) ) $error['idPost'] = 'idPost is missing. fuuuu...';
    }

    if (empty($error) )
    {
	// Vars
	$title	= mysql_real_escape_string($_POST['title']);
	$tags	= mysql_real_escape_string($_POST['tags']);
		
	// Editor vars
	$body    = mysql_real_escape_string(process_full_editor($_POST['body']));
	$summary = mysql_real_escape_string(process_mini_editor($_POST['summary']));
	
	if ( !empty($_POST['summary_img']) && is_url_ok($_POST['summary_img']) )
	    $summary_img = mysql_real_escape_string($_POST['summary_img']);
	
	if (!empty($_POST['date_pub']))
	    $date_pub = mysql_real_escape_string($_POST['date_pub']);
	else $date_pub = date("Y-m-d H:i:s");
	
	if (!empty($_POST['postFor']))
	    $postFor = mysql_real_escape_string($_POST['postFor']);
    
	    // ADDING POST
	    if ( !empty($_POST['submitAdd']))
	    {
		    $query= "INSERT INTO prePosts (title, userId, postFor, summary, summary_img, body, date_pub, tags, imgs)
			     VALUES ('$title', '$userId', '$postFor', '$summary', '$summary_img', '$body', '$date_pub', '$tags', '$imgs')";
		    $result = mysql_query($query, $dbConn);
		    unset ($query, $result);
		    
		    header( 'Location: index.php?done=postAdd' );
		    die;
	    }
	    
	    // EDIT POST
	    if (!empty($_POST['submitEdit']))
	    {
			// Mark the post as NOT READY
			$query = "UPDATE prePosts SET finished='no' WHERE idPost = $idPost";
			$result = mysql_query ($query, $dbConn);
    
		    // Updates the post
		    $query= "UPDATE prePosts SET title = '$title', summary = '$summary', summary_img = '$summary_img',
			     body = '$body', date_pub = '$date_pub', tags = '$tags', postFor = '$postFor'  WHERE idPost = '$idPost'";
		    $result = mysql_query($query, $dbConn);
		    unset ($query, $result);
			
			if($arrUser['type'] == 'admin') $location = rurl().'/admin/index.php?done=prePostEdit'; else $location = rurl().'/edit/index.php?done=postEdit';
		    header( 'Location: '.$location );
		    die;
	    }
    }
}


$page_title = "NC: Post Manager"; // used at includes/head.inc.php
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <?php include rdir().'/admin/includes/head.inc.php';?>

    <script language="javascript" type="text/javascript" src="<?echo rurl();?>/admin/js/datetimepicker_css.js"></script>
		
    <script type="text/javascript" src="<?php echo rurl();?>/js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
    <?php include rdir().'/admin/includes/tinyMceFull.inc.php';?>

    <?php include rdir().'/admin/includes/fancy.inc.php';?>
    
    
    <style type="text/css">
	#post_preview{
	    position: absolute;
	    top: 0; left: 0;
	    width: 100%;
	    background: url('<?php echo rurl();?>/css/art/black70.png');
	    <?php if (empty($_POST['submitPreview'])) echo 'display: none;';?>
	}
	.pre_main{
	    width: 620px;
	    padding: 20px;
	    margin: 100px auto;
	    background: url('<?php echo rurl();?>/css/art/miniContent.png') repeat-y;
	    margin-bottom: 50em;
	}
	.pre_label{
	    background-color: black;
	    padding: 3px 5px;
	    color: white;
	}
    </style>
</head>

<body style="background: url('../css/art/wrapperBackAdmin.png') no-repeat scroll center 0px; background-color: #000;">
    
    <div id="post_preview">
	<div class="pre_main">
	    <button style="width: 100%;" onclick="javascript:document.getElementById('post_preview').style.display = 'none';"> HIDE PREVIEW </button>
	    <br/>
	    <p class="pre_label">SUMMARY</p>
	    <br/>

	    <div class="summary">
		<h1 class="title">
		    <a><?php canput($title, 'No title');?></a>
		</h1>
		
		<img src="<?php canput($summary_img, '" alt="No summary image');?>" title="<?php canput($title, 'No title');?>"/>
		<div class="content"><?php echo canput($summary, 'No summary');?></div>
		<a>Read more...</a>
	    </div>

	    <br/>
	    <p class="pre_label">FULL POST</p>
	    <br/>

	    <div class="article">
		<h1 class="title"><?php canput($title, 'No title');?></h1>
		<p class="info">by <a><?php echo $username?></a>, on <?php echo date("M j, Y", strtotime($date_pub));?>
		<span style="float: right;"> 8 <a>Comments</a></span>
		</p>
		<div class="content"><?php canput($body, 'no body');?></div>
	    </div>

	    <br/>
	    <button style="width: 100%;" onclick="javascript:document.getElementById('post_preview').style.display = 'none';"> HIDE PREVIEW </button>
	</div>
    </div>

    <div id="wrapper">
	<div id="container">
	    <div id="header"></div><!-- /header -->
		
		<div id="menu">
		    <?php include '../includes/menu.inc.php';?>
		    <?php include '../includes/userlog.inc.php';?>
		</div>

		<div id="content">
		    
		    <div id="sidepanel">

			<!-- ERRORS? -->
			<?php if (!empty($error)){?>
			<div class="error">
			    <h3>Problems:</h3>
				<?php foreach ($error as $e){?>
				<p><?php echo $e;?></p>
				<?php }?>
			</div>
			<?php }?>
			<!-- /errors -->
			
			<!-- IMAGES -->
			<h1>Images</h1>
			
			<p><strong>How to put images: </strong>Copy &amp; paste the image URL on the insert image dialog.</p>
			
			<?php if (!empty($arrImgs)){
			    foreach ($arrImgs as $image){?>
				<div style="margin: 1px; padding: 3px; background: url('<?php echo rurl();?>/css/art/black10.png');">
				    <p style="color: #404040; margin: 0; padding: 0;">
					<strong><?php $tmp = getimagesize($image); if ($tmp[0] == 120 && $tmp[1]==64) echo 'SUMMARY IMG'; else echo 'BODY IMG'?></strong>
					<span style="font-size: 0.8em;"><?php echo $tmp[0].'x'.$tmp[1].'px';?></span>
					
				    </p>
				    <img style="max-height: 100px; max-width: 280px;" src="<?php echo $image;?>"/>
				    <p style="font-size: 0.8em; color: #801010; border: dotted 1px #555; padding: 2px; margin-top: 1px;"><?php echo rurl()."/data/images/posts/".$dirImgs.'/'.pathinfo($image, 2);?></p>
				</div>
			<?php }}?>
			
			<h3>
			    <a class="fancy_big_frame" href="<?php echo rurl().'/edit/image-manager.php?dirImgs='.$dirImgs.'&imgs='.$imgs;?>">
				[Image Manager]
			    </a>
			</h3>
			<!--/IMAGES -->

		    </div><!-- /"sidepanel" -->

		    <div id="main">

			<h1>NC Post Manager (<?php if (empty($_GET['idPost'])) echo "add mode"; else echo "edit mode";?>)</h1>
			<!-- ADD&EDIT FORM -->
			<form id="entry" name="entry" action="" method="post">
			    <div id="form">
				<!-- title -->
				<label for="title">Title</label><br/>
				<input name="title" class="title" style="width: 100%; padding: 3px;" type="text" class="new_title" value="<?php canput($title);?>"/>
				<!-- /title -->
				<!-- Reload images -->
				<input name="submitReload" style="float: right" type="submit" value="RELOAD UPLOADED IMAGES"/>
				<!-- /Reload images -->
				<!-- date_pub -->
				<label for="date_pub">Publication Date: </label>
				<a href="javascript:NewCssCal('demo1', 'yyyymmdd','dropdown',true,24,false)">
				    <input name="date_pub" id="demo1" style="width: 12em;" type="text" value="<?php
						if ( time() > strtotime($date_pub)){ echo date("Y-m-d H:i:s"); } else { canput($date_pub, date("Y-m-d H:i:s")); }
					?>" />
				</a>
				<!-- /date_pub -->
				<br/>
				<!-- for -->
				<label for="postFor">Readers: </label>
				<select name="postFor">
				    <option value="all" <?php if (!empty($postFor) && stripslashes($postFor) == 'all') echo 'selected="selected"';?>>
					Public post / for all users
				    </option>
				    <option value="members" <?php if (!empty($postFor) && stripslashes($postFor) == 'members') echo 'selected="selected"';?>>
					Only for members
				    </option>
				</select>
				<!-- /for -->
				<br/><br/>
				<!-- Body -->
				<label for="fullEditor">Body</label><br/>
				<textarea class="fullEditor" name="body" id="body"><?php canput($body);?></textarea>
				<!-- /Body -->
				<br/>
				<!-- Tags -->
				<label for="tags">Tags</label><br/>
				<input name="tags" type="text" value="<?php canput($tags);?>" style="width: 100%;"/>
				<!-- /Tags -->
				<br/>
				<!-- Summary -->
				<label for="summary_img">Summary image (URL)</label><br/>
				<input name="summary_img" type="text" value="<?php if (!empty($summary_img)) canput($summary_img);?>" style="width: 100%;"/>
				<br/>
				<label for="summary">Summary</label><br/>
				<div class="editor">
				    <textarea  class="miniEditor" name="summary" id="summary"><?php canput($summary);?></textarea>
				</div>
				<!-- Summary -->
				<br/>			    
				
				<!-- IMGS -->
				<input name="imgs" type="hidden" value="<?php echo $imgs; ?>" />
				<input name="dirImgs" type="hidden" value="<?php echo $dirImgs; ?>" />
				<!-- /IMGS -->
			    </div>
			    <!-- SUBMITS -->
			    <?php if(empty($_GET['idPost'])){?>
				<input name="submitPreview" type="submit" value="PREVIEW" />
				<input name="submitAdd" type="submit" value="SAVE" />
			    <?php }else{?>
				<input name="submitPreview" type="submit" value="PREVIEW" />
				<input name="idPost" type="hidden" value="<?php canput($idPost);?>" />
				<input class="submit" name="submitEdit" type="submit" value="SAVE" onclick="return confirm('After edition, this post will go back to it\'s owner.\n\nCONFIRM EDITION');"/>
			    <?php }?>
			</form>
			<?php if($arrUser['type'] == 'admin') $location = rurl().'/admin/'; else $location = rurl().'/edit/';?>
			<button style="float: right; margin-top: -3.3em;" onclick="location.href='<?php echo $location?>'">CANCEL</button>
			
			<!-- /ADD&EDIT FORM -->

		    </div><!-- /main -->

		</div><!-- /content-->
		
		<div id="footer"></div> <!-- /footer -->

	    </div><!-- /container -->
    </div><!-- /wrapper -->
</body>

</html>
