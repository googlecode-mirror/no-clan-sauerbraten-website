<?php
session_start ();
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/functions.php';
require_once rdir().'/admin/config.php';
require_once rdir().'/admin/connect.php';
require_once rdir().'/admin/isUser.php';

// db connection
$dbConn = connect_db();
define('LIMIT_SHOW','20');

// LOGIN SENT
if (!empty($_POST['submitLog'])) include $_SERVER['DOCUMENT_ROOT'].'/includes/login.php';
elseif (!empty ($_POST['submitQuit'])) include $_SERVER['DOCUMENT_ROOT'].'/includes/logout.php';

// Is user connected?
if (!empty($_SESSION['NC_user'] ) && !empty($_SESSION['NC_password'])){
    $arrUser = isUser($_SESSION['NC_user'], $_SESSION['NC_password'], $dbConn);
}else{ go_home(); }
// ONLY ADMINS, MEMBERS AND FRIENDS ARE ALLOWED.
if (!empty($arrUser) && $arrUser['type'] == 'user') { go_home(); }

// GET parameter for message list paging system
$offset=0;
if (($_SERVER['REQUEST_METHOD']=='GET')) {
    if (isset($_GET['offset'])) $offset=$_GET['offset'];
    // or see if we want to delete a message
    if (isset($_GET['del'])) include $_SERVER['DOCUMENT_ROOT'].'/messages/delete.php';
}
// page info
$page_title = "NoClan: Messages"; // used at 'includes/head.inc'
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<?php include rdir().'/includes/head.inc.php';?>
	<style type="text/css">
		p.messages_info, div.arrows {margin: 5px 0;}
		p.messages_info{float: left; font-weight: bold; color: #303030;}
		div.arrows{float: right;}
		.la, .ra, .las, .rae, .arrow {float: left; height: 20px; margin-left: 5px;}
		.la, .ra{width: 20px;} .las, .rae{width: 24px;}
		.la			{background: url('<?php echo rurl();?>/css/art/leftArrow.png')}
		.la:HOVER	{background: url('<?php echo rurl();?>/css/art/RedLeftArrow.png')}
		.las		{background: url('<?php echo rurl();?>/css/art/leftArrowStart.png')}
		.las:HOVER	{background: url('<?php echo rurl();?>/css/art/RedLeftArrowStart.png')}
		.ra			{background: url('<?php echo rurl();?>/css/art/rightArrow.png')}
		.ra:HOVER	{background: url('<?php echo rurl();?>/css/art/RedRightArrow.png')}
		.rae		{background: url('<?php echo rurl();?>/css/art/rightArrowEnd.png')}
		.rae:HOVER	{background: url('<?php echo rurl();?>/css/art/RedRightArrowEnd.png')}
	</style>
</head>

<body>
	<div id="wrapper">
		<div id="container">

			<div id="header"><?php include rdir().'/includes/header.inc.php';?></div><!-- /header -->
			
			<div id="menu">
				<?php
				include rdir().'/includes/menu.inc.php';
				include rdir().'/includes/userlog.inc.php';
				?>
			</div>
				
			<div id="content">
				<div id="sidepanel">

					<!-- ERRORS? -->
				    <?php if (!empty($error)){?>
				    <div class="error">
						<?php foreach ($error as $e){?>
						<p><?php echo $e;?></p>
						<?php }?>
					</div>
					<?php }?>
					<!-- /errors -->
                    <?php include $_SERVER['DOCUMENT_ROOT'].'/includes/sidepanel.inc.php';?>
				</div><!-- /"sidepanel" -->

				<div id="main">
<?php
//is the user logged in?
if (isset($arrUser))
{
	if (isset($deleted)) echo "<p>$deleted</p>";
    $idUser=$arrUser['idUser'];
    // SQL_CALC_FOUND_ROWS is needed so that $r2 query returns total
    // number of records. Without it, FOUND_ROWS() returns bad.
    $q="SELECT SQL_CALC_FOUND_ROWS * from messages where messages.to='$idUser' order by time desc limit $offset, ".LIMIT_SHOW;
    if ($r=mysql_query($q, $dbConn))
    {
        //See what is the total number of messages of the last query
        $q2="select FOUND_ROWS()";
        $r2=mysql_query($q2, $dbConn);
        $records_array=mysql_fetch_array($r2);
        $records=$records_array[0];
        if ($records!=0)
        {
			//we actually have incoming messages, load usernames
			$username_query="select username, idUser from users";
			$r3=mysql_query($username_query, $dbConn);
			while ($usernameArr=mysql_fetch_array($r3)) {
				$usernamesID=$usernameArr['idUser'];
				$usernames["$usernamesID"]=$usernameArr['username'];
			}
			
			// $pointer will hold message # for this query.
            $pointer=$offset+1;
            echo '<p class="messages_info">Showing '. mysql_num_rows($r) .' out of '. $records .' messages.</p>';
			
			echo '<div class="arrows">';
            // Show button for first page (pass no parameters)
            if ($offset>0) echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'"  alt="First Page" title="First page"><div class="las"></div></a>';
            else echo '<img class="arrow" src="../css/art/GrayLeftArrowStart.png" alt="First Page" title="First page"/>';

            // Show button for previous page
            if ($offset>LIMIT_SHOW-1)
                echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?offset='.($offset-LIMIT_SHOW).'" title="Previous page"><div class="la"></div></a>';
            else echo '<img class="arrow" src="../css/art/GrayLeftArrow.png" alt="Previous Page" title="Previous page"/>';

            // Show button for next page
            if ($offset+LIMIT_SHOW<$records) echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?offset='.($offset+LIMIT_SHOW).'"  title="Next Page"><div class="ra"></div></a>';
            else echo '<img class="arrow" src="../css/art/GrayRightArrow.png"
                alt="Next page" title="Next Page"/>';

            // Show button for last page
            $lastpage=($records-($records % LIMIT_SHOW));
            if ($offset+LIMIT_SHOW<$records) echo '<a href="'.htmlentities($_SERVER['PHP_SELF']).'?offset='.$lastpage.'" title="Last page"><div class="rae"></div></a><br />';
            else echo '<img class="arrow" src="../css/art/GrayRightArrowEnd.png"
                      alt="Last page" title="Last page"/>';
			
			echo '</div>';

            //Show the actual table with the messages
            // $q still holds the query for it
            echo '<table id="message_list" style="width: 100%"><tr>
                  <th style="text-align: center;">#</th>
                  <th>from</th>
                  <th>date</th>
                  <th>subject</th>
                  <th style="text-align: center;"></th>
                  <th style="text-align: center;"></th>
                  </tr>';
                  
            $i = 0;
            while ($row=mysql_fetch_array($r)) {
                $idMessage=$row['idMessage'];
                $from=$row['from'];
                $time=date("Mj - H:i", strtotime($row['time']));
                $subject=$row['subject'];
                $message=$row['message'];
                $is_read=$row['is_read'];
                
                if ( ($i%2) == 0 ){ $class='class="i"'; } else { $class=''; }
                
                // get link to user's photo
                $img = get_user_pic($from, 24);

                // show message # within query (not idMessage)
                echo '<tr '.$class.'><td style="text-align: center;">'.$pointer++.'</td><td>';
                
                // show sender's photo & name
                // if username isn't found, they are obviously ex-members.
                if (!isset($usernames["$from"])) $usernames["$from"]='ex-member';
                echo '<img class="userpic" src="'.$img.
                     '" alt="'.$usernames["$from"].
                     '" title="'.$usernames["$from"].'"/>&nbsp;'.$usernames["$from"].'</td>';
                
                // show time sent
                echo '<td class="date"><a href="messages/read.php?msg='.$idMessage.
                     '">'.$time.'</a></td>';
                
                // show message subject
                if ($is_read=='0'){
					echo '<td class="subject"><a style="font-weight: bold;" href="messages/read.php?msg='.$idMessage.'">'.$subject.'</a></td>';
				}else
					echo '<td class="subject"><a href="messages/read.php?msg='.$idMessage.'">'.$subject.'</a></td>';
                     
                // show if it is a new message
                if ($is_read=='0') $flag=get_the_flag(24, 'blue'); else $flag=$flag=get_the_flag(24, 'gray');
                echo '<td style="text-align: center;">'.$flag.'</td>';
                
                // show delete icon
                echo '<td style="text-align: center;"><a href="messages/index.php?del='.$idMessage.' title="Shred message" onclick="return confirm(\'Remove message?\');">
                <img src="'.rurl().'/images/delete.png" alt="[X]"/></a></td></tr>';
                
                $i++;
            } // end while mysql_fetch_array
            echo "</table>";
	    } else echo "You have no messages.";
    } else echo "Bad db connection.";

} else {  // or user is not logged in.
    echo 'Sorry: You need to be logged in to read messages.';
}
?>   
				</div><!-- /main -->

			</div><!-- /content-->
			
			<div id="footer">
				<?php include rdir().'/includes/footer.inc.php';?>
			</div> <!-- /footer -->

		</div><!-- /container -->
	</div><!-- /wrapper -->

</body>

</html>
