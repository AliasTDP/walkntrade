var api_url = "/api/";

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

function user_logout(){
	$.ajax({url:api_url, 
		dataType:"html", 
		type:"POST", 
		data:"intent=logout"
		}).success(function(r){
			window.location="/";
	});
}

function createLoginWindow(){
	$("body").prepend('<div id="screen"></div>');
	$("#screen").css("display", "none");
	$("#screen").load("/include/login_window.html");
	$("#screen").fadeIn(function(){
		$("#emailBox").focus();
		$("body").keydown(function(e){
			if(e.keyCode==27)
				destroyLoginWindow();
		});
		$("#screen").click(function(event){
			if(event.target != this) return;
			destroyLoginWindow();
		})
		$('body').keypress(function(e){
			if(e.which == 27){
				destroyLoginWindow();
			}
		});
	});
}

function destroyLoginWindow(){
	$("#screen").fadeOut(function(){
		$("#screen").remove();
	});
}

function resetPassword(){
	dialog("<h1>Enter your email address</h1>\
		<p>We will send you a temporary password to your email.</p>\
		<form name ='passwordResetForm' action='javascript:commitPwReset()'>\
		<p>\
		<input type='text' name='email' placeholder='johndoe@example.com'>\
		</p>\
		<p>\
		<input type='submit' class='button'  value='Ok!'>\
		</p>\
		</form>",false);
}

function commitPwReset(val){
	var email = document.passwordResetForm.email.value;
	if(validateEmail(email)){
		$.ajax({url:api_url, 
			dataType:"html", 
			type:"POST", 
			data:"intent=resetPassword&email="+email
			}).success(function(r){
				dialog(r,true);
		})
	}
}

function user_login(){
	var uname = document.login.username.value;
	var pword = document.login.password.value;
	var checkBox = document.login.rememberMe.checked;

	if (uname == "") {
		$("#response").html("<font color=\"#FF0000\">Please enter an email address.</font>");
		return;
	}

	if (uname != ""){
		if (validateEmail(uname)){
			$("#response").html("");
		}
		else{
			$("#response").html("<font color=\"#FF0000\">Please enter a valid email address.</font>");
			return;
		}
	}

	if (pword == "") {
		$("#response").html("<font color=\"#FF0000\">Please enter a password.</font>");
		return;
	}
	else if (pword != ""){
		$("#response").html("");
	}
	$.ajax({url:api_url, 
		dataType:"html", 
		type:"POST", 
		data: "intent=login&email="+uname+"&password="+pword+"&rememberMe="+checkBox
		}).success(function(r){
		switch(r){
			case("success"):
			window.location = "/user_settings";
			break;
			case("reset"):
			dialog("<p><h1>It seems like you're having trouble</h1> If you forgot your password click the link below to reset it.</p><p><a href='javascript:resetPassword()'>Send me a new password!</a></p>");
			break;
			case("verify"):
			dialog('<form name="code" method="GET" action="/validateKey">\
				<p>\
				<h1> Oops, your account isn\'t verified yet</h1>\
				<p>We\'ve sent you an email containing activation instructions. Don\'t forget to check your spam folder! </p>\
				<p>You may enter your verification code here if you wish, or simply click the link in the email</p>\
				<br>\
				<input name="token" placeholder="xxxxxx" maxlength="6" class="codeInput" style="width:6em"></p>\
				</p>\
				<p>\
				<input type="submit" class="button" style="color:#FFF" value="OK">\
				</p>\
				</form>', false);
			break;
			default:
			$("#response").html("<font color=\"#FF0000\">"+r+"</font>");
			break;
		}
	});
}