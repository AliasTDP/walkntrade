<?php
require_once "CredentialStore.php";
class UserMgmt extends CredentialStore{
	
	public function __construct(){
		parent::__construct();
	}

	public function login($email, $password, $persist){
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
		$uc = $this->getUserConnection();
		$username = preg_replace("/[^a-zA-Z0-9_\-.]+/", "", $username);
		$password = md5($password);

		if (strlen($username) < 5 || strlen($username) > 20){
			return;
		}
		else{
			if (!$this->checkUname($username)){
				return 4;
			}
		}

		if (strlen($email) == ""){
			return;
		}
		else{
			if (!$this->checkEmail($email)){
				return 3;
			}
		}

		if (strlen($phone) != 10){
			//return 10;
			$phone = 0;
		}

		if (strlen($password) < 8){
			return 8;
		}

		if ($insert_stmt = $uc->prepare("INSERT INTO `users` (name, email, password, phone, seed) VALUES (?, ?, ?, ?, ?)")){
			$seed = rand(1000000,9999999);
			$insert_stmt->bind_param('ssssi', $username, $email, $password, $phone, $seed); 
			$insert_stmt->execute();
			if($insert_stmt->affected_rows == 1){
				$status = $this->verifyEmail($email);
				if($status == 0){
					$insert_stmt->close();
					//success
					if($log_user_stmt = $uc->prepare("UPDATE `users` SET date_registered = ? WHERE email = ? LIMIT 1")){
						$date = date('Y/m/d H:i:s');
						$log_user_stmt->bind_param("ss", $date, $email);
						$log_user_stmt->execute();
						$log_user_stmt->close();
					}
					else
						return 550;
					if($getUIDSTMT = $uc->prepare("SELECT `id` FROM `users` WHERE email = ? LIMIT 1")){
						$getUIDSTMT->bind_param("s", $email);
						$getUIDSTMT->execute();
						$getUIDSTMT->store_result();
						$getUIDSTMT->bind_result($uuid);
						$getUIDSTMT->fetch();
						$getUIDSTMT->close();
					}
					else
						return 450;
					if($createInboxSTMT = $this->getWebmailConnection()->prepare("CREATE TABLE `uid_".$uuid."` (
																				  `id` int(11) NOT NULL AUTO_INCREMENT,
																				  `from` int(10) NOT NULL,
																				  `to` int(10) NOT NULL,
																				  `subject` varchar(100) NOT NULL,
																				  `message` text NOT NULL,
																				  `datetime` datetime NOT NULL,
																				  `read` bit(1) DEFAULT b'0',
																				  `trash` bit(1) DEFAULT b'0',
																				  PRIMARY KEY (`id`)
																				) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
																				")){
						$createInboxSTMT->execute();
						$createInboxSTMT->close();
					}
					else
						return 350;
					return 0;
				}
				else{
					$insert_stmt->close();
					//unable to send email
					return "-3e"+$status;
				}
			}
			else{
				//table not updated
				return 2;
			}
		}
		else {
				//SQL error
			return 1;
		}
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
			$concatenated = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<results>\n";
			while($schs->fetch()){
				$mypost = $lc->prepare("SELECT `id`, `identifier`, `category`, `title`,  `date`, `views`, `expire`, `expired` FROM `".$school."` WHERE `userid` = ? ORDER BY `id` DESC");
				$mypost->bind_param("s", $_SESSION["user_id"]);
				$mypost->execute();
				$mypost->store_result();
				$mypost->bind_result($pId, $identifier, $pCat, $pTitle, $pDate, $pViews, $pExpire, $pExpired);
				if($mypost->num_rows > 0){
					$concatenated = $concatenated."\t<school shortName=\"".$school."\" longName=\"".$this->getSchoolName($school)."\">";

					while($mypost->fetch()){
						$link =  $school.":".$identifier;
						$pTitle =(strlen($pTitle) > 55) ? substr($pTitle, 0, 55)."..." : $pTitle;
						$html_blacklist = "/< >/";
						$pTitle = ($pExpired == true) ? "[EXPIRED] ".htmlspecialchars($pTitle): htmlspecialchars($pTitle);
						$pDate = htmlspecialchars($pDate);
						$pCat = htmlspecialchars($pCat);
						$pExpired = ($pExpired == 1) ? "true" : "false";
						$concatenated = $concatenated."\t\t<post id=\"".$pId."\" link=\"".$link."\" category=\"".$pCat."\" title=\"".$pTitle."\" date=\"".$pDate."\" views=\"".$pViews."\" expire=\"".$pExpire."\" expired=\"".$pExpired."\"/>\n";
					}
					$concatenated = $concatenated."\t</school>\n";
				}
				$mypost->close();
			}
			$concatenated = $concatenated."</results>";
			return $concatenated;
		}
		else{
		//NLI
			return 1;
		}
	}

	public function getSentWebmail(){
		$wc = $this->getWebmailConnection();
		if($this->getLoginStatus()){
			if($webmailSTMT = $wc->prepare("SELECT `id`, `to`, `subject`, `message`, `datetime` FROM `uid_".$_SESSION["user_id"]."` WHERE `trash` = 0 AND `from` = ? ORDER BY `id` DESC LIMIT 100")){
				$webmailSTMT->bind_param("i", $_SESSION["user_id"]);
				$webmailSTMT->execute();
				$webmailSTMT->store_result();
				$webmailSTMT->bind_result($id, $to, $subject, $message, $datetime);
			}
			else return 152;
			$concatenated = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<webmail>\n";
			while($webmailSTMT->fetch()){
				$to_resolved = $this->resolveIDToUsername($to);
				$to = ($to_resolved == null) ? "[deleted]" : $to_resolved;
				$subject = (strlen($subject) >= 35)? substr($subject, 0, 35)."..." : $subject;
				$message = (strlen($message) >= 40)? substr($message, 0, 40)."..." : $message;
				$subject = htmlspecialchars($subject);
				$message = htmlspecialchars($message);
				$concatenated = $concatenated."<message id=\"".$id."\" to=\"".$to."\" subject=\"".$subject."\" message=\"".$message."\" datetime=\"".$datetime."\"/>\n";
			}
			$webmailSTMT->close();
			$concatenated = $concatenated."</webmail>";
			return $concatenated;
		}
		else{
		//NLI
			return 1;
		}
	}

	public function getWebmail(){
		$wc = $this->getWebmailConnection();
		if($this->getLoginStatus()){
			if($webmailSTMT = $wc->prepare("SELECT `id`, `from`, `subject`, `message`, `datetime`, `read` FROM `uid_".$_SESSION["user_id"]."` WHERE `trash` = 0 AND `to` = ? ORDER BY `id` DESC LIMIT 100")){
				$webmailSTMT->bind_param("i", $_SESSION["user_id"]);
				$webmailSTMT->execute();
				$webmailSTMT->store_result();
				$webmailSTMT->bind_result($id, $from, $subject, $message, $datetime, $read);
			}
			else return 152;
			$concatenated = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<webmail>\n";
			while($webmailSTMT->fetch()){
				$from_resolved = $this->resolveIDToUsername($from);
				$from = ($from_resolved == "null") ? "[deleted]" : $from_resolved;
				$subject = (strlen($subject) >= 35)? substr($subject, 0, 35)."..." : $subject;
				$message = (strlen($message) >= 40)? substr($message, 0, 40)."..." : $message;
				$subject = htmlspecialchars($subject);
				$message = htmlspecialchars($message);
				$concatenated = $concatenated."<message id=\"".$id."\" from=\"".$from."\" subject=\"".$subject."\" message=\"".$message."\" datetime=\"".$datetime."\" read=\"".$read."\"/>\n";
			}
			$webmailSTMT->close();
			$concatenated = $concatenated."</webmail>";
			return $concatenated;
		}
		else{
		//NLI
			return 1;
		}
	}

	public function pollNewWebmail(){
		if($this->getLoginStatus()){//If user logged in
			if($pollNewWebmailSTMT = $this->getWebmailConnection()->prepare("SELECT `id` FROM uid_".$_SESSION["user_id"]." WHERE `trash` = 0 AND `to` = ? AND `read` = 0")){
				$pollNewWebmailSTMT->bind_param("i", $_SESSION["user_id"]);
				$pollNewWebmailSTMT->execute();
				$pollNewWebmailSTMT->store_result();
				return $pollNewWebmailSTMT->num_rows();
			}
		}
	}

	public function getMessage($messageId){
		if($this->getLoginStatus()){
			if($getMessageSTMT = $this->getWebmailConnection()->prepare("SELECT `from`, `to`, `subject`, `message`, `datetime` FROM `uid_".$_SESSION["user_id"]."` WHERE id = ?")){
				$getMessageSTMT->bind_param("i", $messageId);
				$getMessageSTMT->execute();
				$getMessageSTMT->store_result();
				$getMessageSTMT->bind_result($from, $to, $subject, $message, $datetime);
				$getMessageSTMT->fetch();
			}
			if($getUserNameSTMT = $this->getUserConnection()->prepare("SELECT name FROM users where id = ? LIMIT 1")){
				$getUserNameSTMT->bind_param("i", $from);
				$getUserNameSTMT->execute();
				$getUserNameSTMT->store_result();
				$getUserNameSTMT->bind_result($from);
				$getUserNameSTMT->fetch();
				$getUserNameSTMT->close();
				$from = ($from == "") ? "[deleted user]" : $from;
			}

			if($getMessageSTMT = $this->getWebmailConnection()->prepare("UPDATE `uid_".$_SESSION["user_id"]."` SET `read` = 1 WHERE id = ?")){
				$getMessageSTMT->bind_param("i", $messageId);
				$getMessageSTMT->execute();
			}
			else return 150;
			$getMessageSTMT->close();
			$html_blacklist = "/< >/";
			$subject = htmlspecialchars($subject);
			$message = htmlspecialchars($message);
			$concatenated = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<message from=\"".$from."\" to=\"".$this->resolveIDToUsername($to)."\" subject=\"".$subject."\" message=\"".$message."\" datetime=\"".$datetime."\"/>";
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

	public function removeMessage($messageId){
		if($this->getLoginStatus()){
			if($removeMessageSTMT = $this->getWebmailConnection()->prepare("UPDATE `uid_".$_SESSION["user_id"]."` SET `trash` = 1 WHERE id = ? LIMIT 1")){
				$removeMessageSTMT->bind_param("i", $messageId);
				$removeMessageSTMT->execute();
				if($removeMessageSTMT->affected_rows == 1){
					return 0;
				}
			}
		}
		else{
		//NLI
			return 1;
		}
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

	public function messageUser($uid, $title, $message){
		if($this->getLoginStatus()){
			$needles = array(" fuck ", " shit ", " damn ", " bitch ", " ass ", " dick ", " pussy ", " motherfucker ");
			foreach ($needles as $needle) {
				if(stripos($message, $needle) !== false){
					if($stmtINFRACTIONS = $this->getUserConnection()->prepare("UPDATE `users` SET `infractions` = `infractions` + 1 WHERE `id` = ? LIMIT 1")){
						$stmtINFRACTIONS->bind_param("i", $_SESSION["user_id"]);
						$stmtINFRACTIONS->execute();
					}
					return 5;
				}
			}
			if($stmt = $this->getUserConnection()->prepare("SELECT `email`, `emailPref` FROM `users`  WHERE `id` = ? LIMIT 1")){
				$stmt->bind_param("i", $uid);
				$stmt->execute();
				$stmt->store_result();
				if($stmt->affected_rows == 1){
					$stmt->bind_result($email, $emailPref);
					$emailPref = ($emailPref == "1") ? true : false;
					$stmt->fetch();
					//success
					date_default_timezone_set('America/New_York');
					$user_inbox = "uid_".$uid;
					if($webmailSTMT = $this->getWebmailConnection()->prepare("INSERT INTO ".$user_inbox." (`from`, `to`, `subject`, `message`, `datetime`, `read`, `trash`) VALUES (?,?,?,?,?,0,0)")){
						$dateTime = date('Y/m/d H:i:s');
						$title = ($title == "") ? "[no title]" : $title;
						$message = ($message == "") ? "[no message]" : $message;
						$webmailSTMT->bind_param("iisss", $_SESSION["user_id"], $uid, $title, $message, $dateTime);
						$webmailSTMT->execute();
						if($_SESSION["user_id"] != $uid){
							if($webmailSTMT = $this->getWebmailConnection()->prepare("INSERT INTO "."uid_".$_SESSION["user_id"]." (`from`, `to`, `subject`, `message`, `datetime`, `read`, `trash`) VALUES (?,?,?,?,?,0,0)")){
								$webmailSTMT->bind_param("iisss", $_SESSION["user_id"], $uid, $title, $message, $dateTime);
								$webmailSTMT->execute();
							}
						}
						if($webmailSTMT->affected_rows == 1){
							$webmailSTMT->close();
							if($archiveSTMT = $this->getUserConnection()->prepare("INSERT INTO `interactions`(`sender`,`reciever`,`message`,`datetime`)VALUES(?,?,?,?);")){
								$archiveSTMT->bind_param("ssss", $_SESSION{"emailAddress"}, $email, $message, $dateTime);
								$archiveSTMT->execute();
							}
							if($emailPref){
								$subject = "New message from a user on walkntrade";
								$string = '
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
								</p>
								';
								$headers = "MIME-Version: 1.0" . "\r\n";
								$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
								$headers .= 'From: <no-reply@walkntrade.com>' . "\r\n";
								if(mail($email, $subject, $string, $headers)){
									return 0;
								}
								else return 4;
							}
							else return 0;
						}
						else{
							$webmailSTMT->close();
							return 120;
						}
					}
					else{
						return 100;
					}
				}
				else{
				//user does not exist
					return 3;
				}
			}
			else{
			//SQL error
				return 2;
			}
		}
		else{
		//NLI
			return 1;
		}
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

	public function getAvatarOf($userid){
		if(file_exists("user_images/uid_".$userid.".jpg"))
			return("/user_images/uid_".$userid.".jpg");
		else
			return("/colorful/Anonymous_User.jpg");
	}

	public function getUserProfile($uid){
		$schs = $this->getListingConnection()->prepare("SELECT `textId` FROM `schools` ORDER BY `name` DESC LIMIT 100");
		$schs->execute();
		$schs->store_result();
		$schs->bind_result($school);
		$userName = $this->resolveIDToUsername($uid);
		if($userName != null){
			$avatarUrl = $this->getAvatarOf($uid);
			$concatenated = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<userProfile>\n";
			$concatenated .= "<username>".$userName."</username>\n";
			$concatenated .= "<avatarUrl>".$avatarUrl."</avatarUrl>\n";
			while($schs->fetch()){
				$mypost = $this->getListingConnection()->prepare("SELECT `id`, `identifier`, `category`, `title`,  `date`, `views` FROM `".$school."` WHERE `userid` = ? ORDER BY `id` DESC");
				$mypost->bind_param("s", $uid);
				$mypost->execute();
				$mypost->store_result();
				$mypost->bind_result($pId, $identifier, $pCat, $pTitle, $pDate, $pViews);
				if($mypost->num_rows > 0){
					$concatenated = $concatenated."\t<school shortName=\"".$school."\" longName=\"".$this->getSchoolName($school)."\">\n";

					while($mypost->fetch()){
						$link =  $school.":".$identifier;
						$pTitle =(strlen($pTitle) > 55) ? substr($pTitle, 0, 55)."..." : $pTitle;
						$html_blacklist = "/< >/";
						$pTitle = htmlspecialchars($pTitle);
						$pDate = htmlspecialchars($pDate);
						$pCat = htmlspecialchars($pCat);
						$concatenated = $concatenated."\t\t<post id=\"".$pId."\" link=\"".$link."\" category=\"".$pCat."\" title=\"".$pTitle."\" date=\"".$pDate."\" views=\"".$pViews."\"/>\n";
					}
					$concatenated = $concatenated."</school>\n";
				}
				$mypost->close();
			}
			$concatenated = $concatenated."</userProfile>";
			return $concatenated;
		}
	}
}
?>