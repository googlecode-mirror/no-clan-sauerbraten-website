<?php
/* LINKS
 * use Title, url and blue/red for internal/external link
 */
 
$links = array(
	array('F.A.Q.', rurl().'/faq.php', 'blue'), 
	array('Hints &amp; tips', rurl().'/hints-tips.php', 'blue'), 
	array('Nooblounge &lt;3', 'http://www.nooblounge.net/', 'red'),
	array('Cube 2: Sauerbraten', 'http://sauerbraten.org/', 'red'),
	array('Sauerbraten World League', 'http://swl-cube2.org/', 'red')
);
?>
<div id="links">
    <h3>Links</h3>
    <div>
        <ul>
        <?php foreach ($links as $l){
			echo '<li><a';
            // if it's not locally hosted, open in a new window
            if ($l[2]=='red') echo ' target="_blank"';
            echo ' href="'.$l[1].'" title = "'.$l[0].'">'.get_the_flag(24, $l[2]).'&nbsp;'.$l[0].'</a></li>';
		}?>
		</ul>
	</div>
</div>
