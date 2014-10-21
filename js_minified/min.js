window.api_url = "/api/";
window.api_url2 = "/api2/";
window.resultsPageActive = false;

$(document).ready(function(){
	initFeedbackSlider();
	pollNewMessages();
});

function initAddPost(){
	window.uploadedImages  = 0;
	window.clear = true;
}

function initResults(){
	window.category = "All";
	window.sort = 0;
	window.offset=0;
	window.lastCat = "";
	window.canvas = "#dynamicContent";
	window.scrolling = false;
	window.inhibitUpdate = false;
	window.resultsPageActive = true;

	$.ajaxSetup({url:api_url2, dataType:"json", type:"POST", timeout:15000});


	$(document).ajaxStart(function(){
		document.getElementById("throbber").style.display="block";
	});

	$(document).ajaxComplete(function(){
		document.getElementById("throbber").style.display="none";
	});

	$(document).ajaxError(function(){
		dialog("<h2>Hmmm, something's not right here</h2><p>We are having a problem connecting to walkNtrade. Please check your internet connection and try again.</p>",true);
	});


	$(document).ready(function() {
		resizeListings();
		pageLoad($("#queryBar").val(), window.school, window.category, window.sort, null);
		$("#dump").append("<ul></ul>");
		//updateHeader();
		$("#filterBar").find("a").click(updatePage);
		$("#Nav li").click(
			function(){
				window.category = this.getAttribute("id");
				updatePage();
			});
		$(window).scroll(function(e){
			if($(this).scrollTop() > 64){
				var navWidth = $("#mainCWB").width();
				$("#Nav").css({'width': navWidth}); 
				$("#Nav").css({'position': 'fixed', 'top': '0px'}); 
			}
			else if($(this).scrollTop() <= 185){
				$("#Nav").css({'position': 'inherit'});
				$("#Nav").css({'width': '100%'});
			}
			var scrollPercent = ($("body").height() - $(window).scrollTop()) / $("body").height();
			if(window.scrolling && scrollPercent < ($(window).height() / $("body").height())){
				pageLoad($("#queryBar").val(), window.school, window.category, window.sort, null);
				window.scrolling=false
			}
		});
		$(window).resize(function(){
			if($(this).scrollTop() > 146){
				var navWidth = $("#mainCWB").width();
				$("#Nav").css({'width': navWidth}); 
			}
			resizeListings();
		});
	});
}

function initCP(){
	window.includeDir = "/include/user_settings/";
	window.sections = new Array('<i class="sprite sprite-1396343029_shop"></i>Welcome', '<i class="sprite sprite-1396343080_mail"></i>Received Messages', '<i class="sprite sprite-1396343166_paperplane"></i>Sent Messages', '<i class="sprite sprite-1396343050_news"></i>Your Posts', '<i class="sprite sprite-1396343908_settings"></i>Account Settings', '<i class="sprite sprite-1396343345_user"></i>Profile Settings', '<i class="sprite sprite-1396343039_like"></i>Contact Preferences');
	window._preventDefault;
	window.jumpTo;
	window.cpModule = new Array();
	window.avatar;
	window.username;
	$.ajaxSetup({url:api_url, type:"POST", timeout:15000});

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
				$("#contentTab").slideUp(200, function(){
					$("#contentTab").html(cpModule[e.target.id]);
					$("#contentTab").slideDown(200);
				});
			});
		}
		$("#screen_solid").fadeOut();
	})
}

function initShowPage(){
	$(document).ready(function(e){
		$("#imageOne img").click(blowupImage);
		$("#moreImages img").click(blowupImage);
	});
}

function initFeedbackSlider(){
	var hover_width = 20;
	var hover_animation_time = 500;
	var click_width= 350;

	function resetFeedback(){
		$("#feedbackButton img").click(function(){
			$("#feedbackWrapper").animate({right: + click_width}, function(){
				$("#feedbackButton img").unbind();
				$("#feedbackButton img").click(function(e){
					$("#feedbackWrapper").animate({right: 0});
					resetFeedback();
				});
				$("body *").keydown(function(e){
					if(e.keyCode==27)
					$("#feedbackWrapper").animate({right: 0});
				});
			});
		})
	}

	$("body").prepend("<div id='feedbackWrapper'></div>");
	$("#feedbackWrapper").prepend("<div id='feedbackButton'><img src='/colorful/feedback_button.png'></div>");
	$("#feedbackWrapper").append("<div id='feedbackContent'>\
		<h1>Let us know what you think!</h1>\
		<p>Leave us a message below!</p>\
		<p>\
		<form name='feedbackForm' action='javascript:submitFeedback()'>\
		<input name='email' type='text' placeholder='email address (optional)'>\
		<textarea name='message' placeholder='your message here'></textarea>\
		<input type='submit' value='Send' class='button'>\
		</form>\
		</p>\
		</div>");
	resetFeedback();
}

function pollNewMessages(){
	if(window.location.pathname == "/user_settings" || window.location.pathname == "/user_settings.php"){
		$("#messageIndicator").attr("onclick", "javascript:loadModule('1')");
		$("#postIndicator").attr("onclick", "javascript:loadModule('3')");
		$("#settingsIndicator").attr("onclick", "javascript:loadModule('4')");
	}
	else{
		$("#messageIndicator").attr("onclick", "window.location = '/user_settings#1'");
		$("#postIndicator").attr("onclick", "window.location = '/user_settings#3'");
		$("#settingsIndicator").attr("onclick", "window.location = '/user_settings#4'");
	}
	$.ajax({url: "/api/", dataType: "html", type:"POST", data:"intent=pollNewWebmail"}).success(function(responseText){
		var checkVal = parseInt(responseText);
		if(checkVal !== "NaN" && checkVal > 0){
			$("#mNum").slideDown().html(checkVal).css("background", "#9CCC65");
		}
	});
}

function setCookie(c_name,value,exdays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value +"; path=/";
}

function popup(url) {
	newwindow=window.open(url,'name','height=580,width=510');
	if (window.focus) {newwindow.focus()}
}

function changeSchools(){
	// setCookie("sPref", "", "-1");
	window.location = "/selector.php";
}

function dialog(message,nobutton,callback1,callback2){
		//Get rid of any previous dialogs
		if($("#screen").length != 0){
			$("#screen").fadeOut(function(){
				$("#screen").remove();
				act();
			});
		}
		else
			act();

		function destroy(status){
			$("#screen").fadeOut(function(){
				$("#screen").remove();
				if(typeof callback2 === "function"){
					callback2(status);
				}
			});
		}

		function act(){
		//create a new dialog
		$("body").prepend('<div id="screen"></div>');
		$("#screen").css("display", "none");
		if(!nobutton)
			$("#screen").html("<div id='dialog' class='boxStyle1 dialog'></div>");
		else
			$("#screen").html("<div class='boxStyle1 dialog'><p style='text-align:center;'><input id='dialogComfirm' type='button' class='button' value='Ok'></div>");
		if(message != ""){
			//message = "<p>"+message+"</p>";
			$("#screen div").prepend(message);
		}
		if(typeof callback1 === "function"){
			callback1();
		}
		$("#screen").fadeIn(function(){
			$("#dialogComfirm").click(function(e){
				destroy(true);
			});
			$("body").keydown(function(e){
				if(e.keyCode==27)
					destroy(false);
				// else if(nobutton && e.keyCode==13)
				// 	destroy(true);
			});
			$("#screen").click(function(event){
				if(event.target != this) return;
				destroy(false);
			})
		});
	}
}

function submitFeedback(){
		var email = document.feedbackForm.email.value;
		var message = document.feedbackForm.message.value;

		if(email != "" && !validateEmail(email)){
			dialog("Please use a valid email address",true);
			return;
		}

		if(message != ""){
			$.ajax({url:"/api/", dataType:"html", type:"POST", data:"intent=sendFeedback&email="+email+"&message="+message}).success(function(r){
				$("#feedbackWrapper").animate({right: 0});
				dialog(r,true);
			})
		}
	}

function loadModule(e){
		$("#navBar ul").find("li").removeAttr("class");
		$("#"+e).attr("class", "selected");
		$("#contentTab").slideUp(200, function(){
			$("#contentTab").html(cpModule[e]);
			$("#contentTab").slideDown(200);
		});
	}

function getWebmail(){
	$("#navBarMail").html("[Waiting for mail]");
	$.ajax({ dataType:"xml", data:"intent=getWebmail&quiet=true"}).success(function(xml){
		var pageElement = $("#webmail");
		pageElement.html("<table cellpadding=\"0\" cellspacing=\"0\"></table>");
		$("#navBarMail").html("["+$(xml).find("message").length+"] messages total <input type='button' class='button' value='Reload' onclick='getWebmail()' >");
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
	$.ajax({ dataType:"xml", data:"intent=getSentWebmail&quiet=true"}).success(function(xml){
		var pageElement = $("#webmail");
		pageElement.html("<table cellpadding=\"0\" cellspacing=\"0\"></table>");
		$("#navBarMail").html("["+$(xml).find("message").length+"] messages total");
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
	$.ajax({url:api_url2, dataType:"json", data:"intent=getPostsCurrentUser"}).success(function(json){
		var contentElement = $("#yourPosts");
		contentElement.html("");
		if(json.payload.length == 0 ){
			$("#yourPosts").append($("<h3/>"));
			$("#yourPosts h3").text("You have nothing to show here yet, but you can post something now!");
		}
		else{
			contentElement.html("");
			contentElement.append($("<table/>", {"cellpadding":"0", "cellspacing":"0"}));
		}
		for(i=0;i<json.payload.length;i++){
			var shortName = json.payload[i].shortName;
			var longName = json.payload[i].longName;
			contentElement.find("table").append($("<tr/>", {"id":shortName}));
			contentElement.find("table #"+shortName).append($("<th/>", {"width":"60%", "colspan":"3"})).append($("<th/>", {"width":"30%"})).append($("<th/>", {"width":"10%"}));
			contentElement.find("table #"+shortName+" th:first").text(longName);
			contentElement.find("table #"+shortName+" th:nth-child(2)").text("Posted/Renewed");
			contentElement.find("table #"+shortName+" th:nth-child(3)").text("views");
			var schoolPostsElement = contentElement.find("table");
			for(j=0;j<json.payload[i].post.length;j++){
				var id = json.payload[i].post[j].id;
				var link = json.payload[i].post[j].link;
				var category = json.payload[i].post[j].category;
				var title = json.payload[i].post[j].title;
				var date = json.payload[i].post[j].date;
				var views = json.payload[i].post[j].views;
				var expire = json.payload[i].post[j].expire;
				var expired = json.payload[i].post[j].expired;
				if(expired == "false"){
					if(expire == -1) {
						schoolPostsElement.append('<tr id="'+link+'" class="'+category+'"><td width="2%"><a href="javascript:deletePost(\''+link+'\')"><i class="sprite sprite-1396379273_86"></i></a></td> <td width="2%"><a href="javascript:popup(\'editPost?'+link+'\')"><i class="sprite sprite-1396379288_90"></i></a></td> <td><a href="show?'+link+'">'+title+'</a></td><td>'+date+'</td><td>'+views+'</td></tr>');
					}
					else {
						schoolPostsElement.append('<tr id="'+link+'" class="'+category+', expiring"><td width="2%"><a href="javascript:deletePost(\''+link+'\')"><i class="sprite sprite-1396379273_86"></i></a></td> <td width="2%"><a href="javascript:popup(\'editPost?'+link+'\')"><i class="sprite sprite-1396379288_90"></i></a></td> <td><a href="show?'+link+'">'+title+'</a></td><td>'+date+'</td><td>'+views+'</td></tr>');
					}
				}
				else {
					schoolPostsElement.append('<tr id="'+link+'" class="'+category+', expired"><td width="2%"><a href="javascript:deletePost(\''+link+'\')"><i class="sprite sprite-1396379273_86"></i></a></td> <td width="2%"></td> <td><a href="show?'+link+'">'+title+'</a></td><td>'+date+'</td><td>'+views+'</td></tr>');
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
			}
		}
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

function updateHeader(){
	$("#Nav").find("li").removeAttr("class");
	$("#"+window.category).attr("class", "selected");
}

function w_sort(sort){
	window.sort=sort;
}

function resizeListings(){
	console.log($(window).width());
	if($(window).width() > 1366){
		window.perPage = 21;
		$("#mainCWB #dynamicWrapper #dynamicContent #dump li").css("width", "calc(14% - 19px)");
	}
	else{
		window.perPage = 15;
		$("#mainCWB #dynamicWrapper #dynamicContent #dump li").css("width", "calc(20% - 22px)");
	}
}

function updatePage(){
	if(!window.inhibitUpdate){//only allow page update if an update is not  already in progress.
		window.inhibitUpdate = true;//lock other update threads
		$("html, body").animate({ scrollTop: 0}, 500);
		updateHeader();
		$(window.canvas).slideUp(300, function(){
			$("#dump").find("ul").empty();
			$("#message").hide();
			window.offset=0;
			pageLoad($("#queryBar").val(), window.school, window.category, window.sort, 
				function(){
					$(window.canvas).slideDown(300);

					window.inhibitUpdate = false;//release page for updating
				});
		});
	}
}

function pageLoad(query, school, cat, sort, callback) {
	$.ajax({data:"intent=getPosts&query=" + query + "&school=" + school + "&cat=" + cat + "&offset=" + window.offset + "&sort=" + sort + "&amount=" + window.perPage +"&ellipse=1"}).success(function(json){
		var parentElement = $("#dump").find("ul");
		if(json.payload.length > 0){
			for(i=0;i<json.payload.length;i++){
				var id = json.payload[i].id;
				var obsId = json.payload[i].obsId;
				var title = json.payload[i].title;
				var category = json.payload[i].category;
				var details = json.payload[i].details;
				var username = json.payload[i].username;
				var price = json.payload[i].price;
				var image = json.payload[i].image;
				var userid = json.payload[i].userid;
				var date = json.payload[i].date;
				var views = json.payload[i].views;
				switch(category){
					case("book"):
					color = "rgba(103,137,9,.75)";
					break;
					case("tech"):
					color = "rgba(55,9,137,.75)";
					break;
					case("housing"):
					color = "rgba(255,140,0,.75)"; 
					break;
					case("misc"):
					color="rgba(68,211,216,.75)";
					break
					default:
					color="rgba(0,0,0,.5)";
					break;
				}
				parentElement.append($("<a/>", {"id":"p_"+id, "href":"/show?"+obsId}));
				$("#p_"+id).append($("<li/>"));
				$("#p_"+id+" li")
					.append($("<div/>", {"class":"title"}))
					.append($("<div/>", {"class":"image"}))
					.append($("<div/>", {"class":"price"}))
					.append($("<div/>", {"class":"username"}))
					.append($("<div/>", {"class":"details"}))
					.append($("<div/>", {"class":"categoryTab", "style":"background-color:"+color}));

				$("#p_"+id+" li").find(".title").append($("<h4/>"));
				$("#p_"+id+" li").find(".title h4").text(title);

				$("#p_"+id+" li").find(".image").append($("<img/>", {"src":image}));

				$("#p_"+id+" li").find(".price").text(price);

				$("#p_"+id+" li").find(".username").text(username);

				$("#p_"+id+" li").find(".details").text(details);

				$("#p_"+id+" li").find(".categoryTab").text(category);
				resizeListings();
			}
		}
		if(json.payload.length == 0){
			if(window.offset==0)
				$("#message").show();
		}
		else{
			window.scrolling=true
		}
		if (typeof callback == "function") callback();
		window.offset += window.perPage;
	});
}

function createMessageWindow(userId, title, userName, message){
	var messageWindow ='<form name="contact" action="javascript:messageUser()">\
	<input type="hidden" name="uid" value="'+userId+'">\
	<input type="hidden" name="title" value="'+title+'">\
	<table id="messageWindow">\
		<tr>\
			<th>Email '+userName+'</th>\
		</tr>\
		<tr>\
			<td><textarea name="message">'+message+'</textarea></td>\
		</tr>\
		<tr>\
			<td><span id="response"></span></td>\
		</tr>\
		<tr>\
			<td><input type="submit" value="send"></td>\
		</tr>\
	</table>\
	</form>';
	dialog(messageWindow,false);
}

function messageUser(){
	var id = document.contact.uid.value;
	var title = document.contact.title.value;
	var message = document.contact.message.value;

	if(message.length < 20){
		$("#response").html("your message is too short");
		return
	}
	else{
		$("#response").html("");
	}
	$.ajax({url:"/api/", 
		dataType:"html", 
		type:"POST", 
		data:"intent=messageUser&uid="+id+"&title="+title+"&message="+message
		}).success(function(r){
			var responseObj = document.getElementById("response");
			switch(r){
				case("success"):
					dialog("Your message was sent successfully",true,null,function(){
						window.location = "./";
					});
				break;
				default:
				dialog(r,false);
				break;
			}
	});
}

function validateEmail(email){
	var atpos=email.indexOf("@");
	var dotpos=email.lastIndexOf(".");
	tld = email.substring((email.length - 3), (email.length))
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
			window.location = "/";
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
				<hr>\
				</form>\
				<p id="verbose">\
				<i>Didn\'t recieve the email? Click <a style="text-decoration:underline" href="javascript:allowMe()">here</a> and we\'ll send you a new one</i>\
				</p>', false);
			break;
			default:
			$("#response").html("<font color=\"#FF0000\">"+r+"</font>");
			break;
		}
	});
}

function allowMe(){
	$("#verbose").html("<form action='javascript:void(0)' onSubmit='resendEmail(this.emailField.value)'><input placeholder='Enter your email' name='emailField'><input type='submit'></form>");
}

function checkUname(uname){
	$.ajax({url:api_url2, 
		dataType:"json", 
		type:"POST",
		async: false, 
		data:"intent=checkUsername&username=" + uname
		}).success(function(json){
		if(json.status == 200){
			document.getElementById('1Err').innerHTML = "";
			window.unameOK = true;
		}
		else{
			document.getElementById('1Err').innerHTML = '*'+uname+' is taken.';
			window.unameOK = false;
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
		if(!unameOK)
			return;
		if(uname.length < 5 || uname.length > 20){
			$("#1Err").html("*Username must be between 5 and 20 characters.");
			return;
		}
		else if(uname.match(/\s/g)){
			$("#1Err").html("*Username may not contain spaces.");
			return;
		}
		else{
			$("#1Err").html("");
		}
	}
	else{
		$("#1Err").html("*Please enter a username.");
		return;
	}
	//----------------------------------------email verification
	if (email != ""){
		var atpos=email.indexOf("@");
		var dotpos=email.lastIndexOf(".");
		tld = email.substring((email.length - 3), (email.length))
		if (atpos < 1 || dotpos < atpos+2 || dotpos+2 >= email.length){
			$("#2Err").html("*Please enter a valid email address.");
			return;
		}
		else if(tld != "edu"){
			$("#2Err").html("*Please use your student (.edu) email address.");
			return;
		}
		else if(email.match(/\s/g)){
			$("#2Err").html("*Please enter a valid email address.");
			return;
		}
		else{
			$("#2Err").html("");
			alert("e good");
		}
	}
	else{
		$("#2Err").html("*Please enter an email address.");
		return;
	}

	//----------------------------------------phone verification
	phone = phone.replace(/[^0-9.]/g, "");

	if (phone != ""){
		if(phone.length != 10){
			$("#phoneErr").html("Please enter a valid phone number.");
			return;
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
			return;
			$("#3Err").html("*password must be at least 8 characters.");
		}

		else {
			$("#3Err").html("");
		}
	}
	else{
		return;
		$("#3Err").html("*Please enter a password.");
	}
	
	if (pword != pword2){
		return;
		$("#4Err").html("*Passwords do not match.");
	} 
	else{
		$("#4Err").html("");
	}
	alert();
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

function handleResponse(response){
	if(response.match(/^[a-f0-9]/) !== null){
		sendFiles(response);
	}
	else{
		dialog(response);
	}
}

function addBook(){
	var title = books.elements["Title"].value;
	var author = books.elements["Author"].value;
	var details = books.elements["Details"].value;
	var isbn = books.elements["ISBN"].value;
	var price = books.elements["Price"].value;
	var tags = books.elements["Tags"].value;

	var errTitle = document.getElementById("errTitle");
	var errauthor = document.getElementById("errAuth");
	var errDescription = document.getElementById("errDescription");
	var errIsbn = document.getElementById("errIsbn");
	var errPrice = document.getElementById("errPrice");
	var errTags = document.getElementById("errTags");

	var cont = true;

	if(title != ""){
		if(title.length < 2){
			errTitle.innerHTML="You're bluffing.";
			cont = false;
		}
		else{
			if(title.length > 150){
				errTitle.innerHTML="I'm too lazy to read this... Please make it shorter.";
				cont = false;
			}
			else{
				errTitle.innerHTML = "";			
			}
		}
	}
	else{
		errTitle.innerHTML="Please enter a title.";
		cont = false;
	}

	if(author != ""){
		if(author.length < 2){
			errauthor.innerHTML="Check that author name again...";
			cont = false;
		}
		else{
			if(author.length > 50){
				errauthor.innerHTML="Your author's name is long enough to fill the book...";
				cont = false;
			}
			else{
				errauthor.innerHTML = "";			
			}
		}
	}
	else{
		errauthor.innerHTML="Please enter an author.";
		cont = false;
	}

	if(details != ""){
		if(details.length < 5){
			errDescription.innerHTML="Your details are too short.";
			cont = false;
		}
		else{
			if(details.length > 3000){
				errDescription.innerHTML="Your details are too long, please limit to 3000 characters.";
				cont = false;
			}
			else{
				errDescription.innerHTML = "";			
			}
		}
	}
	else{
		errDescription.innerHTML="Please enter details about your listing.";
		cont = false;
	}

	if(isbn != ""){
		isbn = isbn.replace(/[^0-9]/g, "");
		if(isbn.length == 10 || isbn.length == 13){
			errIsbn.innerHTML="";
		}
		else{
			errIsbn.innerHTML="Please enter a valid ISBN-10 or ISBN-13 number.";
			cont = false;
		}
	}

	price = price.replace(/[^0-9.]/g, "");

	if(tags != ""){
		if(tags.length < 5){
			errTags.innerHTML="Please use more descriptive tags.";
			cont = false;
		}
		else{
			errTags.innerHTML = "";			
		}
	}
	else{
		errTags.innerHTML="Please enter tags to describe your post";
		cont = false;
	}

	if(cont){
		$.ajax({
		url:api_url, 
		dataType:"html", 
		type:"POST", 
		data:"intent=addPost&cat=book&title="+title+"&author="+author+"&details="+details+"&price="+price+"&isbn="+isbn+"&tags="+tags
		}).success(handleResponse);
	}
}

function addElectronics() {
	var title = electronics.elements["Name"].value;
	var details = electronics.elements["Details"].value;
	var price = electronics.elements["Price"].value;
	var tags = electronics.elements["Tags"].value;

	var errTitle = document.getElementById("errTitle");
	var errDescription = document.getElementById("errDescription");
	var errPrice = document.getElementById("errPrice");
	var errTags = document.getElementById("errTags");
	
	var cont = true;

	if(title != ""){
		if(title.length < 2){
			errTitle.innerHTML="You're bluffing.";
			cont = false;
		}
		else{
			if(title.length > 150){
				errTitle.innerHTML="I'm too lazy to read this... Please make it shorter.";
				cont = false;
			}
			else{
				errTitle.innerHTML = "";			
			}
		}
	}
	else{
		errTitle.innerHTML="Please enter a title.";
		cont = false;
	}
	
	if(details != ""){
		if(details.length < 5){
			errDescription.innerHTML="Your details are too short.";
			cont = false;
		}
		else{
			if(details.length > 3000){
				errDescription.innerHTML="Your details are too long, please limit to 3000 characters.";
				cont = false;
			}
			else{
				errDescription.innerHTML = "";			
			}
		}
	}
	else{
		errDescription.innerHTML="Please enter details about your listing.";
		cont = false;
	}
	
	price = price.replace(/[^0-9.]/g, "");

	if(tags != ""){
		if(tags.length < 5){
			errTags.innerHTML="Please use more descriptive tags.";
			cont = false;
		}
		else{
			errTags.innerHTML = "";			
		}
	}
	else{
		errTags.innerHTML="Please enter tags to describe your post";
		cont = false;
	}

	if(cont){
		$.ajax({
		url:api_url, 
		dataType:"html", 
		type:"POST", 
		data:"intent=addPost&cat=tech&title="+title+"&details="+details+"&price="+price+"&tags="+tags
		}).success(handleResponse);
	}
}

function addHousing(){
	var title = housing.elements["Name"].value;
	var details = housing.elements["Details"].value;
	var price = housing.elements["Price"].value;
	var tags = housing.elements["Tags"].value;

	var errTitle = document.getElementById("errTitle");
	var errDescription = document.getElementById("errDescription");
	var errImage = document.getElementById("errImage");
	var errPrice = document.getElementById("errPrice");
	var errTags = document.getElementById("errTags");
	
	var cont = true;
	
	if(title != ""){
		if(title.length < 2){
			errTitle.innerHTML="You're bluffing.";
			cont = false;
		}
		else{
			if(title.length > 150){
				errTitle.innerHTML="I'm too lazy to read this... Please make it shorter.";
				cont = false;
			}
			else{
				errTitle.innerHTML = "";			
			}
		}
	}
	else{
		errTitle.innerHTML="Please enter a title.";
		cont = false;
	}
	
	if(details != ""){
		if(details.length < 5){
			errDescription.innerHTML="Your details are too short.";
			cont = false;
		}
		else{
			if(details.length > 3000){
				errDescription.innerHTML="Your details are too long, please limit to 3000 characters.";
				cont = false;
			}
			else{
				errDescription.innerHTML = "";			
			}
		}
	}
	else{
		errDescription.innerHTML="Please enter details about your advertisement.";
		cont = false;
	}
	
	price = price.replace(/[^0-9.]/g, "");

	if(tags != ""){
		if(tags.length < 5){
			errTags.innerHTML="Please use more descriptive tags.";
			cont = false;
		}
		else{
			errTags.innerHTML = "";			
		}
	}
	else{
		errTags.innerHTML="Please enter tags to describe your post";
		cont = false;
	}
	if(uploadedImages < 2){
		errImage.innerHTML="You must have at least 2 images for this type of post";
		cont = false;
	}
	
	if(cont){
		$.ajax({
		url:api_url, 
		dataType:"html", 
		type:"POST", 
		data:"intent=addPost&cat=housing&title="+title+"&details="+details+"&price="+price+"&tags="+tags
		}).success(handleResponse);
	}
}

function addMisc(){
	var title = miscellaneous.elements["Title"].value;
	//var location = miscellaneous.elements["location"].value;
	var details = miscellaneous.elements["Details"].value;
	var tags = miscellaneous.elements["Tags"].value;

	var errTitle = document.getElementById("errTitle");
	//var errLocation = document.getElementById("errLocation");
	var errDescription = document.getElementById("errDescription");
	var errTags = document.getElementById("errTags");

	var cont = true;

	if(title != ""){
		if(title.length < 2){
			errTitle.innerHTML="We need a longer name here.";
			cont = false;
		}
		else{
			if(title.length > 150){
				errTitle.innerHTML="Too long buddy. Let's shorten it a bit ;)";
				cont = false;
			}
			else{
				errTitle.innerHTML = "";			
			}
		}
	}
	else{
		errTitle.innerHTML="Please enter a title.";
		cont = false;
	}

	// if(location != ""){
	// 	if(location.length < 5){
	// 		errLocation.innerHTML="We need a longer description here.";
	// 		cont = false;
	// 	}
	// 	else{
	// 		if(location.length > 75){
	// 			errLocation.innerHTML="Maybe a little too descriptive. Please shorten it a bit";
	// 			cont = false;
	// 		}
	// 		else{
	// 			errLocation.innerHTML = "";			
	// 		}
	// 	}
	// }

	if(details != ""){
		if(details.length < 5){
			errDescription.innerHTML="Your details are too short.";
			cont = false;
		}
		else{
			if(details.length > 3000){
				errDescription.innerHTML="Your details are too long, please limit to 3000 characters.";
				cont = false;
			}
			else{
				errDescription.innerHTML = "";			
			}
		}
	}
	else{
		errDescription.innerHTML="Please enter details about your event.";
		cont = false;
	}

	if(tags != ""){
		if(tags.length < 5){
			errTags.innerHTML="Please use more descriptive tags.";
			cont = false;
		}
		else{
			errTags.innerHTML = "";			
		}
	}
	else{
		errTags.innerHTML="Please enter tags to describe your post";
		cont = false;
	}

	if(cont){
		$.ajax({
		url:api_url, 
		dataType:"html", 
		type:"POST", 
		data:"intent=addPost&cat=misc&title="+title+"&details="+details+"&tags="+tags
		}).success(handleResponse);
	}
}

function initDropBox(){
	var imgDrop = document.getElementById("imgDrop");
	imgDrop.addEventListener("dragenter", dragenter, false);
	imgDrop.addEventListener("dragover", dragover, false);
	imgDrop.addEventListener("drop", drop, false);
}

function dragenter(e) {
	e.stopPropagation();
	e.preventDefault();
}

function dragover(e) {
	e.stopPropagation();
	e.preventDefault();
}

function drop(e) {
	e.stopPropagation();
	e.preventDefault();

	var dt = e.dataTransfer;
	var files = dt.files;

	handleFiles(files, imgDrop);
}

function getImages(element){
	handleFiles(element.files, imgDrop);
}

function handleFiles(files, container) {
	for (var i = 0; i < files.length; i++) {
		var file = files[i];
		var imageType = /image.jpeg/;
    
	if (uploadedImages < 3 && !file.type.match(imageType)) {
		alert("Sorry, only JPEG images are allowed at this time.");
		continue;
	}
	if(uploadedImages < 3 && file.size > 5242880){
		alert("This image exceeds the 5MB limit.");
		continue;
	}
	if(uploadedImages > 3){
		alert("You have reached the limit of 4 images.");
		break;
	}
	if(clear){
		imgDrop.innerHTML="";
		clear=false;
	}
	uploadedImages++;


	var img = document.createElement("img");
	img.classList.add("obj");
	img.file = file;
	container.appendChild(img);
	var reader = new FileReader();
	reader.onload = (function(aImg){ return function(e) { aImg.src = e.target.result; }; })(img);
	reader.readAsDataURL(file);
	}
}

function sendFiles(identifier) {
	var imgs = document.querySelectorAll(".obj");
	document.getElementById("throbber").style.display="block";
	fileUpload(imgs, imgs.length - 1, identifier, uploadComplete);

	function fileUpload(blob, iteration, identifier, callback) {;
		if(iteration < 0){
			return callback("success");
		}
		if (window.XMLHttpRequest){
			xhttp = new XMLHttpRequest();
		}
		else {
			xhttp = new ActiveXObject("Microsoft.XMLHTTP");//IE Compatibility
		}
		xhttp.open("POST", "/api/", true);
			form = new FormData;
			form.append("intent", "uploadPostImages");
			form.append("identifier", identifier);
			form.append("iteration", iteration);
			form.append("image", blob[iteration].file);
		xhttp.send(form);
		xhttp.onreadystatechange = function(){
			if (xhttp.readyState==4 && xhttp.status==200){
				if(xhttp.responseText == "0"){
					if (typeof callback == "function"){
						return fileUpload(blob, iteration - 1, identifier, callback);
					}
				}
				else{
					console.log(xhttp.responseText);
					return callback("An error has occured while processing some images. They may not be included in your post.");
				}
			}
		}
	}

	function uploadComplete(status){
		document.getElementById("throbber").style.display="none";
		if(status != "success")
			alert(status);
		window.location = "user_settings";
	}
}

function formatPrice(element) {
			value = element.value;
			if (value != "") {
				value = value.replace(/[^0-9.]/g, "");
				if (value.charAt(0) != "$")
					element.value = "$" + value;
			}
}

function blowupImage(e){
		if(e.target.id == "noImg")
			return;
		var imgUrl = $("#"+e.target.id).attr("src");
		$("body").prepend("<div id='screen'><div id=\"imageLargeFloat\"><img src=\""+imgUrl+"\"></div></div>");
		$("#screen").css("display", "none");
		$("#screen").fadeIn();
		$("#screen").click(function(){
			$("#screen").fadeOut(function(){
				$("#screen").remove();
			});
		})
		$("body").keydown(function(e){
			if(e.keyCode==27)
				$("#screen").fadeOut(function(){
					$("#screen").remove();
				});
		});
	}

function resendEmail(email){
	if(!validateEmail(email)){
		$("#verbose").text("This is not a valid email");
		return;
	}
	$(document).ajaxStart(function(){
		$("#verbose").text("Please Wait...");
	});

	$(document).ajaxError(function(){
		$("#verbose").text("ERROR");
	});

	$.ajax({url:api_url2, dataType: "json", type:"POST", data:"intent=verifyEmail&email="+email}).done(function(json){
		$("#verbose").text(json.message);
	});
}