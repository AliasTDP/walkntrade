<?php $query = (isset($_GET["query"])) ? $_GET["query"] : ""; ?>
<!DOCTYPE html>
<html>
<head>
	<title>Walkntrade at <?php echo $cs->getSchoolName($schoolTextId); ?></title>
	<link type="text/css" rel="stylesheet" href="/css/style.css">
	<link type="text/css" rel="stylesheet" href="/css/login_window.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link type="text/css" rel="stylesheet" href="/css/results.css">
	<link type="text/css" rel="stylesheet" href="/css/feedback_slider.css">
	<link rel="shortcut icon" href="http://www.walkntrade.com/favicon.ico?v=2" />
	<meta name="description" content="Walkntrade at <?php echo $cs->getSchoolName($schoolTextId); ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en" />
	<script type="text/javascript" src="/client_js/include.js"></script>	
	<script type="text/javascript" src="/client_js/jquery.min.js"></script>
	<script type="text/javascript" src="/client_js/user_login.js"></script>
	<script type="text/javascript" src="/client_js/results.js"></script>
	<script type="text/javascript" src="/client_js/feedback_slider.js"></script>	
	<script type="text/javascript">var schoolName = "<?php echo $schoolTextId; ?>"</script>
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-42896980-1', 'walkntrade.com');
	ga('send', 'pageview');

	</script>
	<script type="text/javascript">window.school="<?php echo $schoolTextId ?>"</script>
</head>
<body>
<div id="throbber"><img src="/colorful/loader.gif"></div>
<div class="headerBar"></div>
<div id="pageHead"><?php $noLogin=false; include(ROOTPATH."/include/header.php"); ?></div>
<div id="sidebar"><?php include("include/sidebar.php");?></div>
<div class="wrapper" id="mainWrap">
	<div class="wF" id="mainCWB">
		<div style="height:45px">
			<div id="Nav">
				<ul>
					<li id="all">All</li>
					<li id="book">Textbooks</li>
					<li id="service">Services</li>
					<li id="tech">Tech/Games</li>
					<li id="misc">Miscellaneous</li>
				</ul>
			</div>
		</div>
		<div id="dynamicWrapper">
			<div id="dynamicContent">
				<div id="filterBar"><font style="font-size:1.35em"><?php echo $cs->getSchoolName($schoolTextId);?></font>: <a href="javascript:(w_sort(1))">Newest First</a> | <a href="javascript:(w_sort(2))">Oldest First</a> | <a href="javascript:(w_sort(4))">Lowest Price</a> | <a href="javascript:(w_sort(3))">Highest Price</a></div>
				<div id="message">
					No results :(
					<p style="font-size:.5em;line-height:2em">We're sorry we couldn't find what you're looking for... <br>Try refining your search or selecting a different category or school.</p>
				</div>
				<div id="dump">
				</div>
			</div>
		</div>
	</div>
	<div class="push"></div>
</div>
<div class="footerBar blur">
	<?php include(ROOTPATH."/include/footer.html"); ?>
</div>
</body>
</html>
