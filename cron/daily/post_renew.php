<?php
require_once "../../framework/CredentialStore.php";
class PostRenew extends CredentialStore {

	public function traverseDB(){
		$schs = $this->getlistingConnection()->prepare("SELECT `textId` FROM `schools`");
		$schs->execute();
		$schs->store_result();
		$schs->bind_result($school);
		while($schs->fetch()){
			$mypost = $this->getlistingConnection()->prepare("SELECT `id`, `title`, `userid`, `date`, `expire` FROM `".$school."`");
			$mypost->execute();
			$mypost->store_result();
			$mypost->bind_result($pId, $pTitle, $pUserid, $date, $postExpireInt);
			while($mypost->fetch()){
				//action here
				if($postExpireInt == -1){ //if the post is not marked for deletion
					$postAge = $this->getAgeInDays($date); //determine the age of the post
					if($postAge >= 30){
						$expireSTMT = $this->getlistingConnection()->prepare("UPDATE `".$school."` SET `expire` = 3 WHERE `id` =  ?");//if it's over a month old prepare SQL query to mark for 3 day deletion
						$expireSTMT->bind_param("i", $pId);
						$expireSTMT->execute();

						$getEmailSTMT = $this->getUserConnection()->prepare("SELECT `email` FROM `users` WHERE `id` = ?");
						$getEmailSTMT->bind_param("s", $pUserid);
						$getEmailSTMT->execute();
						$getEmailSTMT->store_result();
						$getEmailSTMT->bind_result($email);
						$getEmailSTMT->fetch();

						$this->alertViaEmail($pTitle, $email);
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

	private function alertViaEmail($postTitle, $email){
		$subject = "Your post is about to expire";
		$string = '
		<img src="http://walkntrade.com/colorful/wtlogo_dark.png">
		<h1>Please renew your post:"'.$postTitle.'"</h1>
		<p>
			Here at Walkntrade we have a 30 day post renewal policy. Please log into your account at walkntrade.com and view the "My Posts" tab in account settings. From there you can choose to renew your post if it\'s still available, but please hurry, as your post will expire in 3 days if you don\'t renew it.
		</p>
		<p>
			This is done in an effort to keep Walkntrade relevant and useful to you. Thanks for your time, and as always thank you for using walkntrade.
		</p>
		</p>
		';
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: <no-reply@walkntrade.com>' . "\r\n";
		if(mail($email, $subject, $string, $headers)){
			echo "Emailed ".$email."\n";
			return 0;
		}
	}
}

$wt = new PostRenew();
$wt->traverseDB();

?>