<?php
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$schoolTextId = $cs->cookieCheck("sPref");
if($cs->getSchoolName($schoolTextId) == null){
	include("selector2.php");
}
else{
	header('Location: /schools/'.$schoolTextId);
}

?>