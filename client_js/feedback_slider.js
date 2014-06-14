$(document).ready(function(){
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
				// $(document).click(function(event){
				// 	$("#feedbackWrapper").animate({right: 0});
				// })
				// $("#feedbackWrapper").click(function(e) {
    // 				e.stopPropagation();
    // 				return false; 
				// });
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
})

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