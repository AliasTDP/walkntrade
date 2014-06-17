<?php
class Walkntrade {
	private $host = "localhost";
	private $userDB1 = "wtonline_user";
	private $userDB2 = "wtonline_list";
	private $password = "wtonlin3";
	private $DB1 = "wtonline_users";
	private $DB2 = "wtonline_listings";
	private $DB3 = "wtonline_webmail";

	private $userConnection;
	private $listingConnection;
	private $webmailConnection;

	public function __construct(){
		$this->dbConnect();
		date_default_timezone_set('America/New_York');
	}

	public function __destruct(){
		$this->userConnection->close();
		$this->listingConnection->close();
		$this->webmailConnection->close();
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

		$this->webmailConnection = new mysqli($this->host, $this->userDB1, $this->password, $this->DB3);
		if($this->webmailConnection->connect_errno){
			echo ("Unable to connect to webmail database: (" . $this->webmailConnection->connect_errno . ") " . $this->webmailConnection->connect_error);
		}
	}

	public function getUserConnection(){
		return $this->userConnection;
	}

	public function getListingConnection(){
		return $this->listingConnection;
	}

	public function getWebmailConnection(){
		return $this->webmailConnection;
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
				echo("<results>");
				while($sList->fetch()){
					echo("<school name='".$schoolName."' textId='".$textId."'></school>");
				}
				echo("</results>");
			}
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
				$sort = "`id` DESC";
				break;
				case '2':
				$sort = "`id` ASC";
				break;
				case '3':
				$sort = "`price` DESC";
				break;
				case '4':
				$sort = "`price` ASC";
				break;
				default:
				$sort = "`id` DESC";
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
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` = ? AND (`title` LIKE ? OR `tags` LIKE ? OR `isbn` = ? OR `author` LIKE ?) ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("sssssii",$category, $query, $query, $query, $query, $offset, $amount);
			break;
			case 'tech':
			$category = "tech";
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` = ? AND (`title` LIKE ? OR `tags` LIKE ?) ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("sssii",$category, $query, $query, $offset, $amount);
			break;
			case 'service':
			$category = "service";
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` = ? AND (`title` LIKE ? OR `tags` LIKE ?) ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("sssii",$category, $query, $query, $offset, $amount);
			break;
			case 'misc':
			$category = "misc";
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` = ? AND (`title` LIKE ? OR `tags` LIKE ?) ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("sssii",$category, $query, $query, $offset, $amount);
			break;

			//---------------JUST FOR ASA-------------------------
			case 'asa':
			$category = "asa";
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` = ? AND (`title` LIKE ? OR `tags` LIKE ?) ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("sssii",$category, $query, $query, $offset, $amount);
			break;

			default:
			$stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `category` != 'asa' AND (`title` LIKE ? OR `tags` LIKE ? OR `isbn` LIKE ? OR `author` LIKE ?) ORDER BY ".$sort." LIMIT ?,?");
			$stmt->bind_param("ssssii", $query, $query, $query, $query, $offset, $amount);
			break;
			//---------------!JUST FOR ASA------------------------

			// default:
			// $stmt = $lc->prepare("SELECT `id`, `identifier`, `title`, `category`, `author`, `details`, `price`, `userid`, `username`, `date`, `views` FROM `" . $school . "` WHERE `title` LIKE ? OR `tags` LIKE ? OR `isbn` LIKE ? OR `author` LIKE ? ORDER BY ".$sort." LIMIT ?,?");
			// $stmt->bind_param("ssssii", $query, $query, $query, $query, $offset, $amount);
			// break;
		}
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($id, $identifier, $title, $cat, $author, $details, $price, $userid, $username, $date, $views);
		$string = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<results>\n";

		while($stmt->fetch()){
			$obsId = $school.":".$identifier;
			$title = (strlen($title) >= 23)? substr($title, 0, 23)."..." : $title;
			
			$details = (strlen($details) >= 80)? substr($details, 0, 80)."..." : $details;
			$username = (strlen($username) >= 30)? substr($username, 0, 30)."..." : $username;
			$price = ($price != 0)? "$".round($price, 2) : "";
			$id = htmlspecialchars($id);
			$image = (file_exists("../post_images/".$school."/".$identifier."-thumb.jpeg")) ? "/post_images/".$school."/".$identifier."-thumb.jpeg" : "/colorful/tfe_no_thumb.png";

			$title = htmlspecialchars($title);
			$details = htmlspecialchars($details);
			$username = htmlspecialchars($username);

			$string = $string."\t<listing id=\"$id\" obsId=\"$obsId\" title=\"$title\" category=\"$cat\" details=\"$details\" username=\"$username\" price=\"$price\" image=\"$image\" userid=\"$userid\" date=\"$date\" views=\"$views\"/>\n";
		}
		$string = $string."</results>";
		$stmt->close();
		return $string;;
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
		if($stmt = $this->userConnection->prepare("UPDATE `users` SET `seed` =  ? WHERE `email` = ? LIMIT 1")){
			//generate random seed
			$seed = rand(100000,999999);
			$stmt->bind_param("is", $seed, $email);
			$stmt->execute();
			//if database was updated
			if($stmt->affected_rows == 1){
				//get email params and send confirmation email
				$email = filter_var($email, FILTER_SANITIZE_EMAIL);
				$subject = "WalkNtrade Email Verification";
				$string = '
				<img src="http://walkntrade.com/colorful/wtlogo.png">
				<h1>Walkntrade email verification</h1>
				<h2>Please visit the following link to verify your email address</h2>
				<i>http://walkntrade.com/validateKey?token='.$seed.'</i>
				<h2>Or you may enter the code below if prompted.</h2>
				<h1><span class="tab">'.$seed.'</span></h1>
				<p>
				If you believe that this email was sent in error, you may ignore it or email feedback@walkntrade.com and we will investigate the issue.
				</p>
				</p>
				';
				$headers = "MIME-Version: 1.0" . "\r\n";
				$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
				$headers .= 'From: <no-reply@walkntrade.com>' . "\r\n";
				if(mail($email, $subject, $string, $headers)){
					//success
					return 0;
				}
				else{
					//error connection to mail server
					return "e3";
				}
			}
			else{
				//error updating table
				return "e2";
			}
		}
		else{
			//SQL error
			return "e1";
		}
	}

	public function sendFeedback($from, $message){
		//get email params and send confirmation email
		$email = "feedback@walkntrade.com";
		$subject = "feedback from walkntrade";
		$string = '
		<h2>A user on walkntrade sent feedback</h2>
		<p><i>'.$from.':</i></p>
		<p>'.$message.'</p>
		';
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: <no-reply@walkntrade.com>' . "\r\n";
		if(mail($email, $subject, $string, $headers)){
					//success
			return 0;
		}
		else{
					//error connection to mail server
			return 3;
		}
	}

	public function resetPassword($email){
		if($this->checkEmail($email))
			return 5;
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
					//get email params and send confirmation email
					$subject = "WalkNtrade Password reset";
					$string = '
					<p>
					<img src="http://walkntrade.com/colorful/wtlogo.png">
					<h1>WalkNtrade Password reset</h1>
					<h2>You have requested a new password</h2>
					<p>Here\'s a new temporary password. You can use it to login, but it is recommended that you change it to something a little easier to remember.</p>
					<h2>'.$obscure_password.'</h2>
					</p>
					';
					$headers = "MIME-Version: 1.0" . "\r\n";
					$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
					$headers .= 'From: <no-reply@walkntrade.com>' . "\r\n";
					if(mail($email, $subject, $string, $headers)){
					//success
						$resetPasswordSTMT->close();
						return 0;
					}
					else{
					//error connection to mail server
						if($revertPasswordSTMT = $this->userConnection->prepare("UPDATE `users` SET `password` = ?  WHERE `email` = ? LIMIT 1")){
							$revertPasswordSTMT->bind_param("ss", $oldPassword, $email);
							$revertPasswordSTMT->execute();
							$revertPasswordSTMT->close();
						}
							return 3;
						}
					}
					else{
						$resetPasswordSTMT->close();
						return 2;
					}
				}
				else{
					return 1;
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
		if($gpbiSTMT = $this->listingConnection->prepare("SELECT `id`, `category`, `title`, `author`, `details`, `price`, `isbn`, `tags`, `username`, `date`, `views` FROM `spsu` WHERE `identifier` = ? LIMIT 1")){
			$gpbiSTMT->bind_param("s", $identifier);
			$gpbiSTMT->execute();
			$gpbiSTMT->store_result();
			$gpbiSTMT->bind_result($_pId, $_pCategory, $_pTitle, $_pAuthor, $_pDetails, $_pPrice, $_pIsbn, $_pTags, $_pUsername, $_pDate, $_pViews);
			$gpbiSTMT->fetch();
			if($gpbiSTMT->num_rows != 1){
				echo "no such post";
			}
			else{
				$concatenated = "<post>\n";
					$concatenated .= "\t<id>".$_pId."</id>";
					$concatenated .= "\t<category>".$_pCategory."</category>\n";
					$concatenated .= "\t<title>".$_pTitle."</title>\n";
					$concatenated .= "\t<author>".$_pAuthor."</author>\n";
					$concatenated .= "\t<details>".$_pDetails."</details>\n";
					$concatenated .= "\t<price>".$_pPrice."</price>\n";
					$concatenated .= "\t<isbn>".$_pIsbn."</isbn>\n";
					$concatenated .= "\t<tags>".$_pTags."</tags>\n";
					$concatenated .= "\t<username>".$_pUsername."</username>\n";
					$concatenated .= "\t<date>".$_pDate."</date>\n";
					$concatenated .= "\t<views>".$_pViews."</views>\n";
				$concatenated .= "</post>";
				return $concatenated;
			}
		}
	}
}
?>