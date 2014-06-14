<div id="newMessageWrapper">
</div>
<div id="leftHead">
	<a href="./"><img id="logo" src="/colorful/wtlogo.png"></a>
</div>
<div id="rightHead">
	<script type="text/javascript">

	function DropDown(el) {
		this.dd = el;
		this.initEvents();
	}
	DropDown.prototype = {
		initEvents : function() {
			var obj = this;

			obj.dd.on('click', function(event){
				$(this).toggleClass('active');
				event.stopPropagation();
			});	
		}
	}

	$(function() {

		var dd = new DropDown( $('#dd') );

		$(document).click(function() {
					// all dropdowns
					$('.wrapper-dropdown-5').removeClass('active');
				});
	});
	$(document).ready(function(){
		pollNewMessages();
	});
	</script>
<?php
	if ($loggedIn){
		if(file_exists("../../user_images/uid_".$_SESSION["user_id"].".jpg") || file_exists("user_images/uid_".$_SESSION["user_id"].".jpg")){
			$image = '<img src="/user_images/uid_'.$_SESSION["user_id"].'.jpg">';
		}
		else{
			$image = '<img src="/colorful/avatar.gif"/>';
		}
		echo('<div id="dd" class="wrapper-dropdown-5" tabindex="1">'.$image . ' ' . $_SESSION["username"].'
		<ul class="dropdown">
		    <li><a href="/addBook"><i class="sprite sprite-1396344657_vallet"></i>Post Textbooks</a></li>
		    <li><a href="/addService"><i class="sprite sprite-1396344746_truck"></i>Post Services</a></li>
		    <li><a href="/addElectronics"><i class="sprite sprite-1396344673_phone"></i>Post Electronics/Games</a></li>
			<li><a href="/addMisc"><i class="sprite sprite-1396345059_stack"></i>Post Miscellaneous</a></li>
		    <li><a href="/user_settings"><i class="sprite sprite-1396343119_params"></i>My Account</a></li>
		    <li><a href="javascript:user_logout()"><i class="sprite sprite-1396344419_lock"></i>Log out</a></li>
		    <li><a href="javascript:changeSchools()"><i class="sprite sprite-1396344637_search"></i>Change School</a></li>
		</ul>
		</div>');
	}
	else {
		if(!$noLogin)
			echo('<a href="javascript:createLoginWindow()">Log in</a> | <a href="/signup">Sign up</a> | <a href="javascript:changeSchools()">Change School</a></span>');
	}
?>
</div>