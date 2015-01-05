<?php
require_once "framework2/CredentialStore.php";
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
	<title>Walkntrade</title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link type="text/css" rel="stylesheet" href="css/login_window.css">
	<link type="text/css" rel="stylesheet" href="css/addlisting.css">
	<link rel="shortcut icon" href="http://www.walkntrade.com/favicon.ico?v=2" />
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
<body onload="javascript:initDropBox()">
	<div id="throbber"><img src="colorful/loader.gif"></div>
	<div class="headerBar"></div>
	<div id="pageHead"><?php $noLogin=false; include("include/header.php"); ?></div>
	<div id="sidebar"><?php include("include/sidebar.php");?></div>
	<div class="wrapper">
		<div id="addTable" class="boxStyle1">
			<p><h1 style="text-align:center">Post a textbook advertisement.</h1></p>
		<table style="width:100%" cellpadding="15">
			<form action="javascript:void(0)" method="POST" name="books" onSubmit="addBook()">
				<tr>
					<td width="50%">
						<input type="text" name="Title" placeholder="*Title of the book">
					</td>
					<td width="50%">
						<input type="text" name="Author" placeholder="*Author of the book">
					</td>
				</tr>
				<tr class="errorClass">
					<td  id="errTitle"></td>
					<td  id="errAuth"></td>
				</tr>
				<tr>
					<td colspan="2">
						<textarea name="Details" placeholder="*A short description of the book here"></textarea>
					</td>
				</tr>
				<tr class="errorClass">
					<td id="errDescription" colspan="4"></td>
				</tr>
				<tr>
					<td colspan="2">
						<div id="imgDrop"><h3 style="color:#A0A0A0">Drag and drop images here or select them below (limit 4 images each less than 5MB)</h3></div>
						<input type="file" accept="image/jpeg" multiple="multiple" onchange="getImages(this)"></input>
						<br>
						<br>
					</td>
				</tr>
				<tr>
					<td>
						<input type="text" name="Price" maxlength="7" onKeyUp="javascript:formatPrice(this)" placeholder="Price (optional)">
					</td>
					<td>
						<input type="text" name="ISBN" placeholder="ISBN-10 or ISBN-13 number here (optional)">				
					</td>
				</tr>
				<tr class="errorClass">
					<td id="errPrice"></td>
					<td id="errIsbn"></td>
				</tr>
				<tr>
					<td colspan="2">
						<input type="text" name="Tags" placeholder="*Descriptive tags about your book">
					</td>
				</tr>
				<tr class="errorClass">
					<td id="errTags" colspan="2"></td>
				</tr>
				<tr>
					<th colspan="4">
						<input type="submit" value="Post!">
					</th>
				</tr>
			</form>
		</table>
		</div>
	</div>
	<div class="footerBar">
		<?php include("include/footer.html"); ?>
	</div>
</body>
</html>
<script type="text/javascript" src="/script/jquery.min.js"></script>
<script type="text/javascript" src="/script/walkntrade.js"></script>	
<script type="text/javascript">initAddPost();</script>
