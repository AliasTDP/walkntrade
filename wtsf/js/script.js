//--------------------dependencies------------------------------
function addChildTo(parentId, childType) {//adds 'childType' element to 'parentId'
	var p = document.getElementById(parentId);
	var c = document.createElement(childType);
	p.appendChild(c);
	return c;
}

function hide(id) { //hides 'id' by setting CSS style to none
	document.getElementById(id).style.display = 'none';
}

function q(mode, address, string, callback) {
	if (window.XMLHttpRequest){
		xhttp = new XMLHttpRequest();
	}
	else {
		xhttp = new ActiveXObject("Microsoft.XMLHTTP");//IE Compatibility
	}
	xhttp.open(mode, address, true);
	xhttp.setRequestHeader("Content-type","application/x-www-form-urlencoded");
	xhttp.onreadystatechange = function(){
		if (xhttp.readyState==4 && xhttp.status==200){
			if (typeof callback == "function")
				callback(xhttp.responseText);
		}
	}
	xhttp.send(string);
}

function show(id) {// shows 'id' by setting CSS style to block
	document.getElementById(id).style.display = 'block';
}

function validateEmail(email){
	var atpos=email.indexOf("@");
	var dotpos=email.lastIndexOf(".");
	if (atpos < 1 || dotpos < atpos+2 || dotpos+2 >= email.length){
		return false;
		}
	if(email.match(/\s/g)){
		return false;
	}
	return true;
}

function setCookie(c_name,value,exdays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value +"; path=/";
}

function showLogin(){
	$("#screen").fadeIn();
	$("#emailBox").focus();
	$("#screen").click(function(event){
		if(event.target != this) return;
		hideLogin();
	})
	$('body').keypress(function(e){
    	if(e.which == 27){
        hideLogin();
    	}
	});
}

function showContact(){
	$("#screen2").fadeIn();
	$("#screen2").click(function(event){
		if(event.target != this) return;
		hideContact();
	})
	$('body').keypress(function(e){
    	if(e.which == 27){
        hideContact();
    	}
	});
}

function hideLogin(){
	$("#screen").fadeOut();
}

function hideContact(){
	$("#screen2").fadeOut();
}


function popup(url) {
	newwindow=window.open(url,'name','height=580,width=510');
	if (window.focus) {newwindow.focus()}
}

function changeSchools(){
	setCookie("sPref", "", "-1");
	window.location = "/";
}

function checkUname(uname){//verify that username is not taken, depends on q(){
	function handleResponse(response){
		if(response == 0){
			noGo = false;
		}
		else{
			alert("This username is taken");
			noGo = true;
		}
	}
	q("POST", "/api/", "intent=checkUsername&username=" + uname, handleResponse);
}



function form_send(){//sends new user info to server, depends on q() and validateEmail()

	email = document.walkntrade.email.value;
	uname = document.walkntrade.username.value;
	phone = document.walkntrade.phone.value;
	pword = document.walkntrade.password.value;
	pword2 = document.walkntrade.password2.value;

	var cont = true;
	var noGo = false;

	//-------------------------------------username verification
	if (uname != ""){
		checkUname(uname);
		if(uname.length < 5 || uname.length > 20){
			alert( "*Username must be between 5 and 20 characters.");
			cont = false;
		}
		else if(uname.match(/\s/g)){
			alert("*Username may not contain spaces.");
			cont = false;
		}
		else{
		}
	}
	else{
		alert("*Please enter a username.");
		cont = false;
	}

	//----------------------------------------email verification
	if (email != ""){
		if (!validateEmail(email)){
			cont = false;
			alert("*Please enter a valid email address.");
		}
		else{
		}
	}
	else{
		alert( "*Please enter an email address.");
		cont = false;
	}

	//----------------------------------------phone verification
	phone = phone.replace(/[^0-9.]/g, "");

	if (phone != ""){
		if(phone.length != 10){
			alert("Please enter a valid phone number.");
			cont = false;
		}
		else{
		}
	}
	else{
		phone = 0;
		// phoneE.innerHTML = "*Please enter an phone number (so buyers can contact you)";
		// cont = false;
	}

	//-------------------------------------password verification
	if (pword != ""){
		if (pword.length < 8) {
			cont = false;
			alert("*password must be at least 8 characters.");
		}

		else {
		}
	}
	else{
		cont = false;
		alert("*Please enter a password.");
	}
	
	if (pword != pword2){
		cont = false;
		alert("*Passwords do not match.");
	} 
	else{
	}
	if(!noGo && cont){
		function handleResponse(r){
			switch(r){
				case("0"):
				alert("Thank You!")
				window.location = "./";
				break;
				case("3"):
				alert("This email address is in use!");
				break;
				default:
				alert(r);
				window.location("./");
				break;
			}
		}
		q("POST", "/api/", "intent=addUser&username="+uname+"&email="+email+"&password="+pword+"&phone="+phone, handleResponse);
	}
}