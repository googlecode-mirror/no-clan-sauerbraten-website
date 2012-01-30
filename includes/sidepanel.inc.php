<?php
 // The sidepanel.inc.php file with all the includes
  include $_SERVER['DOCUMENT_ROOT'].'/includes/social-buttons.php';
  include $_SERVER['DOCUMENT_ROOT'].'/includes/userSideInfo.php';
  include $_SERVER['DOCUMENT_ROOT'].'/includes/lastlogin.inc.php';
  include $_SERVER['DOCUMENT_ROOT'].'/includes/links.inc.php';
  include $_SERVER['DOCUMENT_ROOT'].'/includes/latest_comments.inc.php';
 
 if (isset($arrUser) && ($arrUser['type']=='admin')) include $_SERVER['DOCUMENT_ROOT'].'/admin/includes/manager.inc.php';
 
?>
