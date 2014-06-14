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