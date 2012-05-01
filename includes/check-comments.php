<?php
	session_start ();
	require_once '../admin/functions.php';
	require_once rdir().'/admin/config.php';
	require_once rdir().'/admin/connect.php';
	require_once rdir().'/admin/isUser.php';

	// db connection
	$dbConn = connect_db();
	
	if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
		$arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
	}
	// Number of comments to show
	$n=10;

	if ( empty($arrUser) || ( !empty($arrUser) && $arrUser['type'] == 'user' ) ){ 
		// Get the Last Comments of public posts posts.
		$q="SELECT idComment, comments.userId, postId, content, comments.date, username, title FROM comments INNER JOIN posts ON postId = idPost INNER JOIN users ON comments.userId = users.idUser WHERE posts.postFor = 'all' ORDER BY comments.date DESC LIMIT 0,$n";
	}else{
		// Members, friends and admins can see all...
		$q="SELECT idComment, comments.userId, postId, content, comments.date, username, title FROM comments INNER JOIN posts ON postId = idPost INNER JOIN users ON comments.userId = users.idUser ORDER BY comments.date DESC LIMIT 0,$n";
	}

	if ($r=mysql_query($q, $dbConn))
		while ($commentrow=mysql_fetch_array($r))
		{
			extract(strip_slashes_arr($commentrow), EXTR_PREFIX_ALL, "lastc");
			$lastc_pic= get_user_pic($lastc_userId, 24);
			$lastc_title = '<a href="'.rurl().'/post/'.$lastc_postId.'/'.friendly_str($lastc_title).'">'.$lastc_title.'</a>';
			$lastc_content = cut_string($lastc_content, 60);
			$lastc_content = '<a class="acomm" href="'.rurl().'/post/'.$lastc_postId.'/'.friendly_str($lastc_title).'#comm_'.$lastc_idComment.'">'.$lastc_content.'</a>';
		
			echo '<div class="comment">';			
			echo '<p><img class="userpic" src="'. $lastc_pic .'" alt="'. $lastc_username .'" title="'. $lastc_username .'"/>';
			echo '<strong>'.' '.$lastc_username.'</strong>, on '.$lastc_title.' says: ';    
			echo '<em>'.$lastc_content.'</em></p>';
			echo '</div>';
		}

?>
