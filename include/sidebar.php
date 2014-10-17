<?php
	if ($loggedIn){
		if(file_exists("../../user_images/uid_".$_SESSION["user_id"].".jpg") || file_exists("user_images/uid_".$_SESSION["user_id"].".jpg")){
			$image = '\'/user_images/uid_'.$_SESSION["user_id"].'.jpg\'';
		}
		else{
			$image = '\'/colorful/Anonymous_User.jpg\'';
		}
		echo('<ul>
		<li id="userbadge"><div id="avatar" style="background:url('.$image.');background-size: 100%;background-repeat: no-repeat;background-position-y: 50%;"></div><div id="username">'.$_SESSION["username"].'</div>
		<div id="userBadgeMyAccount"><a href="/user_settings">My Account</a><br><a href="javascript:user_logout()">Log out</a></div></li>
		<li class="clickOption" style="" onclick="javascript:window.location=\'/\'"><div id="option0"><i class="sprite sprite-1396343029_shop"></i></div>Home</li>
		<li class="clickOption" style="" onclick="javascript:changeSchools()"><div id="option5"><i class="sprite sprite-1396344637_search"></i></div>Change School</li>
	    <li class="clickOption" style="" onclick="javascript:window.location=\'/addBook\'"><div id="option1"><i class="sprite sprite-1396344657_vallet"></i></div>Post Textbooks</li>
	    <li class="clickOption" style="" onclick="javascript:window.location=\'/addHousing\'"><div id="option2"><i class="sprite sprite-1396344746_truck"></i></div>Post Housing</li>
	    <li class="clickOption" style="" onclick="javascript:window.location=\'/addElectronics\'"><div id="option3"><i class="sprite sprite-1396344673_phone"></i></div>Post Tech/Games</li>
		<li class="clickOption" style="" onclick="javascript:window.location=\'/addMisc\'"><div id="option4"><i class="sprite sprite-1396345059_stack"></i></div>Post Miscellaneous</li>
	</ul>');
	}
	else {
		echo('<ul>
		<li id="loggedOutLogin" style="" onclick="javascript:createLoginWindow()">Login</li>
		<li id="loggedOutSignup" style="" onclick="javascript:window.location=\'/signup\'">Signup</li>
	    <li class="clickOption" style="" onclick="javascript:changeSchools()"><div id="option5"><i class="sprite sprite-1396344637_search"></i></div>Change School</li>
		<li class="clickOption unavailable" style=""><div id="option1"><i class="sprite sprite-1396344657_vallet"></i></div>Post Textbooks</li>
	    <li class="clickOption unavailable" style=""><div id="option2"><i class="sprite sprite-1396344746_truck"></i></div>Post Housing</li>
	    <li class="clickOption unavailable" style=""><div id="option3"><i class="sprite sprite-1396344673_phone"></i></div>Post Tech/Games</li>
		<li class="clickOption unavailable" style=""><div id="option4"><i class="sprite sprite-1396345059_stack"></i></div>Post Miscellaneous</li>
	</ul>');
	}
?>