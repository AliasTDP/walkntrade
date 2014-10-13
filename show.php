<?php
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$loggedIn = $cs->getLoginStatus();
//get page args
$args=array_keys($_GET);
$args = split(":", $args[0]);
$identifier = htmlspecialchars($args[1]);
$schoolTextId = htmlspecialchars($args[0]);
$myPost = false;

if($cs->getSchoolName($schoolTextId) == null){
	header('Location: ./');
	return;
}

if($identifier != null){
	//get details of the post
	if ($stmt = $cs->getlistingConnection()->prepare("SELECT id, identifier, category, title, details, price, location, username, userid, date, views, price, isbn, author FROM `".$schoolTextId."` WHERE `identifier` = ? LIMIT 1")) { //prepare query
		$stmt->bind_param('s', $identifier);
		$stmt->execute(); // Execute the prepared query.
		$stmt->store_result();
		$stmt->bind_result($id, $identifierFromPost, $category, $title, $details, $price, $location, $uName, $userid, $date, $views, $price, $isbn, $author);
		$stmt->fetch();

		//if post doesn't exist redirect to root
		if($stmt->num_rows < 1){
			header('Location: ./');
			return;
		}
		if($isbn == "0"){
			$showISBN = false;
		}
		else{
			//formating isbn correctly
			$showISBN = true;
			if(strlen($isbn) < 10){
				$diff = 10 - strlen($isbn);
				for($i=0;$i<$diff;$i++){
					$isbn = "0".$isbn;
				}
			}
			else if(strlen($isbn) < 13 && strlen($isbn) > 10){
				$diff = 13 - strlen($isbn);
				for($i=0;$i<$diff;$i++){
					$isbn = "0".$isbn;
				}
			}
			if(strlen($isbn) == 10)
				$isbn = substr($isbn, 0, 1)."-".substr($isbn, 1,3)."-".substr($isbn, 4,5)."-".substr($isbn, 9);
			else if(strlen($isbn) == 13)
				$isbn = substr($isbn, 0, 3)."-".substr($isbn, 3,1)."-".substr($isbn, 4,3)."-".substr($isbn, 7,5)."-".substr($isbn, 12);
		}
		
		//sanitize user generated vars
		$title = strip_tags(htmlspecialchars($title));
		$price = ($price != 0)? "$".round($price, 2) : "";
		$details = str_replace("\n", "<br>", strip_tags(htmlspecialchars($details)));
		$location = strip_tags(htmlspecialchars($location));
		$uName = strip_tags(htmlspecialchars($uName));
		$author = strip_tags(htmlspecialchars($author));
	}

	//update view count
	if(($loggedIn && $_SESSION['username'] != $uName) || !$loggedIn){
		if(!isset($_COOKIE[$identifier])){
			if ($stmt = $cs->getListingConnection()->prepare("UPDATE `".$schoolTextId."` SET `views` = `views` + 1 WHERE `id` = ? LIMIT 1")) { //prepare query
				$stmt->bind_param('i', $id);
				$stmt->execute(); 
				$stmt->close();

				$views++;

			$expire = time() + (60 * 60 *24);# 1 Day
			$path = "/";
			setCookie($identifier, "true", $expire, $path);
		}
	}
}
else{
	$myPost = true;
}

	//get poster details
if ($poster_stmt = $cs->getUserConnection()->prepare("SELECT `phone`, `email` FROM `users` WHERE `id` = ? LIMIT 1")){
	$poster_stmt->bind_param("i", $userid);
	$poster_stmt->execute();
	$poster_stmt->store_result();
	$poster_stmt->bind_result($posterPhone, $posterEmail);
	$poster_stmt->fetch();
	$poster_stmt->close();
}
$posterPhone = ($posterPhone == 0) ? null : $posterPhone;

	//get viewer details
if ($viewer_stmt = $cs->getUserConnection()->prepare("SELECT `phone` FROM `users` WHERE `id` = ? LIMIT 1")){
	$viewer_stmt->bind_param("i", $_SESSION["user_id"]);
	$viewer_stmt->execute();
	$viewer_stmt->store_result();
	$viewer_stmt->bind_result($myPhone);
	$viewer_stmt->fetch();
	$viewer_stmt->close();
}

$myPhone = ($myPhone == 0) ? null : $myPhone;

	//switch statement to set page color
switch ($category) {
	case 'book':
	$color =  "#678909";
	break;

	case 'tech':
	$color = "#370989";
	break;

	case("housing"):
	$color = "#ff8c00"; 
	break;
	case("misc"):
	$color = "#44D3D8";
	break;
	case("asa"):
	$color = "#FF0000";
	break;
	default:
	$color = "#000";
	break;
}

}
if($loggedIn){
	if($myPhone != null)
		$message = "Hi, I would like to find out more about your post, please call me at $myPhone or contact me on Walkntrade. Thanks!";
	else
		$message =  "Hi, I would like to find out more about your post, please contact me on Walkntrade. Thanks!";
	echo('
		');
}
//if page args not valid redirect to root
// else{
// 	header('Location: ./');
// 	return;
// }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php echo $title ?></title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link type="text/css" rel="stylesheet" href="css/show.css">
	<link type="text/css" rel="stylesheet" href="css/login_window.css">
	<link type="text/css" rel="stylesheet" href="/css/feedback_slider.css">
	<link type="text/css" rel="stylesheet" href="/css/spritesheet.css">
	<link rel="shortcut icon" href="http://www.walkntrade.com/favicon.ico?v=2" />
	<link href='https://fonts.googleapis.com/css?family=Gochi+Hand' rel='stylesheet' type='text/css'>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta property="og:title" content="<?php echo $title ?>">
	<meta property="og:description" content="<?php echo $details ?>">
	<script type="text/javascript" src="/client_js/include.js"></script>	
	<script type="text/javascript" src="/client_js/jquery.min.js"></script>
	<script type="text/javascript" src="/client_js/user_login.js"></script>
	<script type="text/javascript" src="/client_js/message_users.js"></script>
	<script type="text/javascript" src="/client_js/feedback_slider.js"></script>	
	<script type="text/javascript">
	messaegUserId = "<?php echo $userid ?>";
	messageTitle = "<?php echo $title ?>";
	messageUserName = "<?php echo $uName ?>";
	messageMessage = "<?php echo $message ?>";
	$(document).ready(function(e){
		$("#imageOne img").click(blowupImage);
		$("#moreImages img").click(blowupImage);
	});
	function blowupImage(e){
		if(e.target.id == "noImg")
			return;
		var imgUrl = $("#"+e.target.id).attr("src");
		$("body").prepend("<div id='screen'><div id=\"imageLargeFloat\"><img src=\""+imgUrl+"\"></div></div>");
		$("#screen").css("display", "none");
		$("#screen").fadeIn();
		$("#screen").click(function(){
			$("#screen").fadeOut(function(){
				$("#screen").remove();
			});
		})
		$("body").keydown(function(e){
			if(e.keyCode==27)
				$("#screen").fadeOut(function(){
					$("#screen").remove();
				});
		});
	}
	</script>
	<div id="fb-root"></div>
	
	<!--FaceBook Social Plugin Root -->
	<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<style type="text/css">
	.colorAttr{color:<?php echo $color ?>;}
	.colorBg{background-color:<?php echo $color ?>;}
	</style>
</head>
<body>
	<div id="throbber"><img src="colorful/loader.gif"></div>
	<div class="headerBar blur"></div>
	<div id="sidebar"><?php include("include/sidebar.php");?></div>
	<div id="pageHead" class="blur"><?php $noLogin=false; include("include/header.php"); ?></div>
	<div class="wrapper">
		<div id="marginWrapper" class="blur">
			<div id="marginLeft" class="boxStyle1">
				<div id="postWrapper">
					<div class="header colorBg"><a href="/schools/<?php echo $schoolTextId ?>"><?php echo $cs->getSchoolName($schoolTextId)?></a> >> <?php echo $category ?></div>
					<div class="body">
						<div id="title"><?php echo $title?></div>
						<div id="details">
							<div id="tag" class="colorAttr">
								<div id="price"><?php echo $price ?></div>
								<div class="info">
									<?php 
									if($category == "book"){
										if($showISBN)echo('ISBN: '.$isbn.'<br>');
										echo('Author: '.$author);
									}
									else if($category == "event"){
										if($location != ""){
											echo("Location: ".$location);
										}
									}
									?>
								</div>
							</div>
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<div class="fb-share-button" data-href="<?php 
								$link =  "//$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
								$escaped_link = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
								echo $escaped_link?>"></div>
							<p><?php echo $details?></p>
						</div>
						<div id="imgBox">
							<div id="imageOne">
								<?php
								if(file_exists("post_images/".$schoolTextId."/".$identifierFromPost."-0.jpeg")){
									echo"<img src=\"post_images/".$schoolTextId."/".$identifierFromPost."-0.jpeg\" id='mainImg'>";
								}
								else{
									echo"<img src=\"colorful/tfe_no_thumb.png\" id='noImg'>";
								}
								?>
							</div>
							<div id="moreImages">
								<?php
								if(file_exists("post_images/".$schoolTextId."/".$identifierFromPost."-0.jpeg")){
									$i = 1;
									while(file_exists("post_images/".$schoolTextId."/".$identifierFromPost."-".$i.".jpeg")){
										echo"<div id=\"image\">";
										echo"<img id=\"imageThumb".$i."\" src=\"post_images/".$schoolTextId."/".$identifierFromPost."-".$i.".jpeg\">";
										echo"</div>";
										$i++;
									}							
								}
								?>
							</div>
						</div>
					</div>
					<div class="contact">
						<div class="cImage ">
							<a href="/user?uid=<?php echo $userid ?>">
							<?php
							if(file_exists("user_images/uid_".$userid.".jpg"))
								echo("<img src='user_images/uid_".$userid.".jpg'>");
							else
								echo "<img src='colorful/Anonymous_User.jpg'>";
							?>
							</a>
						</div>
						<div class="cInfo">
							<i>Posted by: <?php echo $uName ?></i><br>

							<i><?php echo $cs->getAgeInDays($date); ?></i><br>
							<i>Viewed <?php echo $views ?> time(s)</i><br>
						</div>
						<?php
						if($loggedIn){
							if($myPost){
								$string = $schoolTextId.":".$identifier;
								echo('
									<div class="cContact">
									<input type="button" value="Edit Post" onclick="javascript:popup(\'editPost.php?'.$string.'\')" class="button gray">
									</div>
									');
							}
							else{
								echo('
									<div class="cContact">
									<input type="button" value="Contact User" onclick=\'javascript:createMessageWindow(messaegUserId, messageTitle, messageUserName, messageMessage)\' class="button">
									</div>
									');
							}
						}
						else{
							echo('
								<div class="cContact">
								<input type="button" value="Log-in to inquire" onclick="javascript:createLoginWindow()" class="button gray">
								</div>
								');
						}
						?>
					</div>
				</div>
			</div>
			<div id="marginRight">
				<div id="adBoxes">
					<?php
					$i=0;
					while(file_exists("include/sidebar".$i.".html")){
						echo("<div class='blurb boxStyle1'><p>");include("include/sidebar".$i.".html");echo("</p></div>");
						$i++;
					} ?>
				</div>
			</div>
		</div>
	</div>
	<div class="footerBar blur">
		<?php include("include/footer.html"); ?>
	</div>
</body>
</html>
