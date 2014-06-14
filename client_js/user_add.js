var api_url = "/api/";
window.no_user_error = true;

function checkUname(uname){
	$.ajax({url:api_url, 
		dataType:"html", 
		type:"POST", 
		data:"intent=checkUsername&username=" + uname
		}).success(function(r){
		if(r == 0){
			document.getElementById('1Err').innerHTML = "";
			window.no_user_error = true;
		}
		else{
			document.getElementById('1Err').innerHTML = '*'+uname+' is taken.';
			window.no_user_error = false;
		}
	});
}

function user_add(){
	var uname = document.useradd.usernameSignup.value;
	var email = document.useradd.emailSignup.value;
	var phone = document.useradd.phoneSignup.value;
	var pword = document.useradd.passwordSignup.value;
	var pword2 = document.useradd.password2Signup.value;

	//-------------------------------------username verification
	if (uname != ""){
		checkUname(uname);
		if(uname.length < 5 || uname.length > 20){
			$("#1Err").html("*Username must be between 5 and 20 characters.");
			window.no_user_error = false;
		}
		else if(uname.match(/\s/g)){
			$("#1Err").html("*Username may not contain spaces.");
			window.no_user_error = false;
		}
		else{
			$("#1Err").html("");
		}
	}
	else{
		$("#1Err").html("*Please enter a username.");
		window.no_user_error = false;
	}
	//----------------------------------------email verification
	if (email != ""){
		if (!validateEmail(email)){
			window.no_user_error = false;
			$("#2Err").html("*Please enter a valid email address.");
		}
		else{
			$("#2Err").html("");
		}
	}
	else{
		$("#2Err").html("*Please enter an email address.");
		window.no_user_error = false;
	}

	//----------------------------------------phone verification
	phone = phone.replace(/[^0-9.]/g, "");

	if (phone != ""){
		if(phone.length != 10){
			$("#phoneErr").html("Please enter a valid phone number.");
			window.no_user_error = false;
		}
		else{
			$("#phoneErr").html("");
		}
	}
	else{
		$("#phoneErr").html("");
		phone = 0;
	}

	//-------------------------------------password verification
	if (pword != ""){
		if (pword.length < 8) {
			window.no_user_error = false;
			$("#3Err").html("*password must be at least 8 characters.");
		}

		else {
			$("#3Err").html("");
		}
	}
	else{
		window.no_user_error = false;
		$("#3Err").html("*Please enter a password.");
	}
	
	if (pword != pword2){
		window.no_user_error = false;
		$("#4Err").html("*Passwords do not match.");
	} 
	else{
		$("#4Err").html("");
	}

	if(window.no_user_error){
		$.ajax({url:api_url, 
			dataType:"html", 
			type:"POST", 
			data:"intent=addUser&username="+uname+"&email="+email+"&password="+pword+"&phone="+phone
			}).success(function(r){
			switch(r){
				case("0"):
				window.location = "/validateKey";
				break;
				case("3"):
				$("#2Err").html("This email address is in use!");
				break;
				default:
				dialog(r);
				window.location("./");
				break;
			}
		});
	}
	else window.no_user_error = true;
}