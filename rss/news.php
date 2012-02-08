<?
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/admin/connect.php';

// whereis index.xml
$xml_path = $_SERVER['DOCUMENT_ROOT'].'/rss/index.xml';

// clear strings. Prepare the info
function clear($str) {
	return stripslashes(htmlentities($str));
}

// CHANNEL DATA
$rss_link=rurl().'/rss/news.php';
$rss_title='No Clan News';
$rss_description = 'Posts published at http://noclan.nooblounge.net.';
$rss_image_url = rurl().'/rss/nc-image.png';
$rss_date = date("r");

$rss_file = '<?xml version="1.0" encoding="UTF-8"?>
	<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>
		<atom:link href="'.$rss_link.'" rel="self" type="application/rss+xml"/>	
		<title>'.$rss_title.'</title>
		<link>'.rurl().'</link>
		<description>'.$rss_description.'</description>
		<language>en</language>
		<pubDate>'.$rss_date.'</pubDate>
		<image>
			<url>'.$rss_image_url.'</url>
			<title>'.$rss_title.'</title>
			<link>'.rurl().'</link>
		</image>
	';

// NEWS
$dbConn = connect_db();
$query="SELECT idPost, postFor, title, summary, summary_img, date_pub, COUNT(idComment) AS numc, username FROM posts INNER JOIN users ON posts.userId = users.idUser LEFT JOIN comments ON comments.postId = posts.idPost GROUP BY idPost HAVING posts.postFor = 'all' AND date_pub <= CURRENT_TIMESTAMP ORDER BY date_pub DESC LIMIT 0,20";
$result = mysql_query($query, $dbConn);
//...and arrage it in $arrPosts
$arrNews = array();
while ( $row = mysql_fetch_assoc ($result)) { array_push( $arrNews, strip_slashes_arr($row)); }
unset($query, $result, $row);

foreach ($arrNews as $new)
{
	// vars
	$title=clear($new['title']);         
	$link = rurl().'/post/'.$new['idPost'].'/'.friendly_str($new['title']);
	$pubDate = date("r", strtotime($new['date_pub']));
	$author = $new['username'];
	
	// comments
	if (empty($new['numc'])) { $comms = 'no comments yet'; }
	else { $comms = $new['numc'].' comments'; }
	
	// image
	if (!empty($new['summary_img'])){
		$body = '<a href="'.$link.'" title="'.$new['title'].'"><img src="'.$new['summary_img'].'" alt="'.$new['title'].'"/></a>';
	}
	else { $body = ''; }
	
	// description
	$body .= $new['summary'].'<br/>by <strong>'.$new['username'].'</strong> - <a href="'.$link.'" title="'.$new['title'].'">Read the full post</a> - <a href="'.$link.'#comments" title="Comments">'.$comms.'</a>.';
	$description=clear($body);
	
	// item
	$rss_file.='
		<item>
			<title>'.$title.'</title>
			<guid isPermaLink="true">'.$link.'</guid>
			<pubDate>'.$pubDate.'</pubDate>
			<description>'.$description.'</description>
		</item>
	';
}

// end
$rss_file .= '
	</channel>
</rss>';

header('Content-type: text/xml; charset="UTF-8"', true);
echo $rss_file;
?>
