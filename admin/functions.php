<?php

/* * * * * * * * *
 * GENERAL STUFF *
 * * * * * * * * */
function nc_error_handler ($e_number, $e_message, $e_file, $e_line,$e_vars) {
	/* HANDLES PHP / MYSQL ERRORS
	 *  Useful during development AND during live mode.
	 */
	  
 // are we live? (0=development phase, 1=we're live)
 // 0 dumps errors on the screen
 // 1 shows generic message on screen, sends detailed message to email(s)
 $live=1; 
 $contact_email1='sauerbraten.no.clan@gmail.com';
 $contact_email2='astrafo02@gmail.com';
 $message = "An error occurred in script '$e_file' on line $e_line:\n$e_message\n";
 $message .= "<pre>" .print_r(debug_backtrace( ), 1) . "</pre>\n";
 if (!$live) {
   echo '<div class="error">' . nl2br($message) . '</div>';
 } else {
  error_log($message, 1, $contact_email1, 'From: error@noclan.nooblounge.net');
  error_log($message, 1, $contact_email2, 'From: error@noclan.nooblounge.net');
 if ($e_number != E_NOTICE) {
   echo '<div class="error">A system error occurred. We apologize for the inconvenience.</div><br>';
 }
 } // End of $live IF-ELSE.
 return true;
} // End of nc_error_handler( ) definition.

set_error_handler('nc_error_handler');

function get_random_string($len = 16){
  $code = md5(uniqid(rand(), false));
  return substr($code, 0, $len);
}

function rurl()
{
    /* RETURNS THE ROOT URL OF THE SITE
     * Useful for working with absolute paths.
     * Ex. $location = rurl().'/directory/file.php';
     */
    return 'http://'.$_SERVER['HTTP_HOST'];
}

function rdir()
{
    /* RETURNS THE ROOT DIRECTORY OF THE SITE
     * Useful with file checkings and includes.
     * Ex. isfile(rdir().'/foo/bar.kk';
     */
    return $_SERVER['DOCUMENT_ROOT'];    
}

function go_home()
{
    /* It doesn't unset the $_SESSION[NC_*]
     * Useful to redirect from /admin/files.php if $arrUser not admin
     */
    $location = rurl();
    header("Location: $location");
    die;
}

function friendly_str($str)
{
    /* RETURNS AN USER-FRIENDLY STRING containing
     * 64-alphanumeric-string-without-spaces.
     * Useful for friendly URLs. Also for search
     * robots and nerds who cannot understand
     * standard languajes
     */
	
    // everything goes lowercase
	$str = strtolower($str);

	// kick special chars
	$find = array('á', 'é', 'í', 'ó', 'ú', 'ñ', 'â', 'ê', 'î', 'ô', 'û', 'à', 'è', 'ì', 'ò', 'ù', 'ä', 'ë', 'ï', 'ö', 'ü', 'ç');
	$repl = array('a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u', 'c');
	$str = str_replace ($find, $repl, $str);

	// kick spaces
	$find = array(' ', '&', '\r\n', '\n', '+');
	$str = str_replace ($find, '-', $str);

	// kick more special chars
	$find = array('/[^a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/');
	$repl = array('', '-', '');
	$str = preg_replace ($find, $repl, $str);

	return substr($str, 0, 64);
}



/* * * * * * * * * * *
 * USER DATA CONTROL *
 * * * * * * * * * * */

function process_content($str){
    /* Make up the user code sent at html textareas
     * for uploading to the database
     * */
    
    $str = trim($str);
    
    // Remove all tags except $exceptions
    $exceptions = "<strong>"."<em>"."<b>"."<i>";
    $str = strip_tags($str, $exceptions);   
    
    // kick weird characters and put pure html
    $str = htmlspecialchars($str, ENT_QUOTES);
    // rePut the allowed tags
    $str = str_replace(array('&lt;', '&gt;'), array('<', '>'), $str);
    
    // Only 3 linebreaks are allowed
    $red = preg_replace('/\r/', '', $str);
    $blue = preg_replace('/\n{3,}/', str_repeat('<br />', 2), preg_replace('/\r/', '', $red));
    
    // change linebreaks to <br />
    $str = nl2br($blue);
	
	// Check the result. Empty comments or strings containing
	// less than 4 chars are not welcome.
	if ($str == '' || strlen($str) < 4) return false;
    
    else return $str;
}

function are_tags_closed($str)
{
    /* Check the string for open tags
     * RETURNS True = all good
     * or FALSE = There are tags unclosed
     * */
    preg_match_all("/(<\w+)(?:.){0,}?>/", $str, $v1);
    preg_match_all("/<\/\w+>/", $str, $v2);
       
    $open = array_map('strtolower', $v1[1]);
    $closed = array_map('strtolower', $v2[0]);
       
    foreach ($open as $tag) {
        $end_tag = preg_replace("/<(.*)/", "</$1>", $tag);
        if (!in_array($end_tag, $closed)) return false;
           
        unset($closed[array_search($end_tag, $closed)]);
    }
    return true;
}

function process_mini_editor($str)
{
    
    /* All this function is based on the mceEditor string output.
     * 
     * MiniEditor has bold, itallic, underline and line-trough buttons
     * and uses <p> in all text. No <br/>s.
     * This func. will strip tags except allowed and all the linebreaks
     * user writes.
     *
     * As MCEeditor automagically puts \n on editor view everytime
     * it sees a </p>, there is no need to manage the \n once removed.
     */
    
    // No linebreaks. We use <p> 
    $str = str_replace(array("\r\n", "\r", "\n"), "", $str);
    // No html linebreaks either.
    $str = str_replace("<p>&nbsp;</p>", "", $str);
    // text decorations allowed. Own tags on it
    $str = preg_replace('#(<span style="text-decoration: line-through;")>(.*?)<(/span>)#is', '[--$2--]', $str);
    $str = preg_replace('#(<span style="text-decoration: underline;")>(.*?)<(/span>)#is', '[__$2__]', $str);
    // Remove all tags except $exceptions
    $exceptions = "<strong>"."<em>"."<p>";
    $str = strip_tags($str, $exceptions);   
    // Reput the underlines and line-throughs spans with own tags
    $own = array('[--', '--]', '[__', '__]');
    $tag = array('<span style="text-decoration: line-through;">', '</span>', '<span style="text-decoration: underline;">', '</span>');
    $str = str_replace($own, $tag, $str);
    // done
    return $str;
}

function process_full_editor($str)
{
    /* All this function is based on the mceEditor string output.
     * 
     * fullEditor has all the format buttons we want
     * and uses <p> in all text. No <br/>s.
     * This func. will only strip the linebreaks user writes.
     * 
     * All tags remain.
     * 
     * As MCEeditor puts linebreaks (\n) on editor view everytime
     * it sees a </p>, no need to manage the \n once removed.
     */
    
    // No linebreaks. We use <p> 
    $str = str_replace(array("\r\n", "\r", "\n"), "", $str);
    // No html linebreaks either.
    //$str = str_replace("<p>&nbsp;</p>", "", $str);
    return $str;
}

function cut_string($str, $limit_chars = 60)
{
	/* Cuts a string at $limit number of chars,
	 * but always returns whole words.
	 * Also, takes care about tags (cutting a strig
	 * can leave tags opened).
	 * And puts "last word..." if needed.
	 * */
	 
	 // conver the linebreaks into spaces
	 $str = preg_replace('/\<br(\s*)?\/?\>/i', ' ', $str);
	 // strip tags
	 $str = strip_tags($str);
	 // get the words
	 $words = explode(' ', $str);
	 $newstr = '';
	 
	 foreach ($words as $w){
		if (strlen($newstr) < $limit_chars){
			$newstr .= "$w ";
		}
	 }
	 
	 if (strlen($newstr) < strlen($str)){
		$newstr = substr($newstr, 0, -1).'...';
	 }
	
	unset($str, $words, $w, $limit_chars);
	
	return $newstr;
}

function is_email_valid($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)){
		return true;	
	} else return false;
    
}


function is_url_ok($url)
{
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

function get_pass($str)
{
    //double salt & hash the password
	$p = SALT_1.$str.SALT_2; 
    $password = hash_hmac('sha256', $p, HASH_KEY, false);
    return $password;
}

function canput($str, $else = '')
{
    // Put values from POSTS if we have them or put something $else
    if (!empty ($str)) echo stripslashes($str);
    else echo $else;
}

function strip_slashes_arr($arr)
{
/* Recursie function that strip slashes
 * on values of an array.
 */
    if (is_array($arr)){
        foreach ($arr as $i => $v){
            if (is_array($v)){
                strip_slashes_arr($v);
            }
            else{
                $arr[$i] = stripslashes($v);
            }
        }
        return $arr;
    }else return false;
}

/* * * * * * * * * *
 * FILE MANAGEMENT *
 * * * * * * * * * */

function delete_dir($dir)
{
    /* Recursive function that emulates "rm -rf dir" */
	if (is_dir($dir)){
	    
		foreach(glob($dir."/*") as $dir_file){
		    if(is_dir($dir_file)) delete_dir($dir_file);
		    else unlink($dir_file);
		}
		rmdir($dir);
	}
}

function get_user_pic($idUser, $pixels)
{
    /* Returns the absolute URL of the userpic.
     * Works on 128, 96, 48, 32 and 24 pixels w/h
     * If user doesn't have and uploaded image,
     * return the generic one.
     */    
    if (file_exists($_SERVER['DOCUMENT_ROOT'].'/images/users/'.$idUser.'-img'.$pixels.'.png')){
        $url = rurl().'/images/users/'.$idUser.'-img'.$pixels.'.png';			    
    }else{
	    $url = rurl().'/images/users/generic-img'.$pixels.'.png';
    }
    return $url;
}

/* * * * * * * * * * 
 * DATE MANAGEMENT *
 * * * * * * * * * */

function date2dateDir($date)
{
    /* In order to store files in auto mode using info from the
     * database and then find it in human mode or robot mode,
     * a good system is to storing files in directories named
     * as YEARMONTH. That way, an entry created on October 2020
     * will have the images stored at /images/202010/files.img
     */
	//ereg( "([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2})", $date, $d);
	//$dataDir=$d[1].$d[2];
    return date("Ym", strtotime($date));
}

function how_long_since($date)
{
/* Returns how long has it been since a standard mysql
 * timestamp (like "2020-30-05 12:45:30") in a very
 * subjective way on understanding time.
 * String returned is like XX minutes, hours, days ago
 * or a normal date if it was more than a week ago.
 * Also includes year if it was on past years.
 * It can also calculate future date differences.
 */
    
    if(empty($date)) return "No date provided";
   
    $periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths         = array("60","60","24","7","4.35","12","10");
    $now             = time();
    $unix_date         = strtotime($date);
   
    // check validity of date
    if(empty($unix_date)) return "Bad date";

    // is it future date or past date
    if($now > $unix_date) {   
        $difference     = $now - $unix_date;
        $tense         = "ago";
    } else {
        $difference     = $unix_date - $now;
        $tense         = "from now";
    }
   
    // check which class (minutes? hours? days?) fits our case and count how many
    for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
        $difference /= $lengths[$j];
    }
   
    $difference = round($difference);
   
    if($difference != 1) $periods[$j].= "s";
   
    $str = "$difference $periods[$j] {$tense}";
    if (($str == '1 day ago') or ($str == '24 hours ago')) $str='yesterday';
    else if (($str=='1 day from now') or ($str=='24 hours from now'))$str='tomorrow';

    return $str;
}


/* * * * * * * * * *\ 
 * IMAGE MANAGEMENT *
\* * * * * * * * * */

function resize_img($img, $finalW)
{
/* Takes the image and the maxwith you want to work
 * Returns a new image with the working width
 * 
 * If image width is lower than the working width working width becomes original width.
 * NOTE: If this functions takes too much time, use imagecopresized(), but quality will be low.
 */
     
	$imgW = imagesx($img);
	$imgH = imagesy($img);
	
	if ($imgW <= $finalW){
		$finalW = $imgW;
		$finalH = $imgH;	
	}
	else{
		$finalH = $finalW*$imgH/$imgW; 
	}
	
	$newImg = imagecreatetruecolor($finalW, $finalH);
	imagecopyresampled($newImg, $img, 0, 0, 0, 0, $finalW, $finalH, $imgW, $imgH);
		
	return $newImg;
	
	imagedestroy($img);
	imagedestroy($newImg);
}

function img_resize($img, $w, $newfilename) {
 
	// Check if GD extension is loaded
	if (!extension_loaded('gd') && !extension_loaded('gd2')) {
		trigger_error("GD is not loaded", E_USER_WARNING);
		return false;
	}
 
	// Get Image size info
	$imgInfo = getimagesize($img);
	
	switch ($imgInfo[2]) {
		case 1: $im = imagecreatefromgif($img); break;
		case 2: $im = imagecreatefromjpeg($img);  break;
		case 3: $im = imagecreatefrompng($img); break;
		default:  trigger_error('Unsupported filetype!', E_USER_WARNING);  break;
	}
	
	$srcW = imagesx($im);
	$srcH = imagesy($im);
	
	// If image width is smaller, do not resize
	if ($srcW <= $w) {
		$w = $srcW;
	}
	// get the new height
	$nHeight = $w*$srcH/$srcW;
	
	$nWidth = round($w);
	$nHeight = round($nHeight);
	
	// create a blank image
	$newImg = imagecreatetruecolor($nWidth, $nHeight);
 
	// Check if this image is PNG or GIF, then set if Transparent
	if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)){
		imagecolortransparent($newImg, imagecolorallocatealpha($newImg, 0, 0, 0, 127));
		imagealphablending($newImg, false);
		imagesavealpha($newImg, true); 
	}
	imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $srcW, $srcH);
 
	//Generate the file, and rename it to $newfilename
	switch ($imgInfo[2]) {
		case 1: imagegif($newImg,$newfilename); break;
		case 2: imagejpeg($newImg,$newfilename);  break;
		case 3: imagepng($newImg,$newfilename); break;
		default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
	}
   
   return $newfilename;
}

/* * * * * * * * *
 * MISC & BEAUTY *
 * * * * * * * * */
 
function get_the_flag($size = 24, $color = "red"){
	/* Get the hmtl code for the flags icon Andreas made
	 * Accepts 'red', 'blue' and gray color strings.
	 */ 
	return '<img src="'.rurl().'/images/sauer/'.$color.'flag'.$size.'.png" alt="'.$color.' flag"/>';
}

function url_flag($size = 24, $color = "red"){
	/* Get the URL for the flags icon Andreas made
	 * Accepts 'red', 'blue' and gray color strings.
	 */ 
	return rurl().'/images/sauer/'.$color.'flag'.$size.'.png';
}

function browser_dettect()
{	/* Returns the user wen browser */

	$user_agent = $_SERVER['HTTP_USER_AGENT'];

	$browsers = array
	(  
		'Opera' => 'Opera',  
		'Firefox'=> '(Firebird)|(Firefox)',  
		'Galeon' => 'Galeon',  
		'Mozilla'=>'Gecko',  
		'MyIE'=>'MyIE',
		'Lynx' => 'Lynx',  
		'Netscape' => '(Mozilla/4\.75)|(Netscape6)|(Mozilla/4\.08)|(Mozilla/4\.5)|(Mozilla/4\.6)|(Mozilla/4\.79)',  
		'Konqueror'=>'Konqueror',
		'IE10' => '(MSIE 10\.[0-9]+)',  
		'IE9' => '(MSIE 9\.[0-9]+)',  
		'IE8' => '(MSIE 8\.[0-9]+)',  
		'IE7' => '(MSIE 7\.[0-9]+)',  
		'IE6' => '(MSIE 6\.[0-9]+)',  
		'IE5' => '(MSIE 5\.[0-9]+)',  
		'IE4' => '(MSIE 4\.[0-9]+)',  
	);  
	foreach($browsers as $b=>$pattern){  
		if (eregi($pattern, $user_agent))	return $b;
	    else return 'unknown';
	}  
}
?>
