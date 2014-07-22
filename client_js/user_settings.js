//initialization sector
var includeDir = "/include/user_settings/";
var apiURL = "/api/";
var sections = new Array('<i class="sprite sprite-1396343029_shop"></i>Home', '<i class="sprite sprite-1396343080_mail"></i>Received Messages', '<i class="sprite sprite-1396343166_paperplane"></i>Sent Messages', '<i class="sprite sprite-1396343050_news"></i>Your Posts', '<i class="sprite sprite-1396343908_settings"></i>Account Settings', '<i class="sprite sprite-1396343345_user"></i>Profile Settings', '<i class="sprite sprite-1396343039_like"></i>Contact Preferences');
var cpModule = new Array();
var _preventDefault;
var jumpTo;

window.avatar;
window.username;
$.ajaxSetup({url:apiURL, type:"POST", timeout:5000});

// Pre load all modules and user info before allowing readystate
$.holdReady(true);
for(var i = 0; i < sections.length; i++){
	$.ajax({url: includeDir+i+".html", dataType: "html", type:"GET", context:Array(i, sections.length)}).done(function(r){
		cpModule[this[0]] = r;
		if(this[0] == (this[1] - 1)){
			$.ajax({ data:"intent=getUserName"}).done(function(username){
				$.ajax({ data:"intent=getAvatar"}).done(function(imgSrc){
					window.avatar = new Image();
					window.avatar.src = imgSrc;
					window.username = username;
					$.holdReady(false);//open readystate on last module load
				});
			});
		}
	});
}

//Establishing event listeners and handlers
$(document).ajaxStart(function(){
	document.getElementById("throbber").style.display="block";
});

$(document).ajaxComplete(function(){
	document.getElementById("throbber").style.display="none";
});

$(document).ajaxError(function(){
	dialog("<h2>Hmmm, something's not right here</h2><p>We are having a problem connecting to walkNtrade. Please check your internet connection and try again.</p>", true);
});

$(document).ready(function(){
	$("#userModule").html("<div id=\"avatar\"><img width=\"50px\" height=\"50px\" src=\""+window.avatar.src+"\"></div><div id=\"uName\">"+window.username+"</div>");
	var jumpTo = location.hash.slice(1);
	if(jumpTo!=""){
		$("#contentTab").html(cpModule[jumpTo]);
		var _preventDefault = true;
	}
	else{
		$("#contentTab").html(cpModule[0]);
		var _preventDefault = false;
	}
	layoutRefresh();
	$("#navBar").append("<ul></ul>");
	for(var i = 0; i < sections.length; i++){
		if(i==0 && !_preventDefault)
			$("#navBar ul").append("<li id=\""+i+"\" class=\"selected\" style=\"cursor:pointer\">"+sections[i]+"</li>");
		else if(i==jumpTo && _preventDefault)
			$("#navBar ul").append("<li id=\""+i+"\" class=\"selected\" style=\"cursor:pointer\">"+sections[i]+"</li>");
		else
			$("#navBar ul").append("<li id=\""+i+"\" style=\"cursor:pointer\">"+sections[i]+"</li>");
		$("#"+i).click(function(e){
			$("#navBar ul").find("li").removeAttr("class");
			$("#"+e.target.id).attr("class", "selected");
			$("#contentTab").fadeOut(function(){
				$("#contentTab").html(cpModule[e.target.id]);
				layoutRefresh();
				$("#contentTab").fadeIn(function(){
				});
			});
		});
	}
	$("#screen_solid").fadeOut();
})

//function declarations

function layoutRefresh(){
	$("#navBar").height($("#contentTab").height() + 59);
}

function getWebmail(){
	$("#navBarMail").html("[Waiting for mail]");
	$.ajax({ dataType:"xml", data:"intent=getWebmail"}).success(function(xml){
		var pageElement = $("#webmail");
		pageElement.html("<table cellpadding=\"0\" cellspacing=\"0\"></table>");
		$("#navBarMail").html("["+$(xml).find("message").length+"] messages total | <a href='javascript:getWebmail();pollNewMessages();'>[Refresh]</a>");
		$(xml).find("message").each(function(){
			var id = $(this).attr("id");
			var from = $(this).attr("from");
			var subject = $(this).attr("subject");
			var message = $(this).attr("message");
			var datetime = $(this).attr("datetime");
			var read = ($(this).attr("read") == 0) ? "unread" : "" ;
			pageElement.find("table").append($('<tr/>', {"onclick":"openMessage('"+id+"', '"+read+"')", "id":"msg_"+id, "class":read}));
			$("#msg_"+id).append($('<td/>', {"width": "15%", "class":"sender"}).text(from));
			$("#msg_"+id).append($('<td/>', {"width": "30%"}).text(subject));
			$("#msg_"+id).append($('<td/>', {"width": "40%"}).text(message));
			$("#msg_"+id).append($('<td/>', {"width": "15%"}).text(datetime));
		});
	});
}

function getSentWebmail(){
	$("#navBarMail").html("[Waiting for mail]");
	$.ajax({ dataType:"xml", data:"intent=getSentWebmail"}).success(function(xml){
		var pageElement = $("#webmail");
		pageElement.html("<table cellpadding=\"0\" cellspacing=\"0\"></table>");
		$("#navBarMail").html("["+$(xml).find("message").length+"] messages total | <a href='javascript:getSentWebmail()'>[Refresh]</a>");
		$(xml).find("message").each(function(){
			var id = $(this).attr("id");
			var to = $(this).attr("to");
			var subject = $(this).attr("subject");
			var message = $(this).attr("message");
			var datetime = $(this).attr("datetime");
			pageElement.find("table").append($('<tr/>', {"onclick":"openMessageSent('"+id+"')", "id":"msg_"+id}));
			$("#msg_"+id).append($('<td/>', {"width": "15%", "class":"sender"}).text(to));
			$("#msg_"+id).append($('<td/>', {"width": "30%"}).text(subject));
			$("#msg_"+id).append($('<td/>', {"width": "40%"}).text(message));
			$("#msg_"+id).append($('<td/>', {"width": "15%"}).text(datetime));
		});
	});
}

function openMessage(id, read){
	$.ajax({dataType:"xml",  data:"intent=getMessage&message_id="+id}).success(function(xml){
		if(read == "unread"){
			pollNewMessages();
			getWebmail();
		}
		var _message = $(xml).find("message");
		window.from = _message.attr("from");
		window.subject = _message.attr("subject");
		window.message = _message.attr("message");
		window.datetime = _message.attr("datetime");
			var msg = "<table cellspacing=\"0\" cellpadding=\"0\" class=\"messageView\"><tr><th width=\"20%\">sender::</th><th id=\"fromElement\" width=\"80%\"></th></tr><tr><th>subject::</th><th id=\"subjectElement\"></th></tr><tr><td colspan=\"2\" height=\"150px\"><div id=\"messageWrapper\"></div></td></tr><tr><td>Sent:</td><td>"+datetime+"</th></td><tr><td width=\"50%\"><button class=\"button\" onclick=\"javascript:removeMessage("+id+")\">Delete message</button></td><td width=\"50%\"><button class=\"button\" onclick=\"javascript:replyTo()\">Reply</button></td><tr></table>";
		dialog(msg,false,function(){
			$("#subjectElement").text(window.subject);
			$("#fromElement").text(window.from);
			$("#messageWrapper").text(window.message);
		});
		
	})
}

function openMessageSent(id){
	$.ajax({dataType:"xml",  data:"intent=getMessage&message_id="+id}).success(function(xml){
		var _message = $(xml).find("message");
		window.to = _message.attr("to");
		window.subject = _message.attr("subject");
		window.message = _message.attr("message");
		window.datetime = _message.attr("datetime");
			var msg = "<table cellspacing=\"0\" cellpadding=\"0\" class=\"messageView\"><tr><th width=\"20%\">to::</th><th id=\"toUserElement\" width=\"80%\"></th></tr><tr><th>subject::</th><th id=\"subjectElement\"></th></tr><tr><td colspan=\"2\" height=\"150px\"><div id=\"messageWrapper\"></div></td></tr><tr><td>Sent:</td><td>"+datetime+"</th></td><tr><td colspan=\"2\"><button class=\"button\" onclick=\"javascript:removeMessage("+id+")\">Delete message</button></td><tr></table>";
		dialog(msg,false,function(){
			$("#subjectElement").text(window.subject);
			$("#toUserElement").text(window.to);
			$("#messageWrapper").text(window.message);
		});
		
	})
}

function removeMessage(id){
	if($("#screen").length != 0){
		$("#screen").fadeOut(function(){
			$("#screen").remove();
			act();
		});
	}
	else act();
	function act(){
		dialog("Are you sure you want to delete this?", true, null, function(r){
			if(r){
				$.ajax({dataType:"html",  data:"intent=removeMessage&message_id="+id, context:refreshAll}).success(function(r){
					if(r == 0){
						this();
					}
					else{
						dialog("An error has occured ("+r+").", true, null, window.reload());
					}
				});
			}
			else return;
		})
	}
}

function sendReply(userName){
	title = document.replyForm.subject.value;
	message = document.replyForm.message.value;
	if($("#screen").length != 0){
		$("#screen").fadeOut(function(){
			$("#screen").remove();
			act();
		});
	}
	else act();
	function act(){
		$.ajax({
		dataType:"text", 
		data:"intent=messageUser&userName="+userName+"&title="+title+"&message="+message,
		context:getWebmail
		}
		).success(function(r){
			if(r == "success")
				dialog("Your message has been sent", true, null,  this());
			else
				dialog(r,true, null);
		});
	}
}

function replyTo(){
	dialog('\
		<form name="replyForm" action="javascript:void(0)" onSubmit="sendReply(document.replyForm.recipient.value)">\
		<table id="messageReply">\
		<tr>\
		<th colspan="2">Compose message:</th>\
		</tr>\
		<tr>\
		<td width="20%">To:</td>\
		<td><input name="recipient" id="_recipient" type="text"></td>\
		</tr>\
		<tr>\
		<td width="20%">Subject:</td>\
		<td><input type="text" name="subject" id="_subject"></td>\
		</tr>\
		<tr>\
		<td colspan="2"><textarea name="message"></textarea></td>\
		</tr>\
		<tr>\
		<td colspan="2"><input type="submit" value="Send!"><td>\
		</tr>\
		</table>\
		</form>',false, function(){
			$("#_recipient").val(window.from);
			$("#_subject").val("RE: "+window.subject);
		}, null);
}

function getUserPosts(){
	$("#yourPosts").append($("<h3/>"));
	$("#yourPosts h3").text("Loading...");
	$.ajax({ dataType:"xml", data:"intent=getPostsCurrentUser"}).success(function(xml){
		var contentElement = $("#yourPosts");
		contentElement.html("");
		if($(xml).find("school").length == 0 ){
			$("#yourPosts").append($("<h3/>"));
			$("#yourPosts h3").text("You have nothing to show here yet, but you can post something now!");
		}
		else{
			contentElement.html("");
			contentElement.append($("<table/>", {"cellpadding":"0", "cellspacing":"0"}));
			$(xml).find("school").each(function(){
				var shortName = $(this).attr("shortName");
				var longName = $(this).attr("longName");
				contentElement.find("table").append($("<tr/>", {"id":shortName}));
				contentElement.find("table #"+shortName).append($("<th/>", {"width":"60%", "colspan":"3"})).append($("<th/>", {"width":"30%"})).append($("<th/>", {"width":"10%"}));
				contentElement.find("table #"+shortName+" th:first").text(longName);
				contentElement.find("table #"+shortName+" th:nth-child(2)").text("Date");
				contentElement.find("table #"+shortName+" th:nth-child(3)").text("views");
				var schoolPostsElement = contentElement.find("table");
				$(this).find("post").each(function(){
					console.log($(this));
					var id = $(this).attr("id");
					var link = $(this).attr("link");
					var category = $(this).attr("category");
					var title = $(this).attr("title");
					var date = $(this).attr("date");
					var views = $(this).attr("views");
					var expire = $(this).attr("expire");
					var expired = $(this).attr("expired");
					if(expired == "false"){
						if(expire == -1) schoolPostsElement.append('<tr id="'+link+'" class="'+category+'"><td width="2%"><a href="javascript:deletePost(\''+link+'\')"><i class="sprite sprite-1396379273_86"></i></a></td> <td width="2%"><a href="javascript:popup(\'editPost?'+link+'\')"><i class="sprite sprite-1396379288_90"></i></a></td> <td><a href="show?'+link+'">'+title+'</a></td><td>'+date+'</td><td>'+views+'</td></tr>');
						else schoolPostsElement.append('<tr id="'+link+'" class="'+category+', expiring"><td width="2%"><a href="javascript:deletePost(\''+link+'\')"><i class="sprite sprite-1396379273_86"></i></a></td> <td width="2%"><a href="javascript:popup(\'editPost?'+link+'\')"><i class="sprite sprite-1396379288_90"></i></a></td> <td><a href="show?'+link+'">'+title+'</a></td><td>'+date+'</td><td>'+views+'</td></tr>');
					}
					else schoolPostsElement.append('<tr id="'+link+'" class="'+category+', expired"><td width="2%"><a href="javascript:deletePost(\''+link+'\')"><i class="sprite sprite-1396379273_86"></i></a></td> <td width="2%"></td> <td><a href="show?'+link+'">'+title+'</a></td><td>'+date+'</td><td>'+views+'</td></tr>');
					//$("#p_"+id).find("a :last").text(title);
				});
			});
		}
		function clrAll(){
			$("table .CFExpired, .CFExpiring").remove();
			$("table tr").css({background:"",color:""});
		}

		$(".expired").mouseenter(function(){
			clrAll();
			id=$(this).attr("id");
			$(this).css({background:"#FF3D3D",color:"#FFFFFF"});
			if($(this).next().attr("class") != "CFExpired")
				$('<tr class="CFExpired"><td colspan="5">This post has expired. Click <a href="javascript:renewPost(\''+id+'\')">here</a> to restore it.</td></tr>').insertAfter($(this));
		})

		$(".expiring").mouseenter(function(){
			clrAll();
			id=$(this).attr("id");
			$(this).css({background:"#FFBA42",color:"#FFFFFF"});
			if($(this).next().attr("class") != "CFExpiring")
				$('<tr class="CFExpiring"><td colspan="5">This post will expire soon. Click <a href="javascript:renewPost(\''+id+'\')">here</a> to restore it.</td></tr>').insertAfter($(this));
		})
	});
}

function getAccountPrefs(callback){
	concat = '	<form name="acctPrefs" action="javascript:updateAcctPrefs()" autocomplete="off">';
	concat += '	<table cellspacing="0px" cellpadding="0px">';
	concat += '	<tr><th colspan="2">Enter your password</th></tr>';
	concat += '	<tr><td>Current Password:</td><td><input type="password" name="pword0"></td></tr>';
	concat += '	<tr><th colspan="2">Update your email</th></tr>';
	concat += '	<tr><td width="50%">New Email:</td><td width="50%"><input type="text" name="email1"></td></tr>';
	concat += '	<tr><td>Comfirm:</td><td><input type="text" name="email2"></td></tr>';
	concat += '	<tr><th colspan="2">Update your password</th></tr>';
	concat += '	<tr><td>New Password:</td><td><input type="password" name="pword1"></td></tr>';
	concat += '	<tr><td>Comfirm:</td><td><input type="password" name="pword2"></td></tr>';
	concat += '	<tr><th colspan="2">Choose your digits</th></tr>';
	concat += '	<tr><td>Phone Number:</td><td><input type="text" name="phone1"></td></tr>';
	concat += '	<tr><td colspan="2"><input type="submit" value="Save Changes"></td></tr>'
	concat += '	</table>';
	concat += '	</form>';
	$("#acctMod").html(concat);
	if(typeof(callback) == "function")
		callback();
}

function updateAcctPrefs(){
	var email1 = document.acctPrefs.email1.value;
	var email2 = document.acctPrefs.email2.value;
	var pword0 = document.acctPrefs.pword0.value;
	var pword1 = document.acctPrefs.pword1.value;
	var pword2 = document.acctPrefs.pword2.value;
	var phone1 = document.acctPrefs.phone1.value;
	if(pword0 == ""){
		dialog("You must enter your password first.", true);
		return;
	}
	var cont = true;
	if(email1 != ""){
		if(validateEmail(email1)){
			if(email1 != email2){
				dialog("Emails must match.", true);
				cont = false;
			}
		}
		else{
			dialog("Please enter a valid email", true);
			cont = false;
		}
	}

	if(pword1 != ""){
		if(pword1.length > 7){
			if(pword1 != pword2){
				dialog("Passwords must match.", true);	
				cont = false;
			}
		}
		else{
			dialog("Passwords must be 8 characters or longer.", true);
			cont = false;
		}
	}

	if(phone1 != ""){
		if(phone1.length != 10){
			dialog("Please enter a valid phone number", true);
			cont = false;
		}
	}

	if(cont){
		user = confirm("Are you sure you want to save these changes? This cannot be undone.");
		if(user){
			$.ajax({dataType:"html",  data:"intent=controlPanel&oldPw="+pword0+"&email="+email1+"&newPw="+pword1+"&phone="+phone1, context:user_logout}).success(function(r){
				switch(r){
					case "No Act":
					break;
					default:
					dialog(r,true,this);
					break;
				}
			});
		}
	}
}

function getProfilePrefs(){
	concat = '<div id="profileImgWrap">';
	concat += '<img id="avImgElement" src="'+window.avatar.src+'">';
	concat += '<div id="avMod"><h2>'+window.username+'</h2><hr>';
	concat += '<p>update profile image: <form id="imageUploadForm"><input type="hidden" name="intent" value="uploadAvatar"> <input name="avatar" type="file" accept="image/jpeg" onchange="getImage(this)"><input value="Save Changes" type="submit"></form></p></div>';
	concat += '</div>';
	$("#profileAvatar").html(concat);
	$('#imageUploadForm').on('submit', function(e){
		e.preventDefault();
		var formData = new FormData(this);
		$.ajax({dataType:"html",  data:formData, contentType:false, processData:false, cache:false}).success(function(r){
			if(r == "0"){
				$.ajax({ data:"intent=getAvatar"}).done(function(imgSrc){
					window.avatar = new Image();
					window.avatar.src = imgSrc;
					$("#avatar img").attr("src", window.avatar.src);
					$("#dd img").attr("src", window.avatar.src);
				});
				dialog("We hope you like the new look!", true);
			}
			else{
				dialog(r, true);
			}
		});
	});
}

function getImage(img){
	handleImage(img);
}

function handleImage(input){
	var $prev = $('#avImgElement'); 
	if (input.files && input.files[0]) {
		window.img = input.files[0];
		var reader = new FileReader();
		reader.onload = function(e){
			$prev.attr('src', e.target.result);
		}
		reader.readAsDataURL(input.files[0]);
	} 
}

function updateEmailPref(value){
	value = (value) ? 1 : 0;
	$.ajax({dataType:"html",  data:"intent=setEmailPref&pref="+value, context:refreshEmailPref}).success(function(r){
		if(r != 0)
			dialog(r, true);
		else
			refreshEmailPref;
	});
}
function refreshEmailPref(){
	$.ajax({dataType:"html",  data:"intent=getEmailPref", context:refreshEmailPref}).success(function(r){
		if(r == "1")
			$("#emailPfefCheckBox").attr("checked", "checked");
		else
			$("#emailPfefCheckBox").removeAttr("checked");
	});
}

function deletePost(identifier){
	dialog("Are you sure you want to delete this post? This cannot be undone.", true, null, function(r){
		if(r){
			$.ajax({dataType:"html", data:"intent=removePost&"+identifier+"=", context:getUserPosts}).success(function(r){
				if(r == "success"){
					this();
				}
				else{
					dialog(r);
				}
			});
		}
	});
}

function renewPost(identifier){
	$.ajax({dataType:"html", data:"intent=renewPost&"+identifier+"=", context:getUserPosts}).success(function(r){
		if(r == "success"){
			this();
		}
		else{
			dialog(r);
		}
	});
}