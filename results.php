<!DOCTYPE html>
<html>
<head>
	<title>Walkntrade at <?php echo $cs->getSchoolName($schoolTextId); ?></title>
	<link type="text/css" rel="stylesheet" href="/css/style.css">
	<link type="text/css" rel="stylesheet" href="/css/login_window.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link type="text/css" rel="stylesheet" href="/css/results.css">
	<link type="text/css" rel="stylesheet" href="/css/feedback_slider.css">
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
<div class="blur" style="position:absolute;left:-3px;top:0px;z-index:50;width:78px;height:75px;background:url('http://cdn.choopia.com/images/beta-ribbon.png') no-repeat"></div>
<div class="headerBar blur"></div>
<div class="schoolHeaderBar">
	<div id="accent"></div>
	<div id="searchWrap">
		<div id="searchWrapInner">
			<form name="search" action="" onsubmit="updatePage(); return false" method="GET">
			<?php $preFill = (isset($query) && $query != null) ? $query : "Find something!"; ?>
			<input id="queryBar" placeholder="<?php echo $preFill ?>">
			<input type="submit" value="search">
		</form>
		</div>
	</div>
	<div id="schoolInfo">
		<div id="schoolWrapper">
			<a href="javascript:changeSchools()"><?php echo $cs->getSchoolName($schoolTextId); ?></a>
		</div>
	</div>
</div>
<div class="wrapper">
	<div id="pageHead" class="blur">
		<?php $noLogin=false; include(ROOTPATH."/include/header.php"); ?>
	</div>
	<div class="wF blur boxStyle1" id="mainCWB">
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
				<div id="filterBar">Sort by: <a href="javascript:(w_sort(1))">Newest first</a> | <a href="javascript:(w_sort(2))">Oldest first</a> | <a href="javascript:(w_sort(4))">cheapest first</a> | <a href="javascript:(w_sort(3))">expensive first</a></div>
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
