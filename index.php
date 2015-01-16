<?php
require_once "framework2/CredentialStore.php";

$serverName = basename($_SERVER["SERVER_NAME"]);
$serverURI = $_SERVER['REQUEST_URI'];
$validDomains = array("walkntrade.com", "dev.wt", "50.147.246.201", "172.16.10.71");

if (!in_array($serverName, $validDomains, true)) {
    header('Location: https://walkntrade.com'.$serverURI, true, 301);
}

$cs = new CredentialStore();
$schoolTextId = $cs->cookieCheck("sPref");
if ($cs->getSchoolName($schoolTextId) === null) {
    include("selector.php");
} else {
    $query = (isset($_GET["query"])) ? $_GET["query"] : "";
    header('Location: /schools/'.$schoolTextId.'?query='.$query);
}
?>
