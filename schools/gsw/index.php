<?php
require_once "../../framework2/CredentialStore.php";
$cs = new CredentialStore();
$schoolTextId = basename(getcwd());
$loggedIn = $cs->getLoginStatus();
define('ROOTPATH', "../../");
include("../../results.php");
?>