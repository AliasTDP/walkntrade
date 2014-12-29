<?php
require_once "CredentialStore.php";

class PostQuery extends CredentialStore {
	
	public function __construct(){
		parent::__construct();
	}

	public function addPost($category, $school, $title, $author, $details, $price, $location, $tags, $isbn){
		if($this->getLoginStatus()){
			if ($stmt = $this->getListingConnection()->prepare("INSERT INTO `".$school."`(identifier, category, title, author, details, price, location, tags, isbn, userid, username, date) VALUES(?,?,?,?,?,round(?,2),?,?,?,?,?,?)")){
				$identifierString = substr(md5(rand()), 0, 32); 
				$date = date('Y-m-d');
				$stmt->bind_param('ssssssssiiss', $identifierString, $category, $title, $author, $details, $price, $location, $tags, $isbn, $_SESSION['user_id'], $_SESSION["username"],  date("Y-m-d"));
				$stmt->execute();
				if($stmt->affected_rows == 1){
					$stmt->close();
					//return identifier to client.
					return($identifierString);
				}
				else{
					$stmt->close();
					return(4);
				}
			}
			else{
				//SQL error
				$err = $stmt->error;
				$stmt->close();
				//table not updated
				return($err);
			}
		}
		else{
			return 1;
		}
	}

	public function editPost($title, $details, $price, $tags, $identifier, $school){
		if(strlen($tags) > 500 || strlen($tags) < 2)
			return $this->statusDump(401, "Don't forget about the tags, and remember to keep them less than 500 characters!", null);
		if(strlen($title) > 150 || strlen($title) < 2)
			return $this->statusDump(401, "Don't forget about the title, and remember to keep it less than 150 characters!", null);
		if(strlen($details) > 3000 || strlen($details) < 5)
			return $this->statusDump(401, "Don't forget about the details, and remember to keep it less than 3000 characters!", null);
		if($this->getLoginStatus()){
			if($stmt = $this->getListingConnection()->prepare("SELECT username FROM `".$school."` WHERE  identifier = ? LIMIT 1")){
					$stmt->bind_param("s", $identifier);
					$stmt->execute();
					$stmt->store_result();
					$stmt->bind_result($userNm);
					if($stmt->num_rows == 1){
						$stmt->fetch();
						if($_SESSION["username"] != $userNm){
							$stmt->close();
							return $this->statusDump(401, "Not authorized", null);
						}
						else{
							$stmt->close();
						}
					}
					else{
						$stmt->close();
						return $this->statusDump(401, "Unable to validate user", null);
					}
				}
				else{
					return $stmt->error;
				}
			if($school != null){
				if($updateSTMT = $this->getListingConnection()->prepare("UPDATE `".$school."` SET `title` = ?, `details` = ?, `price` = ?, `tags` = ? WHERE (`identifier` = ? AND `userid` = ?) ")){
					$updateSTMT->bind_param("ssssss", $title, $details, $price, $tags, $identifier, $_SESSION["user_id"]);
					$updateSTMT->execute();
					if($updateSTMT->affected_rows == 1){
						return $this->statusDump(200, "post has been updated", null);
					}
					else{
						return $this->statusDump(200, "No changes have been made", null);
					}
				}
				else{
					return $this->statusDump(500, $updateSTMT->error, null);
				}

			}
			else{
			//school not set
				return $this->statusDump(401, "Request malformed (no school)", null);
			}
		}
		else{
			return $this->statusDump(401, "Not authorized", null);
		}
	}

	public function removePost($identifier, $school){
		if($this->getLoginStatus()){
			$lc = $this->getListingConnection();
			if($d = $lc->prepare("DELETE FROM `".$school."` WHERE `identifier` = ? AND `userid` = ? LIMIT 1")){
				$d->bind_param("si", $identifier, $_SESSION["user_id"]);
				$d->execute();

				for($i=0;$i<4;$i++){
					if($i == 0){
						if(file_exists("../post_images/".$school."/".$identifier."-thumb.jpeg"))
						unlink("../post_images/".$school."/".$identifier."-thumb.jpeg");
					}
					if(file_exists("../post_images/".$school."/".$identifier."-".$i.".jpeg"))
						unlink("../post_images/".$school."/".$identifier."-".$i.".jpeg");
				}
				if($d->affected_rows == 1){
					$d->close();
				//success
					return 0;
				}
				else{
					$d->close();
				//table not updated
					return 3;
				}
			}
			else{
			//SQL error
				return $d->error;
			}
		}
		else{
			return 1;
		}
	}

	public function renewPost($identifier, $school){
		if($this->getLoginStatus()){
			$lc = $this->getListingConnection();
			if($d = $lc->prepare("UPDATE `".$school."` SET `expired`=0,expire=-1,date = ? WHERE `identifier` = ? AND `userid` = ?  LIMIT 1")){
				$d->bind_param("ssi", date("Y-m-d"), $identifier, $_SESSION["user_id"]);
				$d->execute();
				return 0;
			}
			else{
			//SQL error
				return $d->error;
			}
		}
		else{
			return 1;
		}
	}
}
?>