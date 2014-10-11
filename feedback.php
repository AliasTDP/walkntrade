<?php
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$loggedIn = $cs->getLoginStatus();
?>
<!DOCTYPE html>
<html>
<head>
	<title>walkNtrade.com | Feedback</title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link type="text/css" rel="stylesheet" href="css/feedback_slider.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link type="text/css" rel="stylesheet" href="css/login_window.css">
	<style type="text/css">
		form{
			width: 100%;
			margin: auto;
		}
		input[type="text"]{
			width: 80%;
			margin: auto;
		}

		textarea{
			width:80%;
			height: 300px;
			padding: 4px;
			margin: auto;
		}

	</style>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="description" content="Feedback" >
	<meta name="robots" content="NOINDEX, NOFOLLOW" />
	<meta http-equiv="Content-Language" content="en">
	<script type="text/javascript" src="/client_js/include.js"></script>	
	<script type="text/javascript" src="/client_js/jquery.min.js"></script>
	<script type="text/javascript" src="/client_js/user_login.js"></script>
	<script type="text/javascript">
	function submitFeedback(){
		var email = document.feedbackForm.email.value;
		var message = document.feedbackForm.message.value;

		if(email != "" && !validateEmail(email)){
			dialog("Please use a valid email address", null, true);
			return;
		}

		if(message != ""){
			q("POST", "/api/", "intent=sendFeedback&email="+email+"&message="+message, function(r){
				$("#feedbackWrapper").animate({right: 0});
				dialog(r, function(s){
				}, true);
			})
		}
	}
	</script>
	<script type="text/javascript">
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
		<div id="pageHead"><?php $noLogin=true; include("include/header.php"); ?></div>
		<div id="sidebar"><?php include("include/sidebar.php");?></div>
		<div class="wrapper">
			<div class="wF">
				<div class="boxStyle1 justifyCenter75">
					<style type="text/css">span{margin: 0em 1em 0em 0em;}</style>
					<span style="font-size:1.15em;color:#C0C0C0"><a href="ToS">Terms of Service</a></span> <span style="font-size:1.15em;color:#C0C0C0"><a href="privacy">Privacy Policy</a></span><span style="font-size:1.5em"><a href="feedback">Feedback</a></span>
					<hr>
					<h1>Leave us some feedback would you please :)</h1>
					<img src="http://s133702574.onlinehome.us/pictures/blog/puppyface.jpg" height="200px">
					<p>We'll put on our best puppy dog face in hope that you will tell us what you think about the site. It could be about anything really... whether you like the colors of the logo, or you found a random bug, please, let us know about it. Heck, even if you just wana say hi we want to hear it!</p>
					<form name='feedbackForm' action='javascript:submitFeedback()'>
						<p><input name='email' type='text' placeholder='email address (optional)'></p>
						<p><textarea name='message' placeholder='your message here' resize='none'></textarea></p>
						<p><input type='submit' value='Send' class='button'></p>
					</form>
				</div>
			</div>
		</div>
		<div class="footerBar">
			<?php include("include/footer.html"); ?>
		</div>
</body>
</html>
