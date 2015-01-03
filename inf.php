<?php
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$loggedIn = $cs->getLoginStatus();
if (!isset($_GET["i"])){
	header("Location: ./");
	return;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Walkntrade | Feedback</title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link type="text/css" rel="stylesheet" href="css/login_window.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link rel="shortcut icon" href="http://www.walkntrade.com/favicon.ico?v=2" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="description" content="Feedback" >
	<meta name="robots" content="NOINDEX, NOFOLLOW" />
	<meta http-equiv="Content-Language" content="en">
	<script type="text/javascript" src="/script/walkntrade.js"></script>	
	<script type="text/javascript" src="/script/jquery.min.js"></script>
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-42896980-1', 'walkntrade.com');
	ga('send', 'pageview');

	</script>
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
	<?php if(!$loggedIn){include("include/login_window.html");} ?>
	<div id="throbber"><img src="colorful/loader.gif"></div>
		<div class="headerBar"></div>
		<div id="pageHead" class="blur"><?php include("include/header.php"); ?></div>
		<div class="wrapper">
			
			<div class="wF boxStyle1">
				<div style="width:500px;margin:auto;text-align:center">
					<?php
					switch ($_GET["i"]) {
						case 1000:
							echo('
							<h1> We\'ve sent you an email</h1>
							<p>Check your in-box and click the link provided in order to activate your account.</p>
							<p>you may also enter your verification code here</p>
							<br>
							<form name="code" method="GET" action="validateKey">
							<input name="token" class="inputField2" style="width:6em"></p>
							</form>
							');
							break;
						
						case 2000:
							echo('
							<h2>Server error</h2>
							<p>An error has occurred sending the verification email, please contact feedback@walkntrade.com</p>
							');
							break;
						case 3000:
							echo('
							<h2>Invalid Key</h2>
							<p>Either the link is no longer valid or you mistyped the key.</p>
							');
							break;
						case 4000:
							echo('
							<h2>Activation complete!</h2>
							<p>Your email address has been verified!</p>
							');
							break;
						case 5000:
							echo('
							<h2>This email address is alredy in use...</h2>
							<p>Maybe you already have an account. Try logging in.</p>
							');
							break;
						default:
							# code...
							break;
					}
					?>
				</div>
			</div>
		</div>
		<div class="footerBar blur">
			<?php include("include/footer.html"); ?>
		</div>
</body>
<style type="text/css">
	.wrapper{
		margin-left: 0px;
	}
</style>
</html>
