<?php
	if ($loggedIn){
		if(file_exists("../../user_images/uid_".$_SESSION["user_id"].".jpg")){
			$image = '\'/user_images/uid_'.$_SESSION["user_id"].'.jpg\'';
		}
		else{
			$image = '\'/colorful/avatar.gif\'';
		}
		echo('<ul class="dropdown">
		<li id="userbadge"><div id="avatar" style="background:url('.$image.');background-size: 100%;background-repeat: no-repeat;background-position-y: 50%;"></div><div id="username">'.$_SESSION["username"].'</div>
		<div id="userBadgeMyAccount"><a href="/user_settings.php">My Account</a><br><a href="javascript:user_logout()">Log out</a></div></li>
	    <li class="clickOption" style=""><a href="/addBook"><div id="option1"><i class="sprite sprite-1396344657_vallet"></i></div>Post Textbooks</a></li>
	    <li class="clickOption" style=""><a href="/addService"><div id="option2"><i class="sprite sprite-1396344746_truck"></i></div>Post Services</a></li>
	    <li class="clickOption" style=""><a href="/addElectronics"><div id="option3"><i class="sprite sprite-1396344673_phone"></i></div>Post Electronics/Games</a></li>
		<li class="clickOption" style=""><a href="/addMisc"><div id="option4"><i class="sprite sprite-1396345059_stack"></i></div>Post Miscellaneous</a></li>
	    <li class="clickOption" style=""><div id="option5"><a href="javascript:changeSchools()"><i class="sprite sprite-1396344637_search"></i></div>Change School</a></li>
	</ul>');
	}
	else {
		echo('<ul class="dropdown">
		<li class="clickOption" style=""><a href="javascript:createLoginWindow()"><div id="option1"><i class="sprite sprite-1396344657_vallet"></i></div>Login</a></li>
		<li class="clickOption" style=""><a href="/signup.php"><div id="option1"><i class="sprite sprite-1396344657_vallet"></i></div>Signup</a></li>
		<li class="clickOption" style=""><a href="javascript:createLoginWindow()"><div id="option1"><i class="sprite sprite-1396344657_vallet"></i></div>Post Textbooks</a></li>
	    <li class="clickOption" style=""><a href="javascript:createLoginWindow()"><div id="option2"><i class="sprite sprite-1396344746_truck"></i></div>Post Services</a></li>
	    <li class="clickOption" style=""><a href="javascript:createLoginWindow()"><div id="option3"><i class="sprite sprite-1396344673_phone"></i></div>Post Electronics/Games</a></li>
		<li class="clickOption" style=""><a href="javascript:createLoginWindow()"><div id="option4"><i class="sprite sprite-1396345059_stack"></i></div>Post Miscellaneous</a></li>
	    <li class="clickOption" style=""><a href="javascript:changeSchools()"><i class="sprite sprite-1396344637_search"></i>Change School</a></li>
	</ul>');
	}
?>