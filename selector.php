<?php
$userAgent = $_SERVER["HTTP_USER_AGENT"];
$mobileDevices = array("Android", "iPhone", "iPad", "Windows Phone");

foreach ($mobileDevices as $mobileDevice) {
    if(strpos($userAgent, $mobileDevice) !== false){
        include("mobile/index.html");
        return;
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
	<title>Walkntrade - Buy and sell on campus!</title>
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
	<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-42896980-1', 'auto');
  ga('send', 'pageview');

	</script>
</head>
<body>
<div class="fullBodyWrapper">
	<div id="parallax"></div>
	<div class="titleHeader"><img src="/colorful/wtlogo_dark.png"></img> <button>Search for your school!</button></div>
	<div class="clearfix" id="clearFix0">
	<div id="appClicker">
	</div>
	<!-- 	<div>
			<h1>BUY. SELL. TRADE.</h1>
			<br>
			<h2>Put down the flyers, pick up the mouse.</h2>
		</div>
		<div>
			<button>Find My School Now</button>
		</div> -->
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
<script type="text/javascript" src="/script/jquery.min.js"></script>
<script type="text/javascript" src="/script/walkntrade.js"></script>
<script type="text/javascript">
var img1=new Image();
    img1.src="colorful/app_banner.jpg";
var img2=new Image();
    img2.src="colorful/cf1.jpg";
var img3=new Image();
    img3.src="colorful/cf2.jpg";       
var img4=new Image();
    img4.src="colorful/cf3.jpg";

$("#appClicker").click(function(){
	window.location="https://play.google.com/store/apps/details?id=com.walkntrade";
});

$("#parallax").height($(document).height());

$(".titleHeader button").click(function(){
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
				url:"/api2/", 
				dataType:"json", 
				type:"POST", 
				data:"intent=getSchools&query="+ss.val()
				}).success(function(json){
					listDOM.innerHTML="";
					if(json.payload.length > 0){
						$("#schools").animate({height: (json.payload.length * 45) + "px"}, { queue:false, duration:200 });
					}
					else{
						$("#schools").animate({height: "0px"}, { queue:false, duration:200 });
					}

					for(var i = 0; i < json.payload.length; i++){
						var textId = json.payload[i].textId;
						var sName = json.payload[i].name;

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
