<?php
/* ADMIN LINKS
 * use Title, url and blue/red for internal/external link
 */
 
$links = array(
	array('Backup database', rurl().'/admin/backup.php', 'blue') 
);
?>
<div id="links">
	<h3>Links</h3>
	<div>
		<ul>
		<?php foreach ($links as $l){
			echo '<li><a href="'.$l[1].'" title = "'.$l[0].'">'.get_the_flag(24, $l[2]).'&nbsp;'.$l[0].'</a></li>';
		}?>
		</ul>
	</div>
</div>
