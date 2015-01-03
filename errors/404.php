<?php 
require_once "../framework/CredentialStore.php";
$cs = new CredentialStore();
$schoolTextId = basename(getcwd());
$loggedIn = $cs->getLoginStatus();
?>
<html>

<head>
	<title>Walkntrade - Buy and sell on campus!</title>
	<link type="text/css" rel="stylesheet" href="/css/style.css">
	<link type="text/css" rel="stylesheet" href="/css/login_window.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link href='https://fonts.googleapis.com/css?family=Gochi+Hand' rel='stylesheet' type='text/css'>
	<link rel="shortcut icon" href="http://www.walkntrade.com/favicon.ico?v=2" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<script type="text/javascript" src="/script/walkntrade.js"></script>
	<script type="text/javascript" src="/script/jquery.min.js"></script>
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', 'UA-42896980-1', 'auto');
	  ga('send', 'pageview');

	</script>
	
	<style type="text/css">
		#pageHead #rightHead #searchWrapper{
			padding: 0px 0px;
			float: right;
			width:300px;
		}
		#wtlogo{
			position:relative;
			left:950px;
			bottom: 50px;
		}
		#container {
			position: relative;
			width: 600px;
			left:300px;
		}
		#errorHeader {
			position: relative;
			font-size:40px;
		}
		#errorImage {
			position: relative;
			width:380px;
			left:110px;
		}
		#errorMessage{
			font-weight: bold;
			font-size:20px;
		}
	</style>
</head>
	
	<body style="background:none">
	<div id="throbber"><img src="/colorful/loader.gif"></div>
	<div class="headerBar blur"></div>
	<div id="pageHead" class="blur"><?php $noLogin=false; include("../include/header.php"); ?></div>
	<div class="wrapper">
		<h1>Page not Found</h1>
		<div id="container">
			<div id="errorHeader">Uhh...</div>
			<div id="errorImage"><a href="/"><img src="/errors/404.jpg" /></a></div>
			<div id="errorMessage">Looks like you made a wrong turn or clicked a bad link. Click the gerbil to go home.</div>
		</div>
	</div>
</body>

</html>
