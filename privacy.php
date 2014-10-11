<?php
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$loggedIn = $cs->getLoginStatus();
?>
<!DOCTYPE html>
<html>
<head>
	<title>walkNtrade.com | Privacy Policy</title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link type="text/css" rel="stylesheet" href="css/login_window.css">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="description" content="Privacy Policy" >
	<meta name="robots" content="NOINDEX, NOFOLLOW" />
	<meta http-equiv="Content-Language" content="en">
	<script type="text/javascript" src="/client_js/include.js"></script>	
	<script type="text/javascript" src="/client_js/jquery.min.js"></script>
	<script type="text/javascript" src="/client_js/user_login.js"></script>
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-42896980-1', 'walkntrade.com');
	ga('send', 'pageview');

	</script>
</head>
<body>
	<div id="throbber"><img src="colorful/loader.gif"></div>
		<div class="headerBar"></div>
		<div id="pageHead">
				<?php $noLogin=true; include("include/header.php"); ?>
		</div>
		<div id="sidebar"><?php include("include/sidebar.php");?></div>
		<div class="wrapper">
			<div class="wF">
				<div class="boxStyle1 justifyCenter75">
					<style type="text/css">span{margin: 0em 1em 0em 0em;}</style>
					<span style="font-size:1.15em;color:#C0C0C0"><a href="ToS">Terms of Service</a></span> <span style="font-size:1.5em">Privacy Policy</span><span style="font-size:1.15em;color:#C0C0C0"><a href="feedback">Feedback</a></span>
					<hr>
					<p><ul>
						<li>We don't share your information with third parties for marketing purposes.</li>
						<li>We don't engage in cross-marketing or link-referral programs with other sites.</li>
						<li>Account password information is encrypted.</li>
						<li>We do not knowingly collect any information from persons under the age of 13. If we learn that a posting is by a person under the age of 13, we will remove that post as well as the associated account.</li>
						<li>We may provide links to third party websites, which may have different privacy practices. We are not responsible for, nor have any control over, the privacy policies of those third party websites, and encourage all users to read the privacy policies of each and every website visited.</li>
					</ul></p>
					<p><b>What we collect</b></p>
					<p><ul>
						<li>All posts are stored in our database, and may be even after "deletion," or may be archived elsewhere.</li>
						<li>Our web logs and other records are stored indefinitely.</li>
						<li>Although we make good faith efforts to store the information in a secure operating environment that is not available to the public, we cannot guarantee complete security.</li>
					</ul></p>
					<p><b>What we may share</b></p>
					<p><ul>
						<li>We may disclose information about its users if required to do so by law or in the good faith belief that such disclosure is reasonably necessary to  respond to subpoenas, court orders, or other legal process.</li>
						<li>We may also disclose information about users to law	enforcement officers or others, in the good faith belief that such disclosure is reasonably necessary to enforce our <b><a href="ToS">Terms of Service</a></b></li>
					</ul></p>
				</div>
			</div>
		</div>
		<div class="footerBar">
			<?php include("include/footer.html"); ?>
		</div>
</body>
</html>
