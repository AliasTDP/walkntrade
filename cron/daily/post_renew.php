<?php
require_once "../../framework/CredentialStore.php";
class PostRenew extends CredentialStore {

	public function traverseDB(){
		$schs = $this->getlistingConnection()->prepare("SELECT `textId` FROM `schools`");
		$schs->execute();
		$schs->store_result();
		$schs->bind_result($school);
		while($schs->fetch()){
			$mypost = $this->getlistingConnection()->prepare("SELECT `id`, `date`, `expire` FROM `".$school."`");
			$mypost->execute();
			$mypost->store_result();
			$mypost->bind_result($pId, $date, $postExpireInt);
			while($mypost->fetch()){
				//action here
				if($postExpireInt == -1){ //if the post is not marked for deletion
					$postAge = $this->getAgeInDays($date); //determine the age of the post
					if($postAge >= 30){
						$expireSTMT = $this->getlistingConnection()->prepare("UPDATE `".$school."` SET `expire` = 3 WHERE `id` =  ?");//if it's over a month old prepare SQL query to mark for 3 day deletion
						$expireSTMT->bind_param("i", $pId);
						$expireSTMT->execute();
					}
				}
				else{ //it the post is marked for deletion
					if($postExpireInt == 0){ //if the post's warning period is over
						$expireSTMT = $this->getlistingConnection()->prepare("UPDATE `".$school."` SET `expired` =  1 WHERE `id` =  ?"); //post is expired now
						$expireSTMT->bind_param("i", $pId);
						$expireSTMT->execute();
					}
					else{
						$expireSTMT = $this->getlistingConnection()->prepare("UPDATE `".$school."` SET `expire` = `expire` - 1 WHERE `id` =  ?"); //otherwise deincrement the posts remaining time to live
						$expireSTMT->bind_param("i", $pId);
						$expireSTMT->execute();
					}
				}
			}
		}
		$mypost->close();
		$schs->close();
	}

	private function getAgeInDays($date_string){
		#date format YYYY-MM-DD
		$date1 = new DateTime($date_string);
		$date2 = new DateTime(date("Y-m-d"));

		$diff = $date2->diff($date1)->format("%a");
		return $diff;
	}
}

$wt = new PostRenew();
$wt->traverseDB();

?>