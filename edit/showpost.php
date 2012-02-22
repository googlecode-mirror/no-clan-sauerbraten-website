<?php
session_start ();
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/functions.php';
require_once rdir().'/admin/connect.php';
require_once rdir().'/admin/isUser.php';

// db connection
$dbConn = connect_db();

// This page needs idPost
if (empty($_GET['idPost'])){ } 
else {   
    // GET THE POST
    $idPost = mysql_real_escape_string($_GET['idPost']);
    $query = "SELECT idPost, summary, summary_img, postFor, title, date_pub, userId, body, tags, username FROM prePosts INNER JOIN users ON userId = idUser WHERE idPost = '$idPost'";
    $result = mysql_query ($query, $dbConn);
    if($row = mysql_fetch_assoc ($result))
    {      
		extract(strip_slashes_arr($row), EXTR_PREFIX_ALL , 'p');
		unset($query, $result, $row, $arrPost);
	}else { go_home(); }

	// page info
	$page_title = $p_title; // used at 'includes/head.inc'
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" itemscope itemtype="http://schema.org/Blog">

<head>
	<?php include rdir().'/includes/head.inc.php';?>
	<meta name="keywords" content="NoClan, NC, sauerbraten, No Clan, <?php echo $p_tags;?>"/>
</head>

<body>

<!-- G+ TAGS-->
<span itemprop="name" style="display: none"><?php echo $p_title;?></span>
<span itemprop="description" style="display: none"><?php echo $p_summary;?></span>
<img itemprop="image" src="<?php if (!empty($p_summary_img)) echo $p_summary_img; else echo rurl().'/images/NC-fb-100x100.jpg';?>" style="display: none"/>

	<div id="wrapper">
		<div id="container" style="width: 660px;">
			<div id="content" style="width: 660px; border: solid 3px #101010; border-left: none;">
				<div id="main">
					<p class="pre_label">SUMMARY</p>

						<div class="summary">
							<h1 class="title">
								<a><?php canput($p_title, 'No title');?></a>
							</h1>
							
							<img src="<?php canput($p_summary_img, '" alt="No summary image');?>" title="<?php canput($p_title, 'No title');?>"/>
							<div class="content"><?php echo canput($p_summary, 'No summary');?></div>
							<a>Read more...</a>
						</div>
				
						<p class="pre_label">FULL POST</p>
                    <!-- ARTICLE -->
                    <div class="article">
                        <h1 class="title"><a href="<?php echo rurl().'/post/'.$p_idPost.'/'.friendly_str($p_title);?>" title="<?php echo $p_title;?>"><?php echo $p_title;?></a></h1>
                        <img style="float: left; margin-right: 0.5em; margin-top: 12px;" src="<?php echo get_user_pic($p_userId, 32)?>"/><p class="info">by <a class="fancy" href="<?php echo rurl();?>/user/show_userinfo.php?id=<?php echo $p_userId;?>"><?php echo $p_username?></a><br/>on <?php echo date("M j, Y", strtotime($p_date_pub));?>

                        </p>                       
                        <div class="content"><?php canput($p_body, 'no body');?></div>
                    </div>
                    <!-- /ARTICLE -->
				</div><!-- /main -->
			</div><!-- /content-->
		</div><!-- /container -->
	</div><!-- /wrapper -->
</body>
</html>
