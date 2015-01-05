<?php
require_once("../framework2/Walkntrade.php");
class DataMigration extends Walkntrade{

	public function __construct(){
		parent::__construct();
	}

	public function genThreadIndices(){
		if(!$stmt = $this->getUserConnection()->prepare("SELECT id FROM `users`;")){
			echo "prepare failed";
			return;
		}
		$stmt->execute();
		$stmt->bind_result($uid);
		while($stmt->fetch()){
			if($createInboxSTMT = $this->getThread_indexConnection()->prepare("
					CREATE TABLE `wtonline_thread_index`.`$uid` (
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
		}	
		echo "generated thread indices for each user!";
	}
}

$dm = new DataMigration();
$dm->genThreadIndices();
?>