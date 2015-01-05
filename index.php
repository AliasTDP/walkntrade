<?php
$serverName = basename($_SERVER["SERVER_NAME"]);
$serverURI = $_SERVER['REQUEST_URI'];
$validDomains = array("walkntrade.com", "dev.wt", "50.147.246.201", "172.16.10.71");
$redirect = true;
foreach ($validDomains as $domain) {
	if($serverName === $domain){
		$redirect=false;
	}
}
if($redirect)
	header( 'Location: https://walkntrade.com'.$serverURI, true, 301 );

$query = (isset($_GET["query"])) ? $_GET["query"] : "";
require_once "framework2/CredentialStore.php";
$cs = new CredentialStore();
$schoolTextId = $cs->cookieCheck("sPref");
if($cs->getSchoolName($schoolTextId) == null){
	include("selector.php");
}
else{
	header('Location: /schools/'.$schoolTextId.'?query='.$query);
}

?>