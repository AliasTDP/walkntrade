<?php
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$schoolTextId = basename(getcwd());
$loggedIn = $cs->getLoginStatus();
?>
<!DOCTYPE html>
<html>
<head>
	<title>walkNtrade.com | Terms of Service</title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link type="text/css" rel="stylesheet" href="css/login_window.css">
	<link rel="shortcut icon" href="http://www.walkntrade.com/favicon.ico?v=2" />
	<meta name="description" content="Terms of Service" >
	<meta name="robots" content="NOINDEX, NOFOLLOW" />
	<meta http-equiv="Content-Language" content="en">
	<style type="text/css">
		.wrapper{
			margin-left: 0px;
		}
	</style>
</head>
<body>
	<div id="throbber"><img src="colorful/loader.gif"></div>
	<div class="headerBar"></div>
	<div id="pageHead"><?php $noLogin=false; include("include/header.php"); ?></div>
	<div class="wrapper">
		<div class="wF">
			<div class="boxStyle1" style="position:absolute;width:450px;text-align:center;left:50%;margin-left:-225px;">
					<?php
					require_once "framework/Walkntrade.php";
					if(isset($_GET["token"])){
						$key = filter_var($_GET["token"], FILTER_SANITIZE_NUMBER_INT);
						$wt = new Walkntrade();
						switch($wt->verifyKey($key)){
							case "0":
							echo"<p>";
							echo "Your email address has been verified!";
							break;
							case "2":
							echo "Either the link is no longer valid or you mistyped the key.";
							break;
							case "4":
							echo "No key provided.";
							break;
							default:
							echo "Internal Server Error. Please try again later.";
							break;
						}
						echo "</p>";
						echo "<p>";
						echo '<a class="button" style="padding:5px;color:#FFF" href="./">Go Back</a>';
						echo "</p>";
					}
					else{
						echo('
							<form name="code" method="GET" action="validateKey">
							<p>
								<h1> We\'ve sent you an email</h1>
								<p>Check your inbox and click the link provided in order to activate your account.</p>
								<p>You may also enter your verification code here.</p>
								<br>
								<input name="token" placeholder="xxxxxx" maxlength="6" class="codeInput" style="width:6em"></p>
							</p>
							<p>
								<input type="submit" class="button" style="color:#FFF" value="OK">
							</p>
							</form>
							');
					}
				?>
		</div>
	</div>
</div>
<div class="footerBar">
	<?php include("include/footer.html"); ?>
</div>
</body>
</html>
<script type="text/javascript" src="/client_js/jquery.min.js"></script>
<script type="text/javascript" src="/js_minified/min.js"></script>
