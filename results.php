<?php
$serverName = basename($_SERVER["SERVER_NAME"]);
$serverURI = $_SERVER['REQUEST_URI'];
$validDomains = array("walkntrade.com", "dev.wt", "50.147.246.201", "172.16.10.71");
$redirect = true;
foreach ($validDomains as $domain) {
	if($serverName === $domain){
		$redirect=false;
	}
}
if($redirect)
	header( 'Location: https://walkntrade.com'.$serverURI, true, 301 );

$query = (isset($_GET["query"])) ? $_GET["query"] : ""; 
?>

<?php
$userAgent = $_SERVER["HTTP_USER_AGENT"];
$mobileDevices = array("Android", "iPhone", "iPad", "Windows Phone");

foreach ($mobileDevices as $mobileDevice) {
    if(strpos($userAgent, $mobileDevice) !== false){
        include("mobile/results.php");
        return;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Walkntrade at <?php echo $cs->getSchoolName($schoolTextId); ?></title>
	<link type="text/css" rel="stylesheet" href="/css/style.css">
	<?php if(!$loggedIn){echo'<link type="text/css" rel="stylesheet" href="/css/login_window.css">';} ?>
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link type="text/css" rel="stylesheet" href="/css/results.css">
	<link rel="shortcut icon" href="http://www.walkntrade.com/favicon.ico?v=2" />
	<meta name="description" content="Walkntrade at <?php echo $cs->getSchoolName($schoolTextId); ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en" />
	<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-42896980-1', 'auto');
  ga('send', 'pageview');

	</script>
</head>
<body>
	<div id="throbber"><img width="42px" height="42px" src="/colorful/loader.gif"></div>
	<div class="headerBar"></div>
	<div id="pageHead"><?php $noLogin=false; include(ROOTPATH."/include/header.php"); ?></div>
	<div id="sidebar"><?php include("include/sidebar.php");?></div>
	<div class="wrapper" id="mainWrap">
		<div class="wF" id="mainCWB">
			<div style="height:45px">
				<div id="Nav">
					<ul>
						<li id="all" class="selected">Everything</li>
						<li id="book">Textbooks</li>
						<li id="housing">Housing</li>
						<li id="tech">Tech</li>
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
<script type="text/javascript" src="/script/jquery.min.js"></script>
<script type="text/javascript" src="/script/walkntrade.js"></script>	
<script type="text/javascript">window.school="<?php echo $schoolTextId ?>";initResults();</script>
