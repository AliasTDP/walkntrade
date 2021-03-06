<?php
class Walkntrade {
	private $host = "localhost";
	private $userDB1 = "wtonline_user";
	private $userDB2 = "wtonline_list";
	private $password = "wtonlin3";
	private $DB1 = "wtonline_users";
	private $DB2 = "wtonline_listings";
	private $DB3 = "wtonline_threads";
	private $DB4 = "wtonline_thread_index";

	private $userConnection;
	private $listingConnection;
	private $threadsConnection;
	private $thread_indexConnection;
	private $validDomains = array("walkntrade.com", "dev.wt", "172.16.10.53", "127.0.0.1");

	public function __construct(){
		$this->dbConnect();
		date_default_timezone_set('America/New_York');
	}

	public function __destruct(){
		$this->userConnection->close();
		$this->listingConnection->close();
		$this->threadsConnection->close();
		$this->thread_indexConnection->close();
	}

	private function dbConnect(){
		$this->userConnection = new mysqli($this->host, $this->userDB1, $this->password, $this->DB1);
		if($this->userConnection->connect_errno){
			echo ("Unable to connect to user database: (" . $this->userConnection->connect_errno . ") " . $this->userConnection->connect_error);
		}

		$this->listingConnection = new mysqli($this->host, $this->userDB2, $this->password, $this->DB2);
		if($this->listingConnection->connect_errno){
			echo ("Unable to connect to site database: (" . $this->listingConnection->connect_errno . ") " . $this->listingConnection->connect_error);
		}

		$this->threadsConnection = new mysqli($this->host, $this->userDB1, $this->password, $this->DB3);
		if($this->threadsConnection->connect_errno){
			echo ("Unable to access to threads: (" . $this->threadsConnection->connect_errno . ") " . $this->threadsConnection->connect_error);
		}

		$this->thread_indexConnection = new mysqli($this->host, $this->userDB1, $this->password, $this->DB4);
		if($this->thread_indexConnection->connect_errno){
			echo ("Unable to access to thread index: (" . $this->thread_indexConnection->connect_errno . ") " . $this->thread_indexConnection->connect_error);
		}
	}

	public function statusDump($status, $message, $payload){
		$response = Array("status"=>$status,"message"=>$message,"payload"=>$payload);
		echo json_encode($response);
		return;
	}

	public function getValidDomains(){
		return $this->validDomains;
	}

	public function getUserConnection(){
		return $this->userConnection;
	}

	public function getListingConnection(){
		return $this->listingConnection;
	}

	public function getThreadsConnection(){
		return $this->threadsConnection;
	}

	public function getThread_indexConnection(){
		return $this->thread_indexConnection;
	}

	public function getSchoolName($identifier){
		if($school = $this->listingConnection->prepare("SELECT `name` FROM `schools` WHERE `textId` = ? LIMIT 1")){
			$school->bind_param("s", $identifier);
			$school->execute();
			$school->store_result();
			$school->bind_result($longName);
			$school->fetch();

			if($school->num_rows == 1){
				$school->close();
				return $longName;
			}
			else{
				return null;
			}
			$school->close();
		}
		else{
			return null;
		}
	}

	public function getSchools($search){
		$search = "%".$search."%";
		if($sList = $this->listingConnection->prepare("SELECT `name`, `textId` FROM `schools` WHERE `name` LIKE ? OR `textId` LIKE ? LIMIT 5")){
			$sList->bind_param("ss", $search, $search);
			$sList->execute();
			$sList->store_result();
			$sList->bind_result($schoolName, $textId);
			if($sList->num_rows > 0){
				$concat = Array();
				while($sList->fetch()){
					$line = Array("name"=>$schoolName, "textId"=>$textId);
					array_push($concat, $line);
				}
				return statusDump(200, "schools", $concat);
			}
			return statusDump(404, "No results", Array());
			$sList->close();
		}
	}

	public function getPosts($query, $school, $category, $sort, $offset, $amount){
		$lc = $this->listingConnection;
		if(!isset($school) || $school == "" || $this->getSchoolName($school) == null){
			//No School
			return 1;
		}
		#sanatize vars--------------------------------
		$query = "%".$query."%";
		$category = (isset($category)) ? filter_var($category, FILTER_SANITIZE_STRING) : "";
		if(isset($sort)){
			switch ($sort) {
				case '1':
				$sort = "`date` DESC";
				break;
				case '2':
				$sort = "`date` ASC";
				break;
				case '3':
				$sort = "`price` DESC";
				break;
				case '4':
				$sort = "`price` ASC";
				break;
				default:
				$sort = "`date` DESC";
				break;
			}
		}
		$offset = (isset($offset)) ? filter_var($offset, FILTER_SANITIZE_NUMBER_INT) : 0 ;
		$amount = (isset($amount)) ? filter_var($amount, FILTER_SANITIZE_NUMBER_INT) : 100 ;
		#---------------------------------------------
		#echo"query:$query,category:$category,sort:$sort,offset:$offset,amount:$amount";

		switch ($category) {
			case 'book':
			$category = "book";
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` = ? AND (`title` LIKE ? OR `tags` LIKE ? OR `isbn` = ? OR `author` LIKE ? OR `username` LIKE ?) AND `expired` = 0 ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("ssssssii",$category, $query, $query, $query, $query, $query, $offset, $amount);
			break;
			case 'tech':
			$category = "tech";
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` = ? AND (`title` LIKE ? OR `tags` LIKE ? OR `username` LIKE ?) AND `expired` = 0 ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("ssssii",$category, $query, $query, $query, $offset, $amount);
			break;
			case 'housing':
			$category = "housing";
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` = ? AND (`title` LIKE ? OR `tags` LIKE ? OR `username` LIKE ?) AND `expired` = 0 ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("ssssii",$category, $query, $query, $query, $offset, $amount);
			break;
			case 'misc':
			$category = "misc";
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` = ? AND (`title` LIKE ? OR `tags` LIKE ? OR `username` LIKE ?) AND `expired` = 0 ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("ssssii",$category, $query, $query, $query, $offset, $amount);
			break;
			default:
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE (`title` LIKE ? OR `tags` LIKE ? OR `isbn` LIKE ? OR `author` LIKE ? OR `username` LIKE ?) AND `expired` = 0 ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("sssssii", $query, $query, $query, $query, $query, $offset, $amount);
			break;
		}
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($id, $identifier, $title, $cat, $author, $details, $price, $userid, $username, $date, $views);
		$concat = Array();
		while($stmt->fetch()){
			$obsId = $school.":".$identifier;
			$price = ($price != 0)? "$".round($price, 2) : "";
			$image = (file_exists("../post_images/".$school."/".$identifier."-thumb.jpeg")) ? "/post_images/".$school."/".$identifier."-thumb.jpeg" : "/colorful/tfe_no_thumb.png";
			$title = htmlspecialchars($title);
			$details = htmlspecialchars(preg_replace( "/\r|\n/", " ", $details ));
			$username = htmlspecialchars($username);
			$line = Array("id"=>$id, "obsId"=>"$obsId", "title"=>$title, "category"=>$cat, "details"=>$details, "username"=>$username, "price"=>$price, "image"=>$image, "userid"=>$userid, "date"=>$date, "views"=>$views);
			array_push($concat, $line);
		}
		$stmt->close();
		return statusDump(200, "all posts", $concat);
	}

	public function checkUname($username){
		if ($unameck = $this->userConnection->prepare("SELECT `id` FROM `users` WHERE `name` = ?")) {    
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

	public function checkEmail($email){
		if ($emailck = $this->userConnection->prepare("SELECT `id` FROM `users` WHERE `email` = ?")) {    
			$emailck->bind_param('s', $email); 
			if($emailck->execute()){
				$emailck->store_result();
				if(($emailck->num_rows) > 0){
					$emailck->close();
					return false;
				}
				else{
					$emailck->close();
					return true;
				}
			}
		}
	}

	public function verifyEmail($email){
		//prepare statement for SQL update
		if($stmt = $this->userConnection->prepare("UPDATE `users` SET `seed` = ? WHERE `email` = ? AND `verified` = 0 LIMIT 1")){
			//generate random seed
			$seed = rand(100000,999999);
			$stmt->bind_param("is", $seed, $email);
			$stmt->execute();
			//if database was updated
			if($stmt->affected_rows == 1){
				$messageTEXT = '
				Walkntrade email verification:

				Please visit the following link to verify your email address
				http://walkntrade.com/validateKey?token='.$seed.'
				Or you may enter the code below if prompted.
				'.$seed.'
				If you believe that this email was sent in error, you may ignore it or email wt@walkntrade.com and we will investigate the issue.
				';
				$messageHTML = '<html>
				<head>
				</head>
				<body>
				<img src="http://walkntrade.com/colorful/wtlogo_dark.png">
				<h1>Walkntrade email verification</h1>
				<h2>Please visit the following link to verify your email address</h2>
				<i>http://walkntrade.com/validateKey?token='.$seed.'</i>
				<h2>Or you may enter the code below if prompted.</h2>
				<h1><span class="tab">'.$seed.'</span></h1>
				<p>
				If you believe that this email was sent in error, you may ignore it or email wt@walkntrade.com and we will investigate the issue.
				</p>
				</p>
				</body>
				</html>
				';
				return $this->sendmailMultipart($email, "Walkntrade.com email verification", $messageTEXT, $messageHTML);
			}
			else return -1;
		}
	}

	public function sendmailMultipart($email, $subject, $messageTEXT, $messageHTML){			
		$boundary = uniqid('np');

		$headers = 'From: "Walkntrade.com"<no-reply@walkntrade.com>' . "\r\n";
		$headers .= "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-Type: multipart/alternative;boundary=" . $boundary . "\r\n";

		//here is the content body
		$message = "This is a MIME encoded message.";
		$message .= "\r\n\r\n--" . $boundary . "\r\n";
		$message .= "Content-type: text/plain;charset=utf-8\r\n\r\n";

		//Plain text body
		$message .= $messageTEXT;
		$message .= "\r\n\r\n--" . $boundary . "\r\n";
		$message .= "Content-type: text/html;charset=utf-8\r\n\r\n";

		//Html body
		$message .= $messageHTML;
		$message .= "\r\n\r\n--" . $boundary . "--";

		if(mail($email, $subject, $message, $headers)){
			//success
			return 0;
		}
		else{
			//error connection to mail server
			return 3;
		}
	}

	public function sendFeedback($from, $message){
		$messageTEXT = $from.' : '.$message;
		if($this->sendmailMultipart("wt@walkntrade.com", "Feedback from Walkntrade", $messageTEXT, $messageTEXT) == 0)
			return $this->statusDump(200, "Thanks for your feedback! You're awesome :)", null);
		else
			return $this->statusDump(500, "We're having some connection problems at the moment. If you wish you may send us your feedback at feedback@walkntrade.com. Thanks!", null);
	}

	public function resetPassword($email){
		if($this->checkEmail($email))
			$this->statusDump(401, "User not found", null);
		else{
		//get old password in case operation fails
			if($getOldPassSTMT = $this->userConnection->prepare("SELECT `password` FROM `users` WHERE `email` = ?")){
				$getOldPassSTMT->bind_param("s", $email);
				$getOldPassSTMT->execute();
				$getOldPassSTMT->store_result();
				$getOldPassSTMT->bind_result($oldPassword);
				$getOldPassSTMT->fetch();
			}
			$obscure_password = substr(md5(rand()), 0, 10); 
			$obscure_passwordMD5 = md5($obscure_password);
			if($resetPasswordSTMT = $this->userConnection->prepare("UPDATE `users` SET `password` = ?  WHERE `email` = ? LIMIT 1")){
				$resetPasswordSTMT->bind_param("ss", $obscure_passwordMD5, $email);
				$resetPasswordSTMT->execute();
				if(($resetPasswordSTMT->affected_rows) == 1){
					$subject = "Walkntrade.com password reset request";
					$messageTEXT = '
					Dear Walkntrade user,

					Someone (hopefully you) has requested a password change for your account. You can now login with this password

					"'.$obscure_password.'"

					It is strongly recommended that you change your password to something easier to remember after you login to your account.
					';
					$messageHTML = '
					<html>
					<head></head>
					<body>
					<p>
					<img src="http://walkntrade.com/colorful/wtlogo_dark.png">
					<h1>Walkntrade.com password reset request</h1>
					<h2>Someone (hopefully you) has requested a password change for your account.</h2>
					<p>Here\'s a new temporary password. You can use it to login, but it is recommended that you change it to something a little easier to remember afterwards.</p>
					<h2>'.$obscure_password.'</h2>
					</p>
					</body>
					</html>
					';
					if($this->sendmailMultipart($email, $subject, $messageTEXT, $messageHTML)==0)
						$this->statusDump(200, "Password succesfully reset", null);
					else
						$this->statusDump(500, "We cannot send the email at this time", null);
				}
				else{
					$this->statusDump(500, "Internal Error!", null);
				}
			}
		}
	}

	public function verifyKey($key){
		$seed = (strlen($key) == 6) ? $key  : "-1";
		if($check_stmt = $this->userConnection->prepare("SELECT `id` FROM `users` WHERE `seed` = ? LIMIT 1")){
			$check_stmt->bind_param("i", $seed);
			$check_stmt->execute();
			$check_stmt->store_result();
			if(($check_stmt->num_rows) == 1){//if the seed is legit
				$check_stmt->close();
				if($verify_stmt = $this->userConnection->prepare("UPDATE `users` SET `verified` = 1, `seed` = 0 WHERE `seed` = ? LIMIT 1")){
					$verify_stmt->bind_param("i", $seed);
					if($verify_stmt->execute()){
						return 0;#success
					}
					$verify_stmt->close();
					return;
				}
				else{
					$check_stmt->close();
					return 1; #error 1
					return;
				}
			}
			else{
				return 2; #invalid key
			}
		}
		else{
			return 3; #error 2
		}
	}

	public function cookieCheck($intent){
		if(isset($_COOKIE["$intent"])){
			return $_COOKIE["$intent"];
		}
		else{
			return -1;
		}
	}

	public function getPostByIdentifier($identifier, $school){
		if($gpbiSTMT = $this->listingConnection->prepare("SELECT `id`, `category`, `title`, `author`, `details`, `price`, `isbn`, `tags`, `username`, `date`, `views` FROM `$school` WHERE `identifier` = ? LIMIT 1")){
			$gpbiSTMT->bind_param("s", $identifier);
			$gpbiSTMT->execute();
			$gpbiSTMT->store_result();
			$gpbiSTMT->bind_result($_pId, $_pCategory, $_pTitle, $_pAuthor, $_pDetails, $_pPrice, $_pIsbn, $_pTags, $_pUsername, $_pDate, $_pViews);
			$gpbiSTMT->fetch();
			if($gpbiSTMT->num_rows != 1){
				return $this->statusDump(404, "Post Not Found!", null);
			}
			else{
				$line = Array("id"=>$_pId, "category"=>$_pCategory, "title"=>htmlspecialchars($_pTitle), "author"=>htmlspecialchars($_pAuthor), "details"=>htmlspecialchars($_pDetails), "price"=>$_pPrice, "isbn"=>$_pIsbn, "tags"=>$_pTags, "username"=>$_pUsername, "date"=>$_pDate, "views"=>$_pViews);
				return $this->statusDump(200, "post Info", $line);
			}
		}
	}

	public function getAgeInDays($date_string){
		#date format YYYY-MM-DD
		$date1 = new DateTime($date_string);
		$date2 = new DateTime(date("Y-m-d"));

		$diff = $date2->diff($date1)->format("%a");
		$string = ($diff == 0) ? "Today" : $diff ." day(s) ago";
		return $string;
	}

	public function getCategories(){
		$categoriesRawString = file_get_contents("../include/categories.txt");
		$categoriesParsed = explode('$newCat', $categoriesRawString);
		$i = 0;
		foreach ($categoriesParsed as $category) {
			$catParsed = explode(';', str_replace("\n", "", $category));
			$categoriesParsed[$i] = $catParsed;
			$i++;
		}
		$response = Array("categories"=>$categoriesParsed);
		$this->statusDump(200, "success", $response);
	}

	public function getRandomHex($valLength){
  	return substr(md5(rand()), 0, $valLength);  
	}
}
?>