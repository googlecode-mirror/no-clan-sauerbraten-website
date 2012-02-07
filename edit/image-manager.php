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



/* * * * * * * * * * * *
 * IMAGE MANAGER STARTS
 * * * * * * * * * * * */

// DELETE
if (!empty($_GET['deleteImg'])) unlink($_GET['deleteImg']);

// IMAGE VALUES

// temp dir
$dirTmpUser = '../data/tmp_data/'.$arrUser['idUser'];
if (!is_dir($dirTmpUser)) mkdir($dirTmpUser, 0777);
$workingWidth = 580; // the #main div has 620px of usable width, but large images look better with less width than the text
$workingWidthSummary = 320;
	
// GETTING VALUES FOR STORAGE
if ( !empty ($_GET['dirImgs']) && !empty($_GET['imgs']) ){
    
    $imgs = $_GET['imgs']; // a random char for a pseudo session of image manager
    $dirImgs = $_GET['dirImgs']; // name of the dir images of this sessions will be saved
    
    $storage = rdir().'/data/images/posts'; // root of the img storage
    if (!is_dir($storage.'/'.$dirImgs)) mkdir($storage.'/'.$dirImgs, 0777);    

}


// UPLOADS AN IMAGE
if ( (!empty($_POST['uploadSummary']) || !empty($_POST['uploadArticle']) ) &&  ($_FILES['file']['error'] == 0) )
{
	// Remember where the upload is
	$imgsrc = $_FILES['file']['tmp_name'];
	$ftype = $_FILES['file']['type'];
	
    // Look at what user has uploaded and make the arrangements
	if ($_FILES['file']['size'] < 5242880) // is file small enough?
	{
		switch ($ftype){
			case 'image/jpeg':	$uploaded=imagecreatefromjpeg($imgsrc); break;
			case 'image/png' :	$uploaded=imagecreatefrompng($imgsrc);  break;
			default: $error['format']='Image format not supported';
		}
	
	}else $error['size'] = 'That file is too big to handle.';
	
	if (empty($error))
	{
		// Resize the image to a working width
		if (!empty($_POST['uploadSummary'])){
			$working_up = resize_img($uploaded, $workingWidthSummary);
		}else{
			$working_up = resize_img($uploaded, $workingWidth);
		}
		imagedestroy($uploaded);
			
		// Save it always with different names (browser cache problems)
		$ruta = time().'jpg';
		// Detroy previous uploaded
		if(file_exists($dirTmpUser.'/'.$ruta)) unlink($dirTmpUser.'/'.$ruta);
		// Save image for later cropping
		imagejpeg($working_up, $dirTmpUser.'/'.$imgs.'.jpg', 90);
		imagedestroy($working_up);
	}
	
}elseif (!empty($_FILES) && $_FILES['file']['error'] == 1){		
	
	$error['upload'] = 'Problem on the uploading. Big file?';

// SUBMIT CROP
}elseif (!empty ($_POST['submitCrop']) || !empty($_POST['submitCropSummary']) && empty($error))	

{
	// image coords
	$x1 = $_POST["x1"];
	$y1 = $_POST["y1"];
	$x2 = $_POST["x1"];
	$y2 = $_POST["y1"];
	
	$width = $_POST["width"];
	$height = $_POST["height"];
	
	if (!empty($_POST['submitCropSummary'])){	
		$newWidth  = $_POST["newWidth"];
		$newHeight = $_POST["newHeight"];
	}else{
		$newWidth  = $width;
		$newHeight = $height;
	}
	
	// If user didn't selected a thing we use the same values as the resized one for the cropp.
	if ($newWidth == 0 || $newHeight == 0){
		$t = getimagesize($dirTmpUser.'/'.$imgs.'.jpg');
		$newWidth = $t[0];
		$newHeight = $t[1];
		$width = $t[0];
		$height = $t[1];
		$x1 = 0;
		$y1 = 0;
	}
	
	// NAME ...
	// will have random chars (avoid browser cache)
	list($num, $tmp) = explode(" ", microtime());
	$name = friendly_str($_POST["name"].'-'.substr($num, 2, 5));
	
	// Create the image for cropping from the resized one stored at temp
	$img = imagecreatefromjpeg($dirTmpUser.'/'.$imgs.'.jpg');

	// Create a blank image to draw the resize / crop over
	$newImg = imagecreatetruecolor($newWidth, $newHeight);
	
	// Do the resize and crop on demand
	imagecopyresampled($newImg, $img, 0, 0, $x1, $y1, $newWidth, $newHeight, $width, $height);

	// Store the new image ready to use at post
	$foto = "../data/images/posts/$dirImgs/$imgs-$name.jpg";
	if (!file_exists($foto)) imagejpeg($newImg, $foto);
	else $error['overwrite'] = 'There is an image with the same name';

	// Clean some
	imagedestroy($newImg);
	imagedestroy($img);

	// Erase tempdir
	if (is_dir($dirTmpUser)) delete_dir($dirTmpUser);
	header( "Location: image-manager.php?dirImgs=$dirImgs&imgs=$imgs" );
}



// page info
$location = rurl().$_SERVER['REQUEST_URI'];
$page_title = "NC image manager";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<?php include rdir().'/edit/includes/head.inc.php';?>

	<script src="http://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js" type="text/javascript"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/scriptaculous/1.8.2/scriptaculous.js" type="text/javascript"></script>
	<script src="<?php echo rurl();?>/js/cropper/cropper.js" type="text/javascript"></script>
	<script type="text/javascript" charset="utf-8">
		<?php if (!empty($_POST['uploadSummary'])){?>
			var laX = 120;	var laY = 64;
			var minX = 120;	var minY = 64;
		<?php }else{?>
			var laX;	var laY;
			var minX;	var minY;
		<?php }?>
	</script>

	<script type="text/javascript" charset="utf-8">
		function onEndCrop( coords, dimensions ) {$( 'x1' ).value = coords.x1; $( 'y1' ).value = coords.y1; $( 'x2' ).value = coords.x2; $( 'y2' ).value = coords.y2; $( 'width' ).value = dimensions.width; $( 'height' ).value = dimensions.height;}
		Event.observe( window, 'load', function() {new Cropper.ImgWithPreview('testImage',{minWidth: minX,minHeight: minY,ratioDim: {x: laX, y: laY},displayOnInit: true,onEndCrop: onEndCrop,previewWrap: 'previewResumen'});});
	</script>
	
	<style type="text/css">
		input[type="submit"] { margin-top: 1em; padding: 0.5em;}
	</style>
	
</head>

<body style="padding: 20px; background: #404040;">
	<div style="padding: 20px; background-color: #e0e0e0; overflow: hidden;">

			<?php // read image dir
			$path = '../data/images/posts/'.$dirImgs;
			$images = glob($path.'/{'.$imgs.'*.jpg}', GLOB_BRACE);
			$i = 1;
			
			if (!empty($images)){?>
				<div style="padding: 0.5em; background-color: #aaa">
				<h2>Uploaded images</h2>
				<?php foreach ($images as $image){?>
					<div style="clear: both; margin-bottom: 0.5em; overflow:hidden;">
						<img style="max-height: 100px; float: left; margin-right: 0.5em;" src="<?php echo $image;?>"/>
						<?php $tmp = getimagesize($image); echo $tmp[0].'x'.$tmp[1].' p&iacute;xeles';?>
						<p><?php echo rurl()."/data/images/posts/".$dirImgs.'/'.pathinfo($image, 2);?></p>
						<a href="<?php echo rurl();?>/edit/image-manager.php?dirImgs=<?php echo $dirImgs?>&imgs=<?php echo $imgs?>&deleteImg=<?php echo $image;?>" style="color: red;">[DELETE]</a>
					</div>
				<?php }?>
				</div>
			<?php }?>

		<?php if (!empty($error)){?>
			<div class="error">
			<?php foreach ($error as $e){echo '<p>'.$e.'</p>';}?>
			</div>
		<?php }?>
		
		<?php if( empty($_POST['uploadSummary']) && empty($_POST['uploadArticle'])) {?>
		<h1><?php if (empty($images)){ echo 'Upload an image'; } else { echo 'Upload another image';}?></h1>
		<form action="<?php echo rurl();?>/edit/image-manager.php?dirImgs=<?php echo $dirImgs?>&imgs=<?php echo $imgs?>" method="post" enctype="multipart/form-data" name="f">
			<label for="file">JPG or PNG < 5Mb</label><br/>
			<input type="file" name="file" onchange="javascript:document.f.uploadSummary.disabled=false;document.f.uploadArticle.disabled=false"/><br/>
			<input type="submit" name="uploadSummary" value="UPLOAD FOR SUMMARY" class="submit" id="cargaRes" disabled="disabled" />
			<input type="submit" name="uploadArticle" value="UPLOAD FOR ARTICLE" class="submit" id="cargaImg" disabled="disabled"/>
		</form>
		<?php }?>
		

		<?php if( (!empty($_POST['uploadSummary']) || !empty($_POST['uploadArticle'])) && empty($error) ){?>
			<div style="padding:5px; float: left;">
				<div class="imgResumen" <?php if (!empty($_POST['uploadArticle'])){?>style="visibility: hidden; height: 0;"<?php }?>>
					<div id="previewResumen"></div>
				</div>
				<div class="toolbar">
					<form name="cropperForm" id="results" method="post" action="<?php echo rurl();?>/edit/image-manager.php?dirImgs=<?php echo $dirImgs?>&imgs=<?php echo $imgs?>">
						<input type="hidden" name="x1" id="x1" />
						<input type="hidden" name="y1" id="y1" />
						<input type="hidden" name="x2" id="x2" />
						<input type="hidden" name="y2" id="y2" />

						<div id="testWrap">
							<?php if(!empty($_POST['uploadArticle'])){?>
								<h1>Clic&amp;drag to select the zone and crop the image</h1>
							<?php }else{?>
								<h1>Select and resize the area for the Summary image</h1>
							<?php }?>
							<img src="<?php echo $dirTmpUser.'/'.$imgs.'.jpg'?>" alt="test image" id="testImage" />
						</div>
						
						<?php if(!empty($_POST['uploadArticle'])){?>
						<input type="text" name="width" id="width" size="3"/>x
						<input type="text" name="height" id="height"size="3"/>
						<?php }else{?>
						<input type="hidden" name="width" id="width" size="3"/>
						<input type="hidden" name="height" id="height"size="3"/>
						<input type="hidden" name="newWidth" size="3" value="120"/>
						<input type="hidden" name="newHeight" size="3"value="64"/>
						<p>170 x 110 p&iacute;xeles<br/></p>
						<?php }?>
						
						<label for="nombre">Image Name: </label>
						<input name="name" type="text" value="image-name" onclick="this.value='';"/></span><br/>


						<?php if(!empty($_POST['uploadSummary'])){?>
						<input class="submit" name="submitCropSummary" type="submit" value="SAVE SELECTION"/>
						<input class="submit" name="submitCancelCrop" type="submit" value="CANCEL"/>
						<?php }else{?>
						<input class="submit" name="submitCrop" id="cropButton" type="submit" value="SAVE SELECTION"/>
						<input class="submit" name="cancel" type="submit" value="CANCEL"/>
						<?php }?>
					</form>
				</div>
			</div>


		<?php }?>
 
	</div><!-- /main -->
</body>

</html>
