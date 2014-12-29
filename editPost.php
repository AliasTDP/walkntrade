<?php
require_once "framework/CredentialStore.php";
$cs = new CredentialStore();
$loggedIn = $cs->getLoginStatus();
//get page args
$args=array_keys($_GET);
$args = split(":", $args[0]);
$identifier = $args[1];
$schoolTextId = $args[0];

if($cs->getSchoolName($schoolTextId) == null || !$loggedIn){
 	return;
}

if($identifier != null){
	if($editSTMT = $cs->getListingConnection()->prepare("SELECT id, title, details, price, tags FROM ".$schoolTextId." WHERE identifier = ? LIMIT 1")){
		$editSTMT->bind_param("s", $identifier);
		$editSTMT->execute(); // Execute the prepared query.
		$editSTMT->store_result();
		$editSTMT->bind_result($id, $title, $details, $price, $tags);
		$editSTMT->fetch();

		if($editSTMT->num_rows < 1){
			header('Location: ./');
		}

		$title = htmlspecialchars($title);
		$price = ($price != 0)? "$".round($price, 2) : "(no price)";
		$details = htmlspecialchars($details);
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo(filter_var($title, FILTER_SANITIZE_SPECIAL_CHARS)) ?></title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link rel="shortcut icon" href="http://www.walkntrade.com/favicon.ico?v=2" />
	<script type="text/javascript">
	function formatPrice(element){
		value = element.value;
		if(value != ""){
			if(value.charAt(0) != "$"){
				element.value = "$"+value,replace(/[^0-9.]/g, "");
			}
			element.value=value.replace(/[^0-9.$]/g, "");
		}
	}
	function submit(){
		window.api_url = "/api/";
		window.api_url2 = "/api2/";
		$.ajaxSetup({url:api_url2, dataType:"json", type:"POST"});

		var intent = "editPost";
		var school = "<?php echo $schoolTextId; ?>";
		var title = document.editForm.title.value;
		var details = document.editForm.details.value;
		var price = document.editForm.price.value;
		var identifier = document.editForm.identifier.value;
		var tags = document.editForm.tags.value;

		user = confirm("Are you sure you want to commit these changes?");
		if(user){
			$.ajax({data:"intent="+intent+"&school="+school+"&title="+title+"&details="+details+"&price="+price+"&identifier="+identifier+"&tags="+tags}).success(function(json){
				if(json.status == 200){
					alert(json.message);
					window.close();
				}
				else{
					alert("An error has occured ("+json.message+")");
				}
			})
		}
	}
	</script>
</head>
<body>
	<div style="width:450px;margin:auto">
		<form name="editForm" method="POST" action="javascript:submit()">
			<input type="hidden" name="identifier" value="<?php echo $identifier ?>">
			<fieldset>
				<legend>Edit Title</legend>
				<input type="text" class="inputField2" name="title" value="<?php echo $title ?>">
			</fieldset>
			<fieldset>
				<legend>Edit Details</legend>
				<textarea class="inputField2" style="resize:none;height:150px" name="details"><?php echo $details ?></textarea>
			</fieldset>
			<fieldset>
				<legend>Edit Price, tags</legend>
				<input type="text" class="inputField2" name="price" value="<?php echo $price ?>" onclick="this.value=''" onkeydown="javascript:formatPrice(this)" onkeyup="javascript:formatPrice(this)">
				<input type="text" class="inputField2" name="tags" value="<?php echo $tags ?>">
			</fieldset>
			<br><br>
			<input type="submit" value="Save Changes" style="margin:0px 22%">
		</form>
	</div>
</body>
</html>
<script type="text/javascript" src="/client_js/jquery.min.js"></script>