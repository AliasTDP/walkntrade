<?php
require_once "../../framework/CredentialStore.php";
class PostRenew extends CredentialStore {
	private $emailQueue = array();	
	private $postTitles = array();

	public function traverseDB(){
		$schs = $this->getlistingConnection()->prepare("SELECT `textId` FROM `schools`");
		$schs->execute();
		$schs->store_result();
		$schs->bind_result($school);
		while($schs->fetch()){
			$mypost = $this->getlistingConnection()->prepare("SELECT `id`, `title`, `userid`, `date`, `expire`, `expired` FROM `".$school."`");
			$mypost->execute();
			$mypost->store_result();
			$mypost->bind_result($pId, $pTitle, $pUserid, $date, $postExpireInt, $postExpiredInt);
			while($mypost->fetch()){
				//action here
				if($postExpireInt == -1){ //if the post is not marked for deletion
					$postAge = $this->getAgeInDays($date); //determine the age of the post
					if($postAge >= 60){
						$expireSTMT = $this->getlistingConnection()->prepare("UPDATE `".$school."` SET `expire` = 3 WHERE `id` =  ?");//if it's over 2 months old prepare SQL query to mark for 3 day deletion
						$expireSTMT->bind_param("i", $pId);
						$expireSTMT->execute();
						$email = $this->getEmailFromUserID($pUserid);
						$this->enqueUserEmail($email, $pTitle, "false");
					}
				}
				else{ //it the post is marked for deletion
					if($postExpireInt == 0){ //if the post's warning period is over
						$expireSTMT = $this->getlistingConnection()->prepare("UPDATE `".$school."` SET `expired`=1,`expire`=-2 WHERE `id` =  ?"); //post is expired now
						$expireSTMT->bind_param("i", $pId);
						$expireSTMT->execute();
						$email = $this->getEmailFromUserID($pUserid);
						$this->enqueUserEmail($email, $pTitle, "true");
					}
					elseif($postExpiredInt  == 0){
						$expireSTMT = $this->getlistingConnection()->prepare("UPDATE `".$school."` SET `expire` = `expire` - 1 WHERE `id` =  ?"); //otherwise deincrement the posts remaining time to live
						$expireSTMT->bind_param("i", $pId);
						$expireSTMT->execute();
					}
				}
			}
		}
		return $this->showCompletion();
		$mypost->close();
		$schs->close();
	}

	private function getEmailFromUserID($pUserid){
		$getEmailSTMT = $this->getUserConnection()->prepare("SELECT `email` FROM `users` WHERE `id` = ?");
		$getEmailSTMT->bind_param("s", $pUserid);
		$getEmailSTMT->execute();
		$getEmailSTMT->store_result();
		$getEmailSTMT->bind_result($e);
		$getEmailSTMT->fetch();
		return $e;
	}

	private function enqueUserEmail($email, $pTitle, $expired){
		//echo $email." ".$pTitle." ".$expired."\n";
		$status = ($expired == "true") ? " <font color=\"#FF0000\">[expired]</font>" : "";
		if(!in_array($email, $this->emailQueue)){
			$index = array_push($this->emailQueue, $email);
			$this->postTitles[$index-1]=array($pTitle.$status);
		}
		else{
			$index = array_search($email, $this->emailQueue);
			array_push($this->postTitles[$index], $pTitle.$status);
		}
	}

	private function showCompletion(){
		//var_dump($this->emailQueue);
		//var_dump($this->postTitles);

		foreach ($this->emailQueue as $email) {
			$text = "
			<html>
			<head></head>
			<body bgcolor=\"#FFFFFF\" style=\"-webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; height: 100%; margin-bottom: 0; margin-left: 0; margin-right: 0; margin-top: 0; padding-bottom: 0; padding-left: 0; padding-right: 0; padding-top: 0; width: 100% !important\">
			<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\"><tr>
				<table cellspacing=\"0\" cellpadding=\"8\" border=\"0\" width=\"100%\" style=\"font-size:1.1em\">
					<tr>
						<td width=\"600px\" colspan=\"2\" style=\"background: #3f3f3f;\"><img src=\"http://walkntrade.com/colorful/wtlogo.png\" alt=\"\Walkntrade Logo\"></td>
					</tr>
					<tr>
						<td></td>
					</tr>
					<tr>
						<td  colspan=\"2\" align=\"center\"><h1 style=\"color: #45B407;\">Keep your posts alive!</h1></td>
					</tr>
					<tr>
						<td colspan=\"2\">Here at Walkntrade we have 2 month post renewal policy. We are contacting you because one or more of your posts are expiring or have already expired. To fix this please log into your account and renew the posts that you want to keep listed. Posts expire after three days from their first warning.</td>
					</tr>
					<tr>
						<td colspan=\"2\"><a href=\"http://walkntrade.com/user_settings#3\">Access my account</a></td>
					</tr>
					<tr>
						<td  colspan=\"2\">The following posts are in trouble:</td>
					</tr>";
			$index = array_search($email, $this->emailQueue);
			foreach ($this->postTitles[$index] as $postTitle) {
				$text.="<tr><td style=\"padding:3px\">&#149;</td><td  style=\"padding:3px\"><b>".$postTitle."</b></td></tr>";
			}
			$text.="
			<tr>
				<td colspan=\"2\">This is done as an effort to keep Walkntrade relevant and useful to you. Thanks for your time, and as always thank you for using walkntrade.</td>
			</tr>
			<tr>
				<td></td>
			</tr>
			<tr>
				<td colspan=\"2\" style=\"height: 150px;background: #929292;color: #525252;text-align: center;\"><p><a style=\"color:\#525252\" href=\"/ToS\">Terms of Service</a> &nbsp;&nbsp;|&nbsp;&nbsp; <a href=\"/privacy\" style=\"color:\#525252\">Privacy Policy</a>  &nbsp;&nbsp;|&nbsp;&nbsp; <a href=\"/feedback\" style=\"color:\#525252\">Feedback</a></p></td>
			</tr>
			</table>
			</td></tr></table></body></html>";
			$this->alertViaEmail($text, $email);
		}
	}

	private function alertViaEmail($string, $email){
		$subject = "Your posts are expiring!";
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: Walkntrade <no-reply@walkntrade.com>' . "\r\n";
		if(mail($email, $subject, $string, $headers)){
			echo "Emailed ".$email."\n";
			return 0;
		}
		else
		 	echo "Email ".$email." failed!\n";
	}
}

$wt = new PostRenew();
$wt->traverseDB();

?>