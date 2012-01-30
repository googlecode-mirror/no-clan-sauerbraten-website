<script type="text/javascript" src="<?php echo rurl();?>/js/jquery.min.js"></script>
<link href="<?php echo rurl();?>/js/jquery.fancybox/jquery.fancybox-1.3.4.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo rurl();?>/js/jquery.fancybox/jquery.fancybox-1.3.4.pack.js" type="text/javascript"></script>	
<script type="text/javascript">
	$(document).ready(function() {
		$(".fancy").fancybox({
		    'overlayColor'	: '#000',
		    'overlayOpacity'	: 0.7,
		    'padding'   	: 1,
		    'transitionIn'	:'elastic',
		    'transitionOut'	:'fade',
		    'titleShow'		: false
		});
		
		$(".fancy_mini_main").fancybox({
		'overlayColor'	: '#000',
	        'overlayOpacity': 0.7,
	        'padding'   	: 1,
	        'hideOnOverlayClick':false,
	        'transitionIn'	:'elastic',
	        'transitionOut'	:'fade',
	        'titleShow'	: false,
	        'type'		: 'iframe',
	        'width':673,
	        'scrolling': 'auto'
	        
		});
	});
</script>
