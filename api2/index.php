<?php
#walkntrade.com API Version 2.0

function genJSON($status, $message, $payload){
	$payload = str_replace("\n", "", $payload);
	$message = str_replace("\n", "", $message);
	$output = "{";
	$output .= "\"status\":\"".$status."\",";
	$output .= "\"message\":\"".$message."\",";
	$output .= "\"payload\":[".$payload."]";
	$output .= "}";
	return $output;
}

function statusDump($status, $message, $payload){
	$response = Array("status"=>$status,"message"=>$message,"payload"=>$payload);
	echo json_encode($response);
	return;
}

if(isset($_POST["intent"]))
	$getIntent = htmlspecialchars($_POST["intent"]);
else
	$getIntent = null;

switch($getIntent){
	case "getPosts":
		require_once "../framework2/Walkntrade.php";
		$wt = new Walkntrade();
		header ("Content-Type:text/xml");
		$query= htmlspecialchars($_POST["query"]);
		$school= htmlspecialchars($_POST["school"]);
		$category= htmlspecialchars($_POST["cat"]);
		$offset= htmlspecialchars($_POST["offset"]);
		$sort= htmlspecialchars($_POST["sort"]);
		$amount= htmlspecialchars($_POST["amount"]);
		$payload=$wt->getPosts($query, $school, $category, $sort, $offset, $amount);
		break;
	case "getPostByIdentifier":
		require_once "../framework2/Walkntrade.php";
		$wt = new Walkntrade();
		$args=array_keys($_POST);
		$args = split(":", $args[1]);
		$identifier = htmlspecialchars($args[1]);
		$school = htmlspecialchars($args[0]);
		$wt->getPostByIdentifier($identifier, $school);
		break;
	case "getSchools":
		require_once "../framework2/Walkntrade.php";
		$wt = new Walkntrade();
		header ("Content-Type:text/xml");
		$query= htmlspecialchars($_POST["query"]);
		$wt->getSchools($query);
		break;
	case "controlPanel":
		require_once "../framework/UserMgmt.php";
		$um = new UserMgmt();
		$oldPw = filter_var($_POST["oldPw"], FILTER_SANITIZE_STRING);
		$email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
		$newPw = filter_var($_POST["newPw"], FILTER_SANITIZE_STRING);
		$phone = filter_var($_POST["phone"], FILTER_SANITIZE_NUMBER_INT);
		switch($um->controlPanel($oldPw, $email, $newPw, $phone)){
			case "1";
			echo "Not authorized";
			break;
			case "2";
			echo "Not authorized";
			break;
			case "3";
			echo "One or more of your settings are redundant and have not been changed. You will be logged out now so your changes to take effect. If you changed your email, you will need to verify it before you may log in.";
			break;
			case "0";
			echo "Your settings have been saved. You will be logged out now in order for your changes to take effect. If you changed your email, you will need to verify it before you may log in.";
			break;
			case "301";
			echo "No Act";
			break;
			case "11";
			echo "This email address exists...";
			break;
			default:
			echo "Internal error. Please report this <a href='/feedback'>here</a>.";
			break;
		}
		break;//Skipped Refactoring to JSON until later
	case "checkPassword":
		require_once "../framework/UserMgmt.php";
		$um = new UserMgmt();
		$password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);
		if($um->checkPassword($password))
			echo genJSON(200, "Password Correct", "");
		else
			echo genJSON(406, "Password Incorrect", "");
		break;
	case "checkUsername":
		require_once "../framework/UserMgmt.php";
		$um = new UserMgmt();
		$username = filter_var($_POST["username"], FILTER_SANITIZE_STRING);
		if($um->checkUsername($username))
			echo genJSON(200, "Username Available", "");
		else
			echo genJSON(406, "Username Taken", "");
		break;
	case "getPostsCurrentUser":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		header ("Content-Type:text/xml");
		$payload=$um->getPostsCurrentUser();
		switch ($payload) {
			case 1:
				echo genJSON(401, "Not authorized", "");
				break;
			
			default:
				echo genJSON(200, "", $payload);
				break;
		}
		break;
	case "verifyEmail":
		require_once "../framework2/Walkntrade.php";
		$wt=new Walkntrade();
		if(isset($_POST["email"])){
			$status=$wt->verifyEmail(htmlspecialchars($_POST["email"]));
			switch ($status) {
				case 0:
					echo genJSON(200, "Ok, We sent it again.", "");
					break;
				case -1:
					echo genJSON(401, "User non-existing or email already verified.", "");
					break;
				default:
					echo genJSON("500", "General server error (".$status.")", "");
					break;
			}
		}
		break;
	case "getUserProfile"://left off here
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$uid = (isset($_POST["uid"])) ? filter_var($_POST["uid"], FILTER_SANITIZE_NUMBER_INT) : null;
		$userName = (isset($_POST["userName"])) ? filter_var($_POST["userName"], FILTER_SANITIZE_STRING) : null;
		header ("Content-Type:text/xml");
		$um->getUserProfile($uid, $userName);
		break;
	case "setEmailPref":
		require_once "../framework/UserMgmt.php";
		$um = new UserMgmt();
		$pref = (isset($_POST["pref"])) ? htmlspecialchars($_POST["pref"], FILTER_SANITIZE_NUMBER_INT) : null;
		echo $um->setEmailPref($pref);
		break;
	case "getEmailPref":
		require_once "../framework/UserMgmt.php";
		$um = new UserMgmt();
		echo $um->getEmailPref();
		break;
	case "login":
		require_once "../framework/UserMgmt.php";
		$um = new UserMgmt();
		if (isset($_POST["email"], $_POST["password"])){
			$email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
			$password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);
			$rememberMe = (isset($_POST["rememberMe"]) && $_POST["rememberMe"]=="true") ? true : false;
			$r = $um->login($email, $password, $rememberMe);
			switch($r){
				case(0):
				echo("success");
				break;
				case(1):
				echo("Username or password incorrect.");
				break;
				case(2):
				echo("verify");
				break;
				case(5):
				echo("Your account has been banned for foul language. We warned you!");
				break;
				case(450):
				echo("reset");
				break;
				default:
				echo("Internal server error. ($r)");
				break;
			}
		}
		break;
	case "logout":
		require_once "../framework/UserMgmt.php";
		$um = new UserMgmt();
		if(isset($_POST["GCMClear"]))
			$GCMClear = ($_POST["GCMClear"] == "true") ? true : false;
		if($GCMClear)
			$um->removeAndroidDeviceId();
		$um->logout();
		break;
	case "getAvatar":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		if(isset($_POST["user_id"]))
			$user_id=$_POST["user_id"];
		else
			$user_id=$_SESSION["user_id"];
		$um->getAvatarOf($user_id, false);
		break;
	case "uploadAvatar":
		if(!isset($_FILES["avatar"])){
			echo "You gotta' choose a new image first!";
			return;
		}
		require_once "../framework/BinaryHandler.php";
		$bh = new BinaryHandler();	
		$bh->uploadAvatar($_FILES['avatar']);
		break;
	case "getUserName":
		require_once "../framework2/CredentialStore.php";
		$cs = new CredentialStore();
		$un =  $cs->getUserName();
		if($un != "")
			echo genJSON(200, $un, "");
		else
			echo genJSON(404, "User does not exist", "");
		break;
	case "addPost":
		require_once "../framework/PostQuery.php";
		$pq = new PostQuery();
		$category = (isset($_POST["cat"])) ? $_POST["cat"] : "";
		$title = (isset($_POST["title"])) ? $_POST["title"] : "";
		$author = (isset($_POST["author"])) ? $_POST["author"] : "";
		$details = (isset($_POST["details"])) ? $_POST["details"] : "";
		$price = (isset($_POST["price"])) ? $_POST["price"] : 0;
		$location = (isset($_POST["location"])) ? $_POST["location"] : "";
		$tags = (isset($_POST["tags"])) ? $_POST["tags"] : "";
		$isbn = (isset($_POST["isbn"])) ? $_POST["isbn"] : 0;
		$school= $pq->cookieCheck("sPref");
			if($pq->getSchoolName($school) == null) ### Prevent from inserting into nonexisting db ###
			return "500: Request malformed";
			$response = $pq->addPost($category, $school, $title, $author, $details, $price, $location, $tags, $isbn);
			switch($response){
				case 1:
				echo "Not authorized";
				break;
				case 2:
				echo "An internal error has occurred. Please try again later ($response)";
				break;
				default:
				echo $response;
				break;
			}
		break;
	case "removePost":
		require_once "../framework/PostQuery.php";
		$pq = new PostQuery();
		$args=array_keys($_POST);
		$args = split(":", $args[1]);
		$identifier = htmlspecialchars($args[1]);
		$school = htmlspecialchars($args[0]);
		switch($pq->removePost($identifier, $school)){
			case(0):
			echo("success");
			break;
			case(1):
			echo("You are not authorized for this operation.");
			break;
			default:
			echo"Internal server error, Please report this error <a href='/feedback'>here</a>";
			break;
		}
		break;
	case "renewPost":
		require_once "../framework/PostQuery.php";
		$pq = new PostQuery();
		$args=array_keys($_POST);
		$args = split(":", $args[1]);
		$identifier = htmlspecialchars($args[1]);
		$school = htmlspecialchars($args[0]);
		switch($pq->renewPost($identifier, $school)){
			case(0):
			echo("success");
			break;
			case(1):
			echo("You are not authorized for this operation.");
			break;
			default:
			echo"Internal server error, Please report this error <a href='/feedback'>here</a>";
			break;
		}
		break;
	case "addAndroidDeviceId":
		require_once "../framework/UserMgmt.php";
		$wt = new UserMgmt();
		$deviceId = $_POST["deviceId"];
		$r = $wt->addAndroidDeviceId($deviceId);
		switch ($r) {
			case 0:
				echo "success";
				break;

			case 1:
				echo "Not authorized";
				break;
			
			default:
				echo "An internal error has occurred.";
				break;
		}
		break;
	case "uploadPostImages":
		require_once "../framework/BinaryHandler.php";
		$bh = new BinaryHandler();
		$binImage = $_FILES['image'];
		$iteration = $_POST['iteration'];
		$identifier  =$_POST["identifier"];
		$school = $bh->cookieCheck("sPref");
		if($bh->getSchoolName($school) == null) ### Prevent from inserting into nonexisting directory ###
		return "501: Request malformed";
		echo $bh->uploadPostImages($binImage, $iteration, $identifier, $school);
		break;
	case "editPost":
		require_once "../framework2/PostQuery.php";
		$pq = new PostQuery();
		$school = filter_var($_POST["school"], FILTER_SANITIZE_STRING);
		if($pq->getSchoolName($school) == null) ### Prevent from inserting into nonexisting db ###
		return $this->statusDump(500, "nonexisting school ID", null);
		$title = $_POST["title"];
		$details = $_POST["details"];
		$price = filter_var($_POST["price"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
		$tags = $_POST["tags"];
		$identifier = $_POST["identifier"];
		$pq->editPost($title, $details, $price, $tags, $identifier, $school);
		break;
	case "addUser":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$username = filter_var(strip_tags($_POST["username"]), FILTER_SANITIZE_STRING);
		$email = filter_var(strip_tags($_POST["email"]), FILTER_SANITIZE_EMAIL);
		$password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);
		$phone = filter_var($_POST["phone"], FILTER_SANITIZE_NUMBER_INT);
		$um->addUser($username, $email, $password, $phone);
		break;
	case "verifyKey":
		require_once "../framework/Walkntrade.php";
		$wt = new Walkntrade();
		$key = filter_var($_POST["key"], FILTER_SANITIZE_NUMBER_INT);
		switch($wt->verifyKey($key)){
			case "0":
			echo "Your email address has been verified!";
			break;
			case "2":
			echo "Either the link is no longer valid or you mistyped the key.";
			break;
			case "4":
			echo "No key provided.";
			break;
			default:
			echo "Internal Server Error. Please report this error <a href='/feedback'>here</a> and we'll get on it.";
			break;
		}
		break;
	case "resetPassword":
		require_once "../framework2/Walkntrade.php";
		$wt = new Walkntrade();
		$wt->resetPassword(filter_var($_POST["email"], FILTER_SANITIZE_EMAIL));
		break;
	case "sendFeedback":
		require_once "../framework2/Walkntrade.php";
		$wt = new Walkntrade();
		if(isset($_POST["message"])){
			$email = (isset($_POST["email"])) ? $_POST["email"] : "";
			$wt->sendFeedback($email, $_POST["message"]);
		}
		break;
	case "getPhoneNum":
		require_once "../framework/CredentialStore.php";
		$cs = new CredentialStore();
		echo $cs->getPhoneNum();
		break;
	case "getCategories":
		require_once "../framework2/Walkntrade.php";
		$wt = new Walkntrade();
		$wt->getCategories();
		break;
	case "createMessageThread":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$message_content=(isset($_POST["message"]))?filter_var($_POST["message"], FILTER_SANITIZE_STRING):null;
		$post_id=(isset($_POST["post_id"]))?filter_var($_POST["post_id"], FILTER_SANITIZE_STRING):null;
		$um->createMessageThread($message_content, $post_id);
		break;
	case "getMessageThreadsCurrentUser":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$offset=(isset($_POST["offset"]))?filter_var($_POST["offset"], FILTER_SANITIZE_NUMBER_INT):0;
		$amount=(isset($_POST["amount"]))?filter_var($_POST["amount"], FILTER_SANITIZE_NUMBER_INT):10;
		$um->getMessageThreadsCurrentUser($offset, $amount);
		break;
	case "retrieveThread":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$thread_id=(isset($_POST["thread_id"]))?filter_var($_POST["thread_id"], FILTER_SANITIZE_STRING):null;
		$limit=(isset($_POST["limit"]))?filter_var($_POST["limit"], FILTER_SANITIZE_NUMBER_INT):100;
		$um->retrieveThread($thread_id, $limit);
		break;
	case "retrieveThreadNew":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$thread_id=(isset($_POST["thread_id"]))?filter_var($_POST["thread_id"], FILTER_SANITIZE_STRING):null;
		$override=(isset($_POST["override"]))?filter_var($_POST["override"], FILTER_SANITIZE_NUMBER_INT):0;
		$um->retrieveThreadNew($thread_id, $override);
		break;
	case "appendMessage":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$thread_id=(isset($_POST["thread_id"]))?filter_var($_POST["thread_id"], FILTER_SANITIZE_STRING):null;
		$message_content=(isset($_POST["message"]))?htmlentities($_POST["message"]):null;
		$um->appendMessage($thread_id, $message_content, true, true);
		break;
	case "deleteThread":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$thread_id=(isset($_POST["thread_id"]))?filter_var($_POST["thread_id"], FILTER_SANITIZE_STRING):null;
		$um->deleteThread($thread_id);
		break;
	case "hasNewMessages":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$um->hasNewMessages();
		break;
	case "markThreadAsRead":
		require_once "../framework2/UserMgmt.php";
		$um = new UserMgmt();
		$thread_id=(isset($_POST["thread_id"]))?filter_var($_POST["thread_id"], FILTER_SANITIZE_STRING):null;
		$um->markThreadAsRead($thread_id);
		break;
	default:
		echo "Hi there!";
	break;
}
?>