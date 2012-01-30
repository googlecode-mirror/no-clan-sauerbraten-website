<ul>
<?php $u = rurl();
    $menu_items = array(
    
        array($u,					'HOME'),
        array($u.'/about.php',		'ETHICS'),
        //array($u.'/forum.php',   	'FORUM'),
        array($u.'/members.php',    'MEMBERS'),
        array($u.'/gallery/',    	'GALLERY'),
        array($u.'/contact.php', 	'CONTACT')
    
    );
    
    if (!empty($arrUser) && $arrUser['type'] == 'admin')
        array_push($menu_items, array($u.'/admin', 'ADMIN'));
    
    $I_am_at = explode('?', "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    foreach ($menu_items as $item){
        
        if ($I_am_at[0] == $item[0] || $I_am_at[0] == $item[0].'/')
            echo	"<li class=\"select\"><a href=\"$item[0]\">$item[1]</a></li>";
        
        else echo	"<li><a href=\"$item[0]\">$item[1]</a></li>";
        
    };
?>

</ul>
