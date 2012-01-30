<ul>
<?php $u = rurl().'/admin/';
    $menu_items = array(
    
        array($u,'ADMIN ZONE'),
        array($u.'post-manager.php','POSTS'),
        array($u.'user-manager.php','USERS'),
        array(rurl(), '&nbsp;&rarr;HOME')      
    
    );
    
    $I_am_at = explode('?', "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
    foreach ($menu_items as $item){
        
        if ($I_am_at[0] == $item[0] || $I_am_at[0] == $item[0].'/')
            echo	"<li class=\"select\"><a href=\"$item[0]\">$item[1]</a></li>";
        else echo	"<li><a href=\"$item[0]\">$item[1]</a></li>";
        
    };
?>
</ul>
