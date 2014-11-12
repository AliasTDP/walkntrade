<?php
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$schoolTextId = $cs->cookieCheck("sPref");
if($cs->getSchoolName($schoolTextId) == null)
	header('Location: selector') ;
if(!$loggedIn = $cs->getLoginStatus())
	header('Location: ./');
?>
<!DOCTYPE html>
<html>
<head>
	<title>walkNtrade.com | My Account</title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link type="text/css" rel="stylesheet" href="css/user_settings.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link type="text/css" rel="stylesheet" href="css/login_window.css">
	<link rel="shortcut icon" href="http://www.walkntrade.com/favicon.ico?v=2" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="Content-Language" content="en">
</head>
<body>
	<div id="screen_solid"><div id="loadingGIF2"><img src="/colorful/loading2.gif"></div></div>
	<div id="throbber"><img src="colorful/loader.gif"></div>
	<div class="headerBar"></div>
	<div id="pageHead"><?php $noLogin=false; include("include/header.php"); ?></div>
	<div class="wrapper">
	</div>
	<div id="marginOverrun">
		<div id="navBar">
			<div id="userModule"></div>
		</div>
		<div id="contentTab">
		</div>
	</div>
	<div class="footerBar"><?php include("include/footer.html"); ?></div>
</body>
</html>
<script type="text/javascript" src="/client_js/jquery.min.js"></script>
<script type="text/javascript" src="/js_minified/min.js"></script>
<script type="text/javascript">initCP()</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-42896980-1', 'auto');
  ga('send', 'pageview');

</script>