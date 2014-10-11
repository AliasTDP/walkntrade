<?php
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$loggedIn = $cs->getLoginStatus();
?>
<!DOCTYPE html>
<html>
<head>
	<title>walkNtrade.com | Find what you need... Loose what you don't!</title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<meta name="description" content="Sign up to walkNtrade." >
	<meta name="robots" content="Index, Follow">
	<meta http-equiv="Content-Language" content="en">
	<script type="text/javascript" src="/client_js/include.js"></script>	
	<script type="text/javascript" src="/client_js/jquery.min.js"></script>
	<script type="text/javascript" src="/client_js/user_add.js"></script>
	<script type="text/javascript" src="/client_js/user_login.js"></script>
	<script>
	(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	ga('create', 'UA-42896980-1', 'walkntrade.com');
	ga('send', 'pageview');

	</script>
	<style type="text/css">
		.wrapper{
			margin-left: 0px;
		}
		#pageHead #logo {
			position: relative;
			left: -5px;
			top: 8px;
		}
	</style>
</head>
<body>
	<div id="throbber"><img src="colorful/loader.gif"></div>
		<div class="headerBar"></div>
		<div class="wrapper">
			<div id="pageHead"><?php $noLogin=true; include("include/header.php"); ?></div>
			<div class="wF">
			<div class="boxStyle1 float">
				<form action="javascript:void(0)" onSubmit="user_add()" name="useradd" autocomplete="off">
				<table class="pad">
						<tr>
							<th colspan="2">
								<h1 class="green"><p>Register here for free.</p></h1><hr>
							</th>
						</tr>
						<tr>
							<td colspan="2">
							</td>
						</tr>
						<td width="40%">
							*Username:
						</td>
						<td width="60%">
							<input type="text"  autocomplete="off"  name="usernameSignup" onblur="checkUname(this.value)">
						</td>
					</tr>
					<tr class="errorClass">
						<td id="1Err" colspan="2">
						</td>
					</tr>
					<tr>
						<td>
							*Email:
						</td>
						<td>
							<input type="text"   autocomplete="off" name="emailSignup">
						</td>
					</tr>
					<tr class="errorClass">
						<td id="2Err" colspan="2">
						</td>
					</tr>
					<tr>
						<td>
							Phone Number:
						</td>
						<td>
							<input type="text"  autocomplete="off"  name="phoneSignup">
						</td>
					</tr>
					<tr class="errorClass">
						<td id="phoneErr" colspan="2">
						</td>
					</tr>
					<tr>
						<td>
							*Password:
						</td>
						<td>
							<input type="password"  name="passwordSignup">
						</td>
					</tr>
					<tr class="errorClass">
						<td id="3Err" colspan="2">
						</td>
					</tr>
					<tr>
						<td>
							*Confirm:
						</td>
						<td>
							<input type="password"  name="password2Signup">
						</td>
					</tr>
					<tr class="errorClass">
						<td id="4Err" colspan="2">
						</td>
					</tr>
					<tr>
						<th colspan="2">
							<input type="submit" value="Submit" style="width:50%">
						</th>
					</tr>
			</table>
			</form>
			<p class="italic">By completing this form, you agree to comply to our <a href="ToS"><b>Terms of Service.</b></a></p>
		</div>
		<div class="boxStyle1 float">
			<form action="javascript:void(0)" onSubmit="user_login()" name="login">
				<table class="pad" style="text-align:left;">
					<tr>
						<th id="err" colspan="2">
							<h1 class="green"><p>Already a member? Log-In here.</p><hr></h1>
						</th>
					</tr>
					<tr>
						<td width="40%">
							Email:
						</td>
						<td width="60%">
							<input type="text" name="username" onClick="if (this.value == 'email') {this.value='';}">
						</td>
					</tr>
					<tr>
						<td>
							Password:
						</td>
						<td>
							<input  type="password" name="password" onClick="this.value=''">
						</td>
					</tr>
					<tr>
						<td>
							<input type="checkbox" name="rememberMe" value="true" checked="true"> Remember me
						</td>
						<td>
							<a href="javascript:resetPassword()">Forgot password</a>
						</td>
					</tr>
					<tr class="errorClass">
						<td id="response" colspan="2">
						</td>
					</tr>
					<tr style="text-align:center;">
						<th colspan="2">
							<input class="LButton" type="submit" value="login" style="width:50%">
						</th>
					</tr>
				</table>
			</form>
			<div class="pad">
				<h1 class="green"><p>We're in Beta</p></h1>
				<hr>
				<p class="italic">This means that not everything you see here will be 100%, which is why we need you. You're feedback is the most important thing to us, so send us a message at <b>feedback@walkntrade.com</b> and let us know what you think.</p>
			</div>
		</div>
	</div>
</div>
<div class="footerBar">
		<?php include("include/footer.html"); ?>
</div>
</body>
</html>
