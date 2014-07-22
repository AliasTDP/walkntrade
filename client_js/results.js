//-----------------------------------------------------------initialization sector
window.category = "All";
window.sort = 0;
window.offset=0;
window.lastCat = "";
window.canvas = "#dynamicContent";
window.scrolling = false;
window.inhibitUpdate = false;

var apiURL = "/api/";
$.ajaxSetup({url:apiURL, dataType:"xml", type:"POST", timeout:10000});


//-----------------------------------------------------------Events and handlers
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
	updateHeader();
	$("#filterBar").find("a").click(updatePage);
	$("#Nav li").click(
		function(){
			window.category = this.getAttribute("id");
			updatePage();
		});
	$(window).scroll(function(e){
		if($(this).scrollTop() > 185){
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
//-----------------------------------------------------------functions

function updateHeader(){
	$("#Nav").find("li").removeAttr("class");
	$("#"+window.category).attr("class", "selected");
}

function w_sort(sort){
	window.sort=sort;
}

function resizeListings(){
	if($(window).width() > 1400){
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
	$.ajax({data:"intent=getPosts&query=" + query + "&school=" + school + "&cat=" + cat + "&offset=" + window.offset + "&sort=" + sort + "&amount=" + window.perPage}).success(function(xml){
		var parentElement = $("#dump").find("ul");
		$(xml).find("listing").each(function(){
			var resultObjectXML = $(this);
			var id = resultObjectXML.attr("id");
			var obsId = resultObjectXML.attr("obsId");
			var title = resultObjectXML.attr("title");
			var category = resultObjectXML.attr("category");
			var details = resultObjectXML.attr("details");
			var username = resultObjectXML.attr("username");
			var price = resultObjectXML.attr("price");
			var image = resultObjectXML.attr("image");
			var userid = resultObjectXML.attr("userid");
			var date = resultObjectXML.attr("date");
			var views = resultObjectXML.attr("views");
			switch(category){
				case("book"):
				color = "rgba(103,137,9,.75)";
				break;
				case("tech"):
				color = "rgba(55,9,137,.75)";
				break;
				case("service"):
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
		});
		if($(xml).find("listing").length == 0){
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