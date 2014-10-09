<?php
	if ($loggedIn){
		if(file_exists("../../user_images/uid_".$_SESSION["user_id"].".jpg")){
			$image = '\'/user_images/uid_'.$_SESSION["user_id"].'.jpg\'';
		}
		else{
			$image = '\'/colorful/avatar.gif\'';
		}
		echo('<ul class="dropdown">
		<li id="userbadge"><div id="avatar" style="background:url('.$image.');background-size: 100%;background-repeat: no-repeat;background-position-y: 50%;"></div><div id="username">'.$_SESSION["username"].'</div></li>
	    <li class="clickOption" style="background:rgb(206, 230, 141);"><a href="/addBook"><i class="sprite sprite-1396344657_vallet"></i>Post Textbooks</a></li>
	    <li class="clickOption" style="background:rgb(228, 151, 58);"><a href="/addService"><i class="sprite sprite-1396344746_truck"></i>Post Services</a></li>
	    <li class="clickOption" style="background:rgb(167, 141, 213);"><a href="/addElectronics"><i class="sprite sprite-1396344673_phone"></i>Post Electronics/Games</a></li>
		<li class="clickOption" style="background:rgb(68, 211, 216);"><a href="/addMisc"><i class="sprite sprite-1396345059_stack"></i>Post Miscellaneous</a></li>
	    <li class="clickOption" style="background:rgb(218, 218, 218);><a href="/user_settings"><i class="sprite sprite-1396343119_params"></i>My Account</a></li>
	    <li class="clickOption" style="background:rgb(117, 117, 117);><a href="javascript:user_logout()"><i class="sprite sprite-1396344419_lock"></i>Log out</a></li>
	    <li class="clickOption" style="background:rgb(43, 43, 43);><a href="javascript:changeSchools()"><i class="sprite sprite-1396344637_search"></i>Change School</a></li>
	</ul>');
	}
	else {
		
	}
?>