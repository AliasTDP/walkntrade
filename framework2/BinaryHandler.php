<?php
require_once "UserMgmt.php";
class BinaryHandler extends UserMgmt{
	public function __construct(){
		parent::__construct();
	}

	public function uploadAvatar($binImage){
		if($this->getLoginStatus()){
			if($binImage['error'] == 0){
				if($binImage["size"] < 1024288){
					if($binImage["type"] == "image/jpeg"){
						$uploaddir = '../user_images/';
						$uploadFileName = $uploaddir . "uid_".$_SESSION['user_id'] . ".jpg";
						if($this->scaleImage($_FILES["avatar"]["tmp_name"], $uploadFileName, 300, 300, false)){
							echo 0;
						}
					}
					else{
						echo "only jpeg images allowed.";
					}
				}
				else{
					echo "Filesize too large";
				}
			}
			else{
				echo "error processing file:<br>\n";
				switch($binImage['error']){
					case(1):
					echo("The uploaded file is too large [SRV]");
					break;
					case(2):
					echo("The uploaded file is too large [HTML]");
					break;
					case(3):
					echo("The file was only partially uploaded.");
					break;
					case(4):
					echo("No file was uploaded.");
					break;
					case(7):
					echo("Failed to write file to disk.");
					break;
					case(8):
					echo("Extension stopped the file upload");
					break;
				}
			}
		}
		else{
			echo "not LoggedIn";
		}
	}

	public function uploadPostImages($binImage, $iteration, $identifier, $school){
		if($this->getLoginStatus()){
			$uploadDir = "../post_images/".$school."/";
			$uploadfile = $uploadDir.$identifier."-".$iteration.".jpeg";
			if($iteration == 0){
				$thumbnail = $uploadDir.$identifier."-thumb.jpeg";
				if(!$this->scaleImage($binImage["tmp_name"], $thumbnail, 300, 300, true)){
					return 15;
				}
			}
			if($this->scaleImage($binImage["tmp_name"], $uploadfile, 1000, 1000, true)){
				echo('0');
			}
			else{
				echo("no file recieved");
			}

		}
		else{
			echo "Not Authorized!";
		}
	}

	public function getAvatar(){
		if ($this->getLoginStatus()){
			$userid = $_SESSION["user_id"];
			if(file_exists("../user_images/uid_".$userid.".jpg"))
				return("/user_images/uid_".$userid.".jpg");
			else
				return("/colorful/Anonymous_User.jpg");
		}
		else{
			return null;
		}
	}
	private function scaleImage($sourcePath, $destPath, $iDestWidth, $iDestHeight , $proportional){
		$imagefile = imagecreatefromjpeg($sourcePath);
		$iSourceWidth = imagesx($imagefile);
		$iSourceHeight = imagesy($imagefile);
		if($proportional){
			if($iSourceWidth > $iSourceHeight){
				$r = $iSourceHeight / $iSourceWidth;
				$iDestHeight = $iDestHeight * $r;
			}
			elseif($iSourceHeight > $iSourceWidth){
				$r = $iSourceWidth/ $iSourceHeight;
				$iDestWidth = $iDestWidth * $r;
			}
		}
		$destination = imagecreatetruecolor($iDestWidth, $iDestHeight);
		imagecopyresampled($destination, $imagefile, 0, 0, 0, 0, $iDestWidth, $iDestHeight, $iSourceWidth, $iSourceHeight);

		if(imagejpeg($destination, $destPath, 100)){
			return true;
		}
	}
}
?>