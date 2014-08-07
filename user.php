<?php
require_once "framework/UserMgmt.php";
$um = new UserMgmt();
$uid = (isset($_GET["uid"])) ? filter_var($_GET["uid"], FILTER_SANITIZE_NUMBER_INT) : null;
if($uid == null) header('Location: ./');
$loggedIn = $um->getLoginStatus();

function getAvatarOf($userid){
	if(file_exists("user_images/uid_".$userid.".jpg"))
		return("/user_images/uid_".$userid.".jpg");
	else
		return("/colorful/Anonymous_User.jpg");
}

$userName = $um->resolveIDToUsername($uid);
if($userName == null) header('Location: ./');

$avatarUrl = getAvatarOf($uid);
?>
<!DOCTYPE html>
<html>
<head>
	<title>"<?php echo $userName ?>" at walkNtrade</title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link type="text/css" rel="stylesheet" href="css/feedback_slider.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="description" content="User Profile overview" >
	<meta http-equiv="Content-Language" content="en">
	<script type="text/javascript" src="/client_js/include.js"></script>	
	<script type="text/javascript" src="/client_js/jquery.min.js"></script>
	<script type="text/javascript" src="/client_js/user_login.js"></script>
	<script type="text/javascript">
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

		ga('create', 'UA-42896980-1', 'walkntrade.com');
		ga('send', 'pageview');
	</script>
	<style type="text/css">

	.boxStyle1{
		width: 60%;
		margin: 30px auto;
	}
	.wF table{
		width: 75%;
		margin: auto;
	}
	.wF h1{
		text-align: center;
	}
	.wF th[colspan="2"]{
		padding: 20px inherit;
		text-align: left;
		font-size: 1.3em;
		border-bottom: 1px solid #C0C0C0;
	}
	.wF th{
		text-align: left;
		padding: 5px inherit;
	}
	.wF .right{
		text-align: right;
	}
	#avatarBox{
		width: 100px;
		height: 100px;
		margin: 10px auto;
		padding: 5px;
	}	
	#avatarBox img{
		width: 100px;
		height: 100px;
	}	
	.right{
		text-align: right;
	}
	</style>
</head>
<body>
	<div id="throbber"><img src="colorful/loader.gif"></div>
	<div style="position:absolute;left:-3px;top:0px;z-index:50;width:78px;height:75px;background:url('http://cdn.choopia.com/images/beta-ribbon.png') no-repeat"></div>
	<div class="headerBar"></div>
	<div class="wrapper">
		<div id="pageHead">
			<?php $noLogin=true; include("include/header.php"); ?>
		</div>
		<div class="wF">
			<div class="boxStyle1">
				<p><h1><?php echo $userName ?>'s posts:</h1></p>
				<p><div class="boxStyle1" id="avatarBox"><img src="<?php echo $avatarUrl ?>"/></div></p>
				<p>
				<table>
				<?php 
					$schs = $um->getListingConnection()->prepare("SELECT `textId` FROM `schools` ORDER BY `name` DESC LIMIT 100");
					$schs->execute();
					$schs->store_result();
					$schs->bind_result($school);
					if($userName != null){
						$count = 0;
						while($schs->fetch()){
							$mypost = $um->getListingConnection()->prepare("SELECT `identifier`, `category`, `title`,  `date` FROM `".$school."` WHERE `userid` = ? ORDER BY `id` DESC");
							$mypost->bind_param("s", $uid);
							$mypost->execute();
							$mypost->store_result();
							$mypost->bind_result($identifier, $pCat, $pTitle, $pDate);
							if($mypost->num_rows > 0){
								$concatenated = "<tr><th colspan='2'>At ".$um->getSchoolName($school)."</th></tr><tr><th>Title</th><th class=\"right\">Posted</th></tr>";

								while($mypost->fetch()){
									$link =  $school.":".$identifier;
									$pTitle =(strlen($pTitle) > 55) ? substr($pTitle, 0, 55)."..." : $pTitle;
									$html_blacklist = "/< >/";
									$pTitle = htmlspecialchars($pTitle);
									$pDate = $um->getAgeInDays($pDate)." day(s) ago";
									$pCat = htmlspecialchars($pCat);
									$concatenated .= "<tr><td><a href='/show?".$link."'>".$pTitle."</a></td><td class='right'>".$pDate."</td>";
									$count++;
								}
							}
							$mypost->close();
						}
						if($count != 0){
							$concatenated = $concatenated."</userProfile>";
							echo $concatenated;
						}
						else
							echo "Nothing here yet!";
					}
					?>
					</table>
					</p>
				</div>
			</div>
		</div>
		<div class="footerBar">
			<?php include("include/footer.html"); ?>
		</div>
	</body>
	</html>
