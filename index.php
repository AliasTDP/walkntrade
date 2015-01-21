<?php
require_once "framework2/CredentialStore.php";
$cs = new CredentialStore();
$serverName = basename($_SERVER["SERVER_NAME"]);
$serverURI = $_SERVER['REQUEST_URI'];
$redirect = true;
foreach ($cs->getValidDomains() as $domain) {
	if($serverName === $domain){
		$redirect=false;
	}
}
if($redirect)
	header( 'Location: https://walkntrade.com'.$serverURI, true, 301 );
$query = (isset($_GET["query"])) ? $_GET["query"] : "";
$schoolTextId = $cs->cookieCheck("sPref");
if($cs->getSchoolName($schoolTextId) == null){
	include("selector.php");
}
else{
	header('Location: /schools/'.$schoolTextId.'?query='.$query);
}

?>