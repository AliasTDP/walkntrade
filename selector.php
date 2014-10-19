<!DOCTYPE HTML>
<html>
<head>
	<title>Walkntrade.com - BUY AND SELL ON CAMPUS!</title>
	<link type="text/css" rel="stylesheet" href="css/style.css">
	<link type="text/css" rel="stylesheet" href="css/landing.css">
	<link type="text/css" rel="stylesheet" href="css/spritesheet.css">
	<link type="text/css" rel="stylesheet" href="css/selector2.css">
	<link href='https://fonts.googleapis.com/css?family=Gochi+Hand' rel='stylesheet' type='text/css'>
	<noscript><meta http-equiv="refresh" content="0;url=noscript.html"></noscript>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="description" content="We make it easy to trade and sell on campus!">
	<meta name="keywords" content="college, campus, students, buy, sell, trade, post">
	<meta name="robots" content="Index, Follow">
	<meta http-equiv="Content-Language" content="en">
</head>
<body>
<div class="fullBodyWrapper">
	<div id="parallax"></div>
	<div class="titleHeader"><img src="/colorful/wtlogo_dark.png"></img></div>
	<div class="clearfix" id="clearFix0">
		<div>
			<h1>BUY. SELL. TRADE.</h1>
			<br>
			<h2>Put down the flyers, pick up the mouse.</h2>
		</div>
		<div>
			<button>Find My School Now</button>
		</div>
	</div>


	<div class="contentBlock" id="bodyCB">
		<div id="blab">
			<div class="fl">
				<h2>Trade without having to leave campus.</h2>
				<p><i class="sprite sprite-1396377513_map-"></i></p>
				<p>Meet up and exchange in the convenience of your university. There are no shipping fees or the need to drive across town.</p>
			</div>
		</div>
	</div>
	<div class="clearfix" id="clearFix1"></div>


	<div class="contentBlock" id="bodyCB">
		<div id="blab">
			<div class="fl">
				<h2>Message traders securely and privately.</h2>
				<p><i class="sprite sprite-1396377515_bubbles"></i></p>
				<p>Message users privately through walkntrade's internal email. You never have to disclose your personal email or phone number if you don't want to.</p>
			</div>
		</div>
	</div>
	<div class="clearfix" id="clearFix2"></div>


	<div class="contentBlock" id="bodyCB">
		<div id="blab">
			<div class="fl">
				<h2>Find exactly what you need.</h2>
				<p><i class="sprite sprite-1396377528_target"></i></p>
				<p>Everyone on your school's Walkntrade page shares a common community, which makes it easier for you to find exactly what you need!</p>
			</div>
		</div>
	</div>
	<div class="clearfix" id="clearFix3">
		<div>
			<h1>Enter your school name to begin!</h1>
			<br>
			<input id="schoolSearch" name="schoolSearch" placeholder="Start searching here!">
			<div id="schools">
				<ul></ul>
			</div>
		</div>
	</div>

	<div class="footerBar">
			<?php include("include/footer.html"); ?>
	</div>
</div>
</body>
</html>
<script type="text/javascript" src="/client_js/jquery.min.js"></script>
<script type="text/javascript" src="/js_minified/min.js"></script>
<script type="text/javascript">
$("#parallax").height($(document).height());

$("#clearFix0 button").click(function(){
	var scrollTarget = $("#clearFix3").offset().top;
	$("html, body").animate({ scrollTop: scrollTarget}, 900, function(){$("#schoolSearch").focus();});
});

$(window).scroll(function () {
	var scrollNum = -($(this).scrollTop()/2);
	$('#clearFix1').css('background-position', '0px '+-(scrollNum+590)+'px');
	$('#clearFix2').css('background-position', '0px '+-(scrollNum+1150)+'px');
	$('#clearFix3').css('background-position', '0px '+-(scrollNum+1500)+'px');
	$('#parallax').css({'top' : scrollNum+"px"}); 
});

$(document).ready(function(){
	var ss = $("#schoolSearch");
	ss.bind("keyup", function(e){
		if(e.keyCode == 13){
			var elements = document.getElementsByClassName("sResult");
			if(elements.length == 1){
				setCookie('sPref', elements[0].getAttribute("id"),'30');
				window.location='./'
			}
		}
		var listDOM = document.getElementById("schools")
		if(ss.val() != ""){
			$.ajax({
				url:"/api/", 
				dataType:"html", 
				type:"POST", 
				data:"intent=getSchools&query="+ss.val()
				}).success(function(response){
					var results = new DOMParser().parseFromString(response, "application/xml");
					var schools = results.getElementsByTagName("school");
					listDOM.innerHTML="";
					if(schools.length > 0){
						$("#schools").animate({height: (schools.length * 45) + "px"}, { queue:false, duration:200 });
					}
					else{
						$("#schools").animate({height: "0px"}, { queue:false, duration:200 });
					}

					for(var i = 0; i < schools.length; i++){
						var textId = schools[i].getAttribute("textId");
						var sName = schools[i].getAttribute("name");

						element = addChildTo("schools", "li");
						element.innerHTML = sName;
						element.setAttribute("onClick", "javascript:setCookie('sPref', '"+textId+"','30');window.location='./'")
						element.setAttribute("class", "sResult");
						element.setAttribute("id", textId);
					}
				});
		}
		else{
				listDOM.innerHTML="";
				$("#schools").animate({height: "0px"}, { queue:false, duration:200 });
		}
	})
})

function addChildTo(parentId, childType) {//adds 'childType' element to 'parentId'
	var p = document.getElementById(parentId);
	var c = document.createElement(childType);
	p.appendChild(c);
	return c;
}
</script>