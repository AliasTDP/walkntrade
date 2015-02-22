<?php
require_once "framework2/CredentialStore.php";

$cs = new CredentialStore();
$serverName = basename($_SERVER["SERVER_NAME"]);
$serverURI = $_SERVER['REQUEST_URI'];

if (!in_array($serverName, $cs->getValidDomains())) {
    header( 'Location: https://walkntrade.com'.$serverURI, true, 301 );
}

$schoolTextId = $cs->cookieCheck("sPref");
if ($cs->getSchoolName($schoolTextId) === null) {
    include("selector.php");
} else {
    $query = (isset($_GET["query"])) ? $_GET["query"] : "";
    header('Location: /schools/'.$schoolTextId.'?query='.$query);
}
?>
