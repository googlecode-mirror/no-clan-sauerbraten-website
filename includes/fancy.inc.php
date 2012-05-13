<script type="text/javascript" src="<?php echo rurl();?>/js/jquery.min.js"></script>
<link href="<?php echo rurl();?>/js/jquery.fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css"/>
<script src="<?php echo rurl();?>/js/jquery.fancybox/jquery.fancybox.js" type="text/javascript"></script>	
<script type="text/javascript">
	$(document).ready(function() {
		$(".fancy").fancybox({
			'type'          : 'ajax',
		    'overlayColor'	: '#000',
		    'overlayOpacity'	: 0.7,
		    'padding'   	: 1,
		    'transitionIn'	:'elastic',
		    'transitionOut'	:'fade',
		    'titleShow'		: false
		});
		
		$(".fancy_mini_main").fancybox({
			'type'          : 'ajax',
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
