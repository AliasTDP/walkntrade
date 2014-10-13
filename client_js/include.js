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