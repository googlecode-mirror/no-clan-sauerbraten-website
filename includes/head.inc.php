<base href="<?php echo rurl();?>"/>
<title><?php echo $page_title;?></title>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<meta name="Language" content="EN"/>
<meta name="Keywords" content="NoClan, No Clan, sauerbraten, cube2, clan, fun"/>
<meta name="Description" content="No Clan: Sauerbraten Clan, since 2011"/>
<meta name="Distribution" content="Global"/>
<meta name="Robots" content="All"/>
<link rel="shortcut icon" href="<?php echo rurl();?>/favicon.ico">

<!-- STYLE -->
<link href='http://fonts.googleapis.com/css?family=Orbitron:400,500,700,900|Aldrich|Gochi+Hand' rel='stylesheet' type='text/css'>
<link rel="stylesheet" type="text/css" href="css/default.css"/>
<link rel="stylesheet" type="text/css" href="css/style.css"/>
<!-- /style -->

<!-- Fancy -->
<?php include rdir().'/includes/fancy.inc.php'?>
<!-- /Fancy -->

<!-- private messages -->
<?php if (isset($arrUser)) echo '<script language="javascript" type="text/javascript" src="'.rurl().'/js/ajax-general.js"></script>'; ?>
<!-- /private messages -->

<!-- ANALYTICS -->
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-27511799-1']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
<!-- /ANALYTICS -->

<!-- RSS -->
<link rel="alternate" title="No Clan: News" href="<?php echo rurl().'/rss/news.php';?>" type="application/rss+xml">
<!-- /RSS -->

<!-- G+ -->
<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
<!-- /being social -->
