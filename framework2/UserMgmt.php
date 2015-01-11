<?php
require_once "CredentialStore.php";
class UserMgmt extends CredentialStore{

	public function __construct(){
		parent::__construct();
	}

	public function login($email, $password, $persist){
		rtrim($email);
		$uc = $this->getUserConnection();
		if ($stmt = $uc->prepare("SELECT id, name, password, email, verified, infractions, emailPref, phone FROM users WHERE email = ? LIMIT 1")) { 
			$stmt->bind_param('s', $email); // Bind "$email" to parameter.
			$stmt->execute(); // Execute the prepared query.
			$stmt->store_result();
			$stmt->bind_result($user_id, $uname, $db_password, $emailAddress, $verified, $infractions, $emailPref, $phoneNum); // get variables from result.
			$stmt->fetch();
			$password = md5($password); // hash the password
			if($stmt->num_rows == 1) { // If the user exists
				if($db_password == $password) { // Check if the password in the database matches the password the user submitted. 
					// Password is correct!
					if($verified == 1){
						if($infractions < 5){
							$user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
							$_SESSION['user_id'] = $user_id;
							$username = preg_replace("/[^a-zA-Z0-9_\-.]+/", "", $uname); // XSS protection as we might print this value
							$_SESSION['phone'] = $phoneNum;
							$_SESSION['username'] = $uname;
							$_SESSION['emailAddress'] = $emailAddress;
							$_SESSION['login_string'] = md5($password.$user_browser);
							$_SESSION['email_me'] = ($emailPref == 1) ? true : false;
			        // Login successful.

							if($log_user_stmt = $uc->prepare("UPDATE `users` SET last_visited = ? WHERE id = ? LIMIT 1")){
								$date = date('Y/m/d H:i:s');
								$log_user_stmt->bind_param("si", $date, $user_id);
								$log_user_stmt->execute();
								$log_user_stmt->close();
							}
							else{
								return 5000;
							}

							if($persist)
								$this->generateLoginCookie($user_id, $_SESSION['login_string']);
							$stmt->close();
							return "0";
						}
						else{
							//user banned
							return 5;
						}
					}
					else{
						//acct not verified
						$stmt->close();
						return 2;
					}
				}
				else {
					// Password is not correct
					$stmt->close();
					if(!isset($_SESSION["login_attempts"]))
						$_SESSION["login_attempts"] = 0;
					else if($_SESSION["login_attempts"] > 1){
						$_SESSION["login_attempts"] = 0;
						return 450;
					}
					$_SESSION["login_attempts"]++;
					return 1; 
				}
			}
			else {
				// No user exists. 
				$stmt->close();
				if(!isset($_SESSION["login_attempts"]))
					$_SESSION["login_attempts"] = 0;
				else if($_SESSION["login_attempts"] > 1){
					$_SESSION["login_attempts"] = 0;
					return 450;
				}
				$_SESSION["login_attempts"]++;
				return 1; 
			}
			$stmt->close();
		}
	}

	private function generateLoginCookie($sessionUid, $sessionSeed){
		$expire = time() + (60 * 60 *24 * 30 *24);# 2Years
		$path = "/";
		setCookie("sessionUid", $sessionUid, $expire, $path);
		setCookie("sessionSeed", $sessionSeed, $expire, $path);
	}

	public function logout(){
		$_SESSION = array();
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		$path = "/";
		setCookie("sessionUid", "", time() - 42000, $path);
		setCookie("sessionSeed", "", time() - 42000, $path);
		session_destroy();
	}

	public function addUser($username, $email, $password, $phone){
		$username = preg_replace("/[^a-zA-Z0-9_\-.]+/", "", $username);
		$password = md5($password);
		if (strlen($username) < 5 || strlen($username) > 20){
			return $this->statusDump(401, "Username must be between 5 and 20 characters long", null);
		}
		else{
			if (!$this->checkUname($username)){
				return $this->statusDump(401, "The username has been taken", null);
			}
		}
		if (strlen($email) == ""){
			return $this->statusDump(401, "You must provide an email", null);
		}
		else{
			$tld = substr($email, -3);
			if($tld != "edu"){
				return $this->statusDump(401, "We only allow .edu emails at this time", null);
			}
			if (!$this->checkEmail($email)){
				return $this->statusDump(401, "Please provide a valid email", null);
			}
		}
		if (strlen($phone) != 10){
			$phone = 0;
		}
		if (strlen($password) < 8){
			return $this->statusDump(401, "Password must be at least 8 characters long", null);
		}
		//----------end verification-------------------------------------------------------------------------------------------------------------------
		if($insert_stmt = $this->getUserConnection()->prepare("INSERT INTO `users` (name, email, password, phone, seed) VALUES (?, ?, ?, ?, ?)")){
			$seed = rand(1000000,9999999);
			$insert_stmt->bind_param('ssssi', $username, $email, $password, $phone, $seed); 
			$insert_stmt->execute();
			if($insert_stmt->affected_rows == 1){
				$this->verifyEmail($email);
				$insert_stmt->close();
				//success
				if($log_user_stmt = $this->getUserConnection()->prepare("UPDATE `users` SET date_registered = ? WHERE email = ? LIMIT 1")){
					$date = date('Y/m/d H:i:s');
					$log_user_stmt->bind_param("ss", $date, $email);
					$log_user_stmt->execute();
					$log_user_stmt->close();
				}
				else
					return $this->statusDump(500, "Unable to set registration date", null);
				if($getUIDSTMT = $this->getUserConnection()->prepare("SELECT `id` FROM `users` WHERE email = ? LIMIT 1")){
					$getUIDSTMT->bind_param("s", $email);
					$getUIDSTMT->execute();
					$getUIDSTMT->store_result();
					$getUIDSTMT->bind_result($uuid);
					$getUIDSTMT->fetch();
					$getUIDSTMT->close();
				}
				else
					return $this->statusDump(500, "Unable to get userID", null);
				if($createInboxSTMT = $this->getThread_indexConnection()->prepare("
						CREATE TABLE `wtonline_thread_index`.`$uuid` (
					  `thread_id` VARCHAR(20) NOT NULL,
					  `last_message` VARCHAR(100) NOT NULL,
					  `last_user_id` INT(50) NOT NULL,
					  `post_id` VARCHAR(45) NOT NULL,
					  `post_title` VARCHAR(100) NULL,
					  `datetime` DATETIME NOT NULL,
					  `new_messages` INT(2) NOT NULL,
					  `associated_with` INT(50) NOT NULL,
					  `locked` BIT NOT NULL,
					  `hidden` BIT NOT NULL,
					  PRIMARY KEY (`thread_id`),
					  UNIQUE INDEX `thread_id_UNIQUE` (`thread_id` ASC));
					")){
					$createInboxSTMT->execute();
					$createInboxSTMT->close();
				}
				return $this->statusDump(200, "Success", null);
			}
			else{
				return $this->statusDump(500, "Table not updated", null);
			}
		}
		else
			return $this->statusDump(500, "Unable to prepare connection", null);
	}

	public function controlPanel($oldPw, $email, $newPw, $phone){
		$uc = $this->getUserConnection();
		if($this->getLoginStatus()){
			if($this->checkPassword($oldPw)){
				$errors = false;
				if($email != ""){
					if($this->checkEmail($email)){
						if($emailUpdate = $uc->prepare("UPDATE `users` SET `email` = ?,`verified` = 0 WHERE `id` = ? LIMIT 1")){
							$emailUpdate->bind_param("ss", $email, $_SESSION["user_id"]);
							$emailUpdate ->execute();
							if($emailUpdate->affected_rows ==1){
								if($this->verifyEmail($email) == 0){
										//success
									$emailUpdate->close();
								}
								else{
										//unable to send email
									$emailUpdate->close();
									return 14;
								}
							}
							else{
									//unable to update table
								return 13;
							}
						}
						else{
								//SQL error
							return 12;
						}
					}
					else{
							//email address exixts
						return 11;
					}
				}
				if($newPw != ""){
					$newPw = md5($newPw);
					$stmt = $uc->prepare("UPDATE `users` SET `password` = ?  WHERE `id` = ? LIMIT 1");
					$stmt->bind_param("si", $newPw, $_SESSION["user_id"]);
					$stmt->execute();
					if($stmt->affected_rows != 1){
						$errors = true;
					}
				}
				if($phone != ""){
					$stmt = $uc->prepare("UPDATE `users` SET `phone` = ?  WHERE `id` = ? LIMIT 1");
					$stmt->bind_param("si", $phone, $_SESSION["user_id"]);
					$stmt->execute();
					if($stmt->affected_rows != 1){
						$errors = true;
					}
				}
				if(!$errors){
					if($email == "" && $newPw == "" && $phone =="")
							//No Act
						return 301;
						//All Gud
					return 0;
				}
				else
						//Err detect
					return 3;
			}
			else
					//No Auth
				return 2;
		}
		else
				//NLI
			return 1;
	}

	public function getPostsCurrentUser(){
		$lc = $this->getlistingConnection();
		if($this->getLoginStatus()){
			$schs = $lc->prepare("SELECT `textId` FROM `schools` ORDER BY `name` DESC LIMIT 100");
			$schs->execute();
			$schs->store_result();
			$schs->bind_result($school);
			$concatenated = '';
			$i = 0;
			while($schs->fetch()){
				$mypost = $lc->prepare("SELECT `id`, `identifier`, `category`, `title`,  `date`, `views`, `expire`, `expired` FROM `".$school."` WHERE `userid` = ? ORDER BY `date` DESC");
				$mypost->bind_param("s", $_SESSION["user_id"]);
				$mypost->execute();
				$mypost->store_result();
				$mypost->bind_result($pId, $identifier, $pCat, $pTitle, $pDate, $pViews, $pExpire, $pExpired);
				if($mypost->num_rows > 0){
					$postConcat="";
					$j = 0;
					while($mypost->fetch()){
						$link =  $school.":".$identifier;
						$pTitle =(strlen($pTitle) > 55) ? substr($pTitle, 0, 55)."..." : $pTitle;
						$html_blacklist = "/< >/";
						if($pExpired == true) $pTitle = "[EXPIRED] ".htmlspecialchars($pTitle);
						elseif($pExpire != -1)  $pTitle = "[".$pExpire." DAY(S) LEFT] ".htmlspecialchars($pTitle);
						else $pTitle = htmlspecialchars($pTitle);

						$pDate = $this->getAgeInDays($pDate);

						$pCat = htmlspecialchars($pCat);
						$pExpired = ($pExpired == 1) ? "true" : "false";
						if($j>0) $postConcat.=",";
						$postConcat.='{"id":"'.$pId.'","link":"'.$link.'","category":"'.$pCat.'","title":"'.$pTitle.'","date":"'.$pDate.'","views":"'.$pViews.'","expire":"'.$pExpire.'","expired":"'.$pExpired.'"}';
						$j++;
					}
					if($i>0) $concatenated.=",";
					$concatenated.='{"shortName":"'.$school.'","longName":"'.$this->getSchoolName($school).'","post":['.$postConcat.']}';
					$i++;
				}
				$mypost->close();
			}
			return $concatenated;
		}
		else{
				//NLI
			return 1;
		}
	}

	public function setEmailPref($intent){
		if($this->getLoginStatus()){
			if($setEmailPrefSTMT = $this->getUserConnection()->prepare("UPDATE users SET emailPref = ? WHERE id = ? LIMIT 1")){
				$setEmailPrefSTMT->bind_param("ii", $intent, $_SESSION["user_id"]);
				$setEmailPrefSTMT->execute();
				$_SESSION["email_me"] = $intent;
				return 0;
			}
			else return 10;
		}
		else return 1;
	}

	public function getEmailPref(){
		echo $_SESSION["email_me"];
	}

	public function checkPassword($password){
		$password = md5($password);
		if ($passwordck = $this->getUserConnection()->prepare("SELECT `id` FROM `users` WHERE `password` = ? AND `name` = ?")) {    
			$passwordck->bind_param('ss', $password, $_SESSION["username"]); 

			if($passwordck->execute()){
				$passwordck->store_result();
				if(($passwordck->num_rows) != 1){
					$passwordck->close();
					return false;
				}
				else{
					$passwordck->bind_result($id);
					$passwordck->fetch();

					if ($id == $_SESSION["user_id"]){
						$passwordck->close();
						return true;
					}
				}
			}
		}
	}

	public function checkUsername($username){
		if ($unameck = $this->getUserConnection()->prepare("SELECT `id` FROM `users` WHERE `name` = ?")) {    
			$unameck->bind_param('s', $username); 

			if($unameck->execute()){
				$unameck->store_result();
				if(($unameck->num_rows) > 0){
					$unameck->close();
					return false;
				}
				else{
					$unameck->close();
					return true;
				}
			}
		}
	}

	private function GCMPush($uid, $androidDeviceId, $remoteMessageId, $title, $message, $dateTime){
		$registrationIDs = array($androidDeviceId);
		$apiKey = "AIzaSyCOlxC1pWV-MAVDyGE_NcdKfk1hCVJ7ZcQ";
		$imgUrl = "user_images/uid_".$_SESSION["user_id"].".jpg";

		$post_string["id"]=$remoteMessageId;
		$post_string["user"]=$_SESSION["username"];
		$post_string["subject"]=$title;
		$post_string["message"]=$message;
		$post_string["date"]=$dateTime;
		$post_string["userImageURL"]= (file_exists("../".$imgUrl)) ? $imgUrl : "colorful/Anonymous_User.jpg";

		    // Set POST variables
		$url = 'https://android.googleapis.com/gcm/send';

		$fields = array(
			'registration_ids' => $registrationIDs,
			'data' => $post_string,
			);
		$headers = array(
			'Authorization: key=' . $apiKey,
			'Content-Type: application/json'
			);

		    // Open connection
		$ch = curl_init();

		    // Set the URL, number of POST vars, POST data
		curl_setopt( $ch, CURLOPT_URL, $url);
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
		    //curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields));

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		    // curl_setopt($ch, CURLOPT_POST, true);
		    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode( $fields));

		    // Execute post
		$result = curl_exec($ch);

		    // Close connection
		curl_close($ch);
			// echo $result;
		    //print_r($result);
		    //var_dump($result);
	}

	public function resolveUsernameToID($userName){
		if ($resolveUsernameToIDSTMT = $this->getUserConnection()->prepare("SELECT `id` FROM `users` WHERE `name` = ?")) {    
			$resolveUsernameToIDSTMT->bind_param('s', $userName); 
			$resolveUsernameToIDSTMT->execute();
			$resolveUsernameToIDSTMT->store_result();
			$resolveUsernameToIDSTMT->bind_result($id);
			$resolveUsernameToIDSTMT->fetch();
		}
		$id = ($id == "") ? null : $id;
		return $id;
	}

	public function resolveIDToUsername($id){
		if ($resolveUsernameToIDSTMT = $this->getUserConnection()->prepare("SELECT `name` FROM `users` WHERE `id` = ?")) {    
			$resolveUsernameToIDSTMT->bind_param('i', $id); 
			$resolveUsernameToIDSTMT->execute();
			$resolveUsernameToIDSTMT->store_result();
			$resolveUsernameToIDSTMT->bind_result($id);
			$resolveUsernameToIDSTMT->fetch();
		}
		$id = ($id == "") ? null : $id;
		return $id;
	}

	public function getAvatarOf($userid, $plainText){
		if(!$plainText){
			if(file_exists("../user_images/uid_".$userid.".jpg"))
				$this->statusDump(200, "/user_images/uid_".$userid.".jpg", null);
			else
				$this->statusDump(200, "/colorful/Anonymous_User.jpg", null);
		}
		else{
			if(file_exists("../user_images/uid_".$userid.".jpg"))
				return "/user_images/uid_".$userid.".jpg";
			else
				return "/colorful/Anonymous_User.jpg";
		}
	}

	public function getUserProfile($uid, $userName){
			if($uid != ""){//get by user ID
				$userName = $this->resolveIDToUsername($uid);
				if ($userName==null)
					return $this->statusDump(404, "No matching UID", null);

				$schs = $this->getListingConnection()->prepare("SELECT `textId` FROM `schools` ORDER BY `name` DESC LIMIT 100");
				$schs->execute();
				$schs->store_result();
				$schs->bind_result($school);
				$postArray = Array();
				$avatarUrl = $this->getAvatarOf($uid, true);
				while($schs->fetch()){//for schools
					$mypost = $this->getListingConnection()->prepare("SELECT `id`, `identifier`, `category`, `title`,  `date`, `views` FROM `".$school."` WHERE `userid` = ? ORDER BY `id` DESC");
					$mypost->bind_param("s", $uid);
					$mypost->execute();
					$mypost->store_result();
					$mypost->bind_result($pId, $identifier, $pCat, $pTitle, $pDate, $pViews);
					if($mypost->num_rows > 0){
						while($mypost->fetch()){//for posts
							$link =  $school.":".$identifier;
							$pTitle =(strlen($pTitle) > 55) ? substr($pTitle, 0, 55)."..." : $pTitle;
							$html_blacklist = "/< >/";
							$pTitle = htmlspecialchars($pTitle);
							$pDate = htmlspecialchars($pDate);
							$pCat = htmlspecialchars($pCat);
							$line = Array("post_identifier"=>$link,"title"=>$pTitle,"date"=>$pDate,"category"=>$pCat,"schoolLongName"=>$this->getSchoolName($school),"schoolShortName"=>$school);
							array_push($postArray, $line);
						}
					}
					$mypost->close();
				}
			return $this->statusDump(200, "returned userdata by UID", Array("username"=>$userName,"avatarUrl"=>$avatarUrl,"posts"=>$postArray));
			}
			else if($userName != ""){//get by userName
				$uid = $this->resolveUsernameToID($userName);
				if($uid == null)
					return $this->statusDump(404, "No matching USERNAME", null);

				$schs = $this->getListingConnection()->prepare("SELECT `textId` FROM `schools` ORDER BY `name` DESC LIMIT 100");
				$schs->execute();
				$schs->store_result();
				$schs->bind_result($school);
				$postArray = Array();
				$avatarUrl = $this->getAvatarOf($uid, true);
				while($schs->fetch()){//for schools
					$mypost = $this->getListingConnection()->prepare("SELECT `id`, `identifier`, `category`, `title`,  `date`, `views` FROM `".$school."` WHERE `userid` = ? ORDER BY `id` DESC");
					$mypost->bind_param("s", $uid);
					$mypost->execute();
					$mypost->store_result();
					$mypost->bind_result($pId, $identifier, $pCat, $pTitle, $pDate, $pViews);
					if($mypost->num_rows > 0){
						while($mypost->fetch()){//for posts
							$link =  $school.":".$identifier;
							$pTitle =(strlen($pTitle) > 55) ? substr($pTitle, 0, 55)."..." : $pTitle;
							$html_blacklist = "/< >/";
							$pTitle = htmlspecialchars($pTitle);
							$pDate = htmlspecialchars($pDate);
							$pCat = htmlspecialchars($pCat);
							$line = Array("post_identifier"=>$link,"title"=>$pTitle,"date"=>$pDate,"category"=>$pCat,"schoolLongName"=>$this->getSchoolName($school),"schoolShortName"=>$school);
							array_push($postArray, $line);
						}
					}
					$mypost->close();
				}
				return $this->statusDump(200, "returned userdata by USERNAME", Array("username"=>$userName,"avatarUrl"=>$avatarUrl,"posts"=>$postArray));
			}
		//return error for invalid input
	}

	public function addAndroidDeviceId($deviceId){
		if($this->getLoginStatus()){
			if($aidSTMT = $this->getUserConnection()->prepare("UPDATE `users` SET `android_deviceId` = ? WHERE `id` = ?")){
				$aidSTMT->bind_param("ss", $deviceId, $_SESSION["user_id"]);
				$aidSTMT->execute();
				$aidSTMT->store_result();
				if($aidSTMT->affected_rows == 1){
					return 0;
				}
			}
		}
		else{
			return 1;
		}
	}

	public function removeAndroidDeviceId(){
		if($this->getLoginStatus()){
			if($aidSTMT = $this->getUserConnection()->prepare("UPDATE `users` SET `android_deviceId` = null WHERE `id` = ?")){
				$aidSTMT->bind_param("s", $_SESSION["user_id"]);
				$aidSTMT->execute();
				$aidSTMT->store_result();
				if($aidSTMT->affected_rows == 1){
					return 0;
				}
			}
		}
		else{
			return 1;
		}
	}

	private function appendThreadIndex($currentUserId, $thread_id, $message_content, $reciever_id, $post_id, $post_title){
		if($reciever_id == $currentUserId)
			$threadOwners = Array($currentUserId);
		else
			$threadOwners = Array($currentUserId, $reciever_id);
		$i=0;
		foreach ($threadOwners as $owner) {
			if($i ==0)
				$associated_with = $reciever_id;
			else
				$associated_with = $currentUserId;
			if(!$appendThreadSTMT = $this->getThread_indexConnection()->prepare("INSERT INTO `$owner` (thread_id, last_message, last_user_id, post_id, post_title, datetime, new_messages, associated_with) VALUES (?, ?, ?, ?, ?, NOW(), 0, ?)"))
				return false;
			$appendThreadSTMT->bind_param("ssissi", $thread_id, $message_content, $currentUserId, $post_id, $post_title, $associated_with);
			$appendThreadSTMT->execute();
			if($appendThreadSTMT->affected_rows != 1)
				return false;
			$i++;
		}
		return true;
		$appendThreadSTMT->close();
	}

	private function createThreadTable($thread_id){
		$createThreadsQuery = "
		CREATE TABLE `$thread_id` (
	  `message_id` INT NOT NULL AUTO_INCREMENT,
	  `sender_id` INT(50) NOT NULL,
	  `sender_name` VARCHAR(20) NOT NULL,
	  `message_content` VARCHAR(1000) NOT NULL,
	  `datetime` DATETIME NOT NULL,
	  `message_seen` BIT NOT NULL,
	  PRIMARY KEY (`message_id`),
	  UNIQUE INDEX `message_id_UNIQUE` (`message_id` ASC));
		";
		if(!$createThreadTableSTMT = $this->getThreadsConnection()->prepare($createThreadsQuery))
			return false;
		$createThreadTableSTMT->execute();
		return true;
	}

	private function userOwnsThread($thread_id){
		$currentUserId = $_SESSION['user_id'];
		if(!$ownVerifySTMT = $this->getThread_indexConnection()->prepare("SELECT `thread_id` FROM `$currentUserId` WHERE `thread_id` = ?")){
			return false;
		}
		$ownVerifySTMT->bind_param("s", $thread_id);
		$ownVerifySTMT->execute();
		$ownVerifySTMT->store_result();
		if($ownVerifySTMT->num_rows != 1)
			return false;
		return true;
	}

	private function externamMailer($uid, $message, $title, $thread_id){
		if($stmt = $this->getUserConnection()->prepare("SELECT `email`, `emailPref`, `android_deviceId` FROM `users`  WHERE `id` = ? LIMIT 1")){
			$stmt->bind_param("i", $uid);
			$stmt->execute();
			$stmt->bind_result($email, $emailPref, $_androidDeviceId);
			$stmt->fetch();
		}
		$currentUserId = $_SESSION['user_id'];
		$title = "title";
		$this->GCMPush($currentUserId, $_androidDeviceId, $thread_id, $title, $message, date('Y/m/d H:i:s'));
		if($emailPref){
			$subject = "New message from a user on walkntrade";
			$messageHTML = '
			<html>
			<head></head>
			<body>
			<img src="http://walkntrade.com/colorful/wtlogo_dark.png">
			<h1>New message from '.$_SESSION["username"].' on walkntrade</h1>
			<p>
				<b>'.$title.'</b>
			</p>
			<p>
				<b>'.$message.'</b>
			</p>
			<p>
				<i>Please log-in to walkntrade.com and visit the Inbox tab in your control panel to reply.</i>
			</p>
			<p>
				<i>If you would prefer not to receive these emails, you can change your email preferences under "Contact Preferences" in your walkntrade account.</i>
			</p>
			</p>
			</body>
			</html>
			';
			$messageTEXT='New message from '.$_SESSION["username"].' on walkntrade\r\n'.$title.'\r\n'.$message.'\r\nPlease log-in to walkntrade.com and visit the Inbox tab in your control panel to reply.';
			$this->sendmailMultipart($email, $subject, $messageTEXT, $messageHTML);
		}
	}

	private function threadHasNewMessage($thread_id, $user_id){
		if(!$newMsgSTMT = $this->getThread_indexConnection()->prepare("UPDATE `$user_id` SET new_messages = new_messages + 1 WHERE thread_id = ?"))
			return false;
		$newMsgSTMT->bind_param("s", $thread_id);
		$newMsgSTMT->execute();
		if($newMsgSTMT->affected_rows == 1)
			return true;
		else
			return false;
	}

	private function threadHasNoNewMessage($thread_id, $user_id){
		if(!$noNewMsgSTMT = $this->getThread_indexConnection()->prepare("UPDATE `$user_id` SET new_messages = 0 WHERE thread_id = ?"))
			echo 1;
			// return false;
		$noNewMsgSTMT->bind_param("s", $thread_id);
		$noNewMsgSTMT->execute();
		if($noNewMsgSTMT->affected_rows == 1)
			return true;
		else
			return false;
	}

	private function updateLastMessage($thread_id, $message, $currentUserId, $associated_with){
		if($this->getLoginStatus() && $this->userOwnsThread($thread_id)){
			$owners = Array($_SESSION["user_id"], $associated_with);
			foreach ($owners as $owner) {
				if($owner == $_SESSION["user_id"])
					$message = $message;
				$lmSTMT = $this->getThread_indexConnection()->prepare("UPDATE `$owner` SET last_message = ?, last_user_id = ? WHERE thread_id = ?");
				$lmSTMT->bind_param("sis", $message, $currentUserId, $thread_id);
				$lmSTMT->execute();
			}
		}
	}

	public function appendMessage($thread_id, $message_content, $standAlone, $sendNotification){
		if($thread_id == "")
			return $this->statusDump(500, "No thread Id", null);
		if($message_content == "")
			return $this->statusDump(500, "No message", null);
		if($this->getLoginStatus() && $this->userOwnsThread($thread_id)){
			$currentUserId = $_SESSION['user_id'];
			$currentUserName = $_SESSION['username'];
			$assoc = $this->getAssoc($thread_id);
			$associated_with = $assoc["associated_with"];
			$post_title = $assoc["post_title"];
			$threadLocked = $assoc["threadLocked"];
			
			if($threadLocked){
				if($standAlone)
					return $this->statusDump(401, "The other user has closed the conversation. To talk to them again, please reply to one of their posts.", null);
				return false;
			}

			if(!$appendThreadSTMT = $this->getThreadsConnection()->prepare("INSERT INTO `$thread_id` (sender_id, sender_name, message_content, datetime, message_seen) VALUES (?, ?, ?, NOW(), 0)")){
				if($standAlone)
					return $this->statusDump(500, "Unable to update database (301)", null);
				return false;
			}
			$appendThreadSTMT->bind_param("sss", $currentUserId, $currentUserName, $message_content);
			$appendThreadSTMT->execute();
			if($appendThreadSTMT->affected_rows != 1){
				if($standAlone)
					return $this->statusDump(500, "No update (300)", null);
				return false;
			}
			$this->threadHasNewMessage($thread_id, $associated_with);
			$this->updateLastMessage($thread_id, $message_content, $currentUserId, $associated_with);
			if($sendNotification)
				$this->externamMailer($associated_with, $message_content, $post_title, $thread_id);
			if($standAlone){
				return $this->statusDump(200, "Message sent", null);
			}
			return true;
		}
		else{
			if($standAlone)
				return $this->statusDump(401, "User not authorized (255)", null);
			return false;
		}
	}

	private function getPostDetails($post_identifier){
		$args = split(":", $post_identifier);
		$identifier = htmlspecialchars($args[1]);
		$school = htmlspecialchars($args[0]);
		if($gpbiSTMT = $this->getListingConnection()->prepare("SELECT `title`, `username` FROM `$school` WHERE `identifier` = ? LIMIT 1")){
			$gpbiSTMT->bind_param("s", $identifier);
			$gpbiSTMT->execute();
			$gpbiSTMT->store_result();
			$gpbiSTMT->bind_result($_pTitle, $_pUsername);
			$gpbiSTMT->fetch();
			if($gpbiSTMT->num_rows == 1){
				return Array($_pTitle, $_pUsername);
			}
			else{
				return false;
			}
		}
	}

	public function createMessageThread($message_content, $post_id){
		if($this->getLoginStatus()){
			$postDetails = $this->getPostDetails($post_id);
			$reciever_id = $this->resolveUsernameToID($postDetails[1]);
			$post_title = $postDetails[0];
			$thread_id = $this->getRandomHex(20);
			$currentUserId = $_SESSION['user_id'];
			$currentUserName = $_SESSION['username'];

			if(!$this->appendThreadIndex($currentUserId, $thread_id, $message_content, $reciever_id, $post_id, $post_title))
				return $this->statusDump(500, "Unable to appendThreadIndex()", null);
			if(!$this->createThreadTable($thread_id))
				return $this->statusDump(500, "Unable to createThreadTable()", null);
			if(!$this->appendMessage($thread_id, $message_content, false, true))
				return $this->statusDump(500, "Unable to appendMessage()", null);
			return $this->statusDump(200, "Message sent!", Array("datetime"=>Date("Y-m-d H:i:s")));
		}
		else{
			return $this->statusDump(401, "User not authorized", null);
		}
	}

	public function getMessageThreadsCurrentUser($offset, $amount){
		if($this->getLoginStatus()){
			$currentUserId = $_SESSION['user_id'];
			if(!$getThreadsSTMT = $this->getThread_indexConnection()->prepare("SELECT thread_id, last_message, last_user_id, post_id, post_title, datetime, new_messages, associated_with FROM `$currentUserId` WHERE `hidden` = 0 ORDER BY `thread_id` DESC LIMIT ?,? "))
				return $this->statusDump(500, "Unable to get threads (1000)", null);
			$getThreadsSTMT->bind_param("ii", $offset, $amount);
			$getThreadsSTMT->execute();
			$getThreadsSTMT->bind_result($thread_id, $last_message, $last_user_id, $post_id, $post_title, $datetime, $new_messages, $associated_with);
			$threadsArray = Array();
			while($getThreadsSTMT->fetch()){
				$line = Array("thread_id"=>$thread_id, "last_message"=>$last_message, "last_user_id"=>$last_user_id, "last_user_name"=>$this->resolveIDToUsername($last_user_id), "post_id"=>$post_id, "post_title"=>$post_title, "datetime"=>$datetime, "new_messages"=>$new_messages,"associated_with"=>$associated_with,"associated_with_name"=>$this->resolveIDToUsername($associated_with),"associated_with_image"=>$this->getAvatarOf($associated_with, true));
				array_push($threadsArray, $line);
			}
			$this->statusDump(200, "Threads for current user", $threadsArray);
		}
		else{
			$this->statusDump(500, "User not authorized", null);
		}
	}

	public  function retrieveThread($thread_id, $limit){
		if($this->getLoginStatus() && $this->userOwnsThread($thread_id)){
			$retrieveThreadSTMT = $this->getThreadsConnection()->prepare("SELECT message_id, sender_id, sender_name, message_content, datetime, message_seen  FROM `$thread_id` ORDER BY `message_id` DESC LIMIT ?");
			$retrieveThreadSTMT->bind_param("i", $limit);
			$retrieveThreadSTMT->execute();
			$retrieveThreadSTMT->bind_result($message_id, $sender_id, $sender_name, $message_content, $datetime, $message_seen);
			$threadArray=Array();
			while($retrieveThreadSTMT->fetch()){
				$sentFromMe = ($sender_id == $_SESSION["user_id"])?1:0;
				$line=Array("message_id"=>$message_id,"sentFromMe"=>$sentFromMe,"sender_id"=>$sender_id,"sender_name"=>($sender_name == $_SESSION["username"])? "You" : $sender_name, $sender_name,"message_content"=>$message_content,"datetime"=>$datetime,"message_seen"=>$message_seen, "avatar"=>$this->getAvatarOf($sender_id, true));
				// array_push($threadArray, $line);
				array_unshift($threadArray, $line);
			}
			$this->threadHasNoNewMessage($thread_id, $_SESSION["user_id"]);
			$this->statusDump(200, "Here's your thread sir/madam", $threadArray);
		}
		else{
			$this->statusDump(500, "User not authorized", null);
		}
	}

	private function newMessagesForThread($thread_id){
		$currentUserId = $_SESSION["user_id"];
		$numNewSTMT = $this->getThread_indexConnection()->prepare("SELECT new_messages FROM `$currentUserId` WHERE thread_id = ?");
		$numNewSTMT->bind_param("s", $thread_id);
		$numNewSTMT->execute();
		$numNewSTMT->bind_result($new_messages);
		$numNewSTMT->fetch();
		return $new_messages;
	}

	public function retrieveThreadNew($thread_id, $override){
		if($this->getLoginStatus() && $this->userOwnsThread($thread_id)){
			$currentUserId = $_SESSION["user_id"];
			if($override == 0){
				$new_messages = $this->newMessagesForThread($thread_id);
			}
			else
				$new_messages = $override;

			$this->threadHasNoNewMessage($thread_id, $currentUserId);

			$retrieveThreadSTMT = $this->getThreadsConnection()->prepare("SELECT message_id, sender_id, sender_name, message_content, datetime, message_seen  FROM `$thread_id` ORDER BY `message_id` DESC LIMIT ?");
			$retrieveThreadSTMT->bind_param("i", $new_messages);
			$retrieveThreadSTMT->execute();
			$retrieveThreadSTMT->bind_result($message_id, $sender_id, $sender_name, $message_content, $datetime, $message_seen);
			$threadArray=Array();
			while($retrieveThreadSTMT->fetch()){
				$sentFromMe = ($sender_id == $_SESSION["user_id"])?1:0;
				$line=Array("message_id"=>$message_id,"sentFromMe"=>$sentFromMe,"sender_id"=>$sender_id,"sender_name"=>$sender_name,"message_content"=>$message_content,"datetime"=>$datetime,"message_seen"=>$message_seen, "avatar"=>$this->getAvatarOf($sender_id, true));
				// array_push($threadArray, $line);
				array_unshift($threadArray, $line);
			}
			$this->statusDump(200, "New messages for thread", $threadArray);
		}
		else{
			$this->statusDump(500, "User not authorized", null);
		}
	}

	private function getAssoc($thread_id){
		$currentUserId = $_SESSION["user_id"];
		$getAssocSTMT = $this->getThread_indexConnection()->prepare("SELECT associated_with, post_title, locked FROM `$currentUserId` WHERE thread_id = ?");
		$getAssocSTMT->bind_param("s", $thread_id);
		$getAssocSTMT->execute();
		$getAssocSTMT->bind_result($associated_with, $post_title, $threadLocked);
		$getAssocSTMT->fetch();
		$threadLocked = ($threadLocked == 1) ? true : false;
		return Array("associated_with"=>$associated_with, "post_title"=>$post_title, "threadLocked"=>$threadLocked);
	}

	public function deleteThread($thread_id){
		if($this->getLoginStatus() && $this->userOwnsThread($thread_id)){
			$currentUserId = $_SESSION["user_id"];
			$associated_with = $this->getAssoc($thread_id)["associated_with"];
			$this->appendMessage($thread_id, $_SESSION["username"]." has left the conversation. To talk to them again, reply to one of their posts.", false, false);
			if(!$deleteThreadSTMT = $this->getThread_indexConnection()->prepare("UPDATE `$currentUserId` SET `$currentUserId`.hidden = 1, `$currentUserId`.locked=1 WHERE `$currentUserId`.thread_id = ?;"))
				return $this->statusDump(500,"Unable to prepare connection (482)", null);
			$deleteThreadSTMT->bind_param("s", $thread_id);
			$deleteThreadSTMT->execute();
			if(!$deleteThreadSTMT = $this->getThread_indexConnection()->prepare("UPDATE `$associated_with` SET `$associated_with`.locked = 1	WHERE `$associated_with`.thread_id = ?;"))
				return $this->statusDump(500,"Unable to prepare connection (483)", null);
			$deleteThreadSTMT->bind_param("s", $thread_id);
			$deleteThreadSTMT->execute();
			$this->threadHasNoNewMessage($thread_id, $currentUserId);
			return $this->statusDump(200,"Thread closed", null);
		}
		else
			return $this->statusDump(401,"User Not Authorized (2479)", null);
	}

	public function hasNewMessages(){
		if($this->getLoginStatus()){
			$currentUserId = $_SESSION["user_id"];
			if(!$getNewSTMT = $this->getThread_indexConnection()->prepare("SELECT new_messages FROM `$currentUserId` WHERE new_messages > 0 AND hidden = 0"))
				return $this->statusDump(500, "unable to prepare connection (2579)", null);
			$getNewSTMT->execute();
			$getNewSTMT->bind_result($new_messages);
			$getNewSTMT->store_result();
			$total = 0;
			while($getNewSTMT->fetch()){
				$total += $new_messages;
			}
			$this->statusDump(200, $total, null);
		}
		else
			$this->statusDump(401, "User Not authorized", null);
	}

	public function markThreadAsRead($thread_id){
		if($this->getLoginStatus() && $this->userOwnsThread($thread_id)){
			$this->threadHasNoNewMessage($thread_id, $_SESSION["user_id"]);
			return $this->statusDump(200,"Ok done :)", null);
		}
		else{
			return $this->statusDump(401,"User Not Authorized (2474)", null);
		}
	}
}
?>