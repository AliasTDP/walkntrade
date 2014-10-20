<?php
require_once "Walkntrade.php";

class CredentialStoreCompat extends Walkntrade{
	private $loggedIn;

	public function __construct(){
		parent::__construct();
		$this->secure_session_start();
		if($this->authorizeFromSession())
			$this->loggedIn = true;
		else if($this->authorizeFromCookie())
			$this->loggedIn = true;
		else
			$this->loggedIn = false;
	}

	private function authorizeFromSession() {
		if(isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) {
			$user_id = $_SESSION['user_id'];
			$login_string = $_SESSION['login_string'];
			$uname = $_SESSION['username'];
			$user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
			if ($stmt = $this->getUserConnection()->prepare("SELECT password  FROM users WHERE id = ? LIMIT 1")) { 
				$stmt->bind_param('i', $user_id); // Bind "$user_id" to parameter.
				$stmt->execute(); // Execute the prepared query.
				$stmt->store_result();
				if($stmt->num_rows == 1) { // If the user exists
					$stmt->bind_result($password); // get variables from result.
					$stmt->fetch();
					$login_check = md5($password.$user_browser);
					if($login_check == $login_string) {
						// Logged In!!!!
						$stmt->close();
						return true;
					}
					else{
						return false;
					}
				}
				else{
					// User dosen't exist
					$stmt->close();
					return false;
				}
			}
			else{
				// SQL Error
				return false;
			}
		}
		else{
			// Not logged in
			return false;
		}
	}

	private function authorizeFromCookie(){
		if(isset($_COOKIE["sessionUid"]) && isset($_COOKIE["sessionSeed"])){
			$user_id = $_COOKIE["sessionUid"];
			$login_string = $_COOKIE["sessionSeed"];
			$user_browser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
			if ($stmt = $this->getUserConnection()->prepare("SELECT password, name, email, infractions, emailPref FROM users WHERE id = ? LIMIT 1")) { 
				$stmt->bind_param('i', $user_id); // Bind "$user_id" to parameter.
				$stmt->execute(); // Execute the prepared query.
				$stmt->store_result();
				if($stmt->num_rows == 1) { // If the user exists
					$stmt->bind_result($password,$username,$emailAddress, $infractions, $emailPref); // get variables from result.
					$stmt->fetch();
					$login_check = md5($password.$user_browser);
					if($login_check == $login_string && $infractions < 5) {
						// Logged In!!!!
						$_SESSION['user_id'] = $user_id;
						$_SESSION["username"] = preg_replace("/[^a-zA-Z0-9_\-.]+/", "", $username);
						$_SESSION['emailAddress'] = $emailAddress;
						$_SESSION['login_string'] = $login_check;
						$_SESSION['email_me'] = ($emailPref == 1) ? true : false;
						$stmt->close();
						if($log_user_stmt = $this->getUserConnection()->prepare("UPDATE `users` SET last_visited = ? WHERE id = ? LIMIT 1")){
							$date = date('Y/m/d H:i:s');
		        	$log_user_stmt->bind_param("si", $date, $user_id);
		        	$log_user_stmt->execute();
		        	$log_user_stmt->close();
		        }
						return true;
					}
					else{
						return false;
					}
				}
				else{
					// User dosen't exist
					$stmt->close();
					return false;
				}
			}
			else{
				// SQL Error
				return false;
			}
		} 
		else{
			// Not logged in
			return false;
		}
	}

	private function secure_session_start(){
		$session_name = 'user_login'; // Set a custom session name
		$secure = false; // Set to true if using https.
		$httponly = true; // This stops javascript being able to access the session id. 
		ini_set('session.use_only_cookies', 1); // Forces sessions to only use cookies. 
		$cookieParams = session_get_cookie_params(); // Gets current cookies params.
		session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly); 
		session_name($session_name); // Sets the session name to the one set above.
		session_start(); // Start the php session
		session_regenerate_id(true); // regenerated the session, delete the old one.
	}

	public function getLoginStatus(){
		return $this->loggedIn;
	}

	public function getUserName(){
		if ($this->loggedIn){
			return $_SESSION["username"];
		}
	}
}

?>