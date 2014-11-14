<?php
$query = (isset($_GET["query"])) ? $_GET["query"] : "";
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$schoolTextId = $cs->cookieCheck("sPref");
if($cs->getSchoolName($schoolTextId) == null){
	//if mobile
	//redirect to mobile selector page
	$needles = array("Android", "iPhone", "iPad", "Windows Phone");
	foreach ($needles as $needle) {
		if(strpos($_SERVER["HTTP_USER_AGENT"], $needle) !== false){
			include("mobile/index.html");
			return;
		}
	}
	//otherwise
	//include normal selector
	include("selector.php");
}
else{
	header('Location: /schools/'.$schoolTextId.'?query='.$query);
}

?>