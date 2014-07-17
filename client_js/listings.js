//--------------------------------------------------------------------------------------------------------------------------------------------------------
// Copyright 2013 walkNtrade.com This content may not be distributed, duplicated or modified without written consent of a walkNtrade.com representative --
// please email tdphillip@gmail.com (webmaster) for more information.                                                                                   --
//--------------------------------------------------------------------------------------------------------------------------------------------------------
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

function addService(){
	var title = service.elements["Name"].value;
	var details = service.elements["Details"].value;
	var price = service.elements["Price"].value;
	var tags = service.elements["Tags"].value;

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
		data:"intent=addPost&cat=service&title="+title+"&details="+details+"&price="+price+"&tags="+tags
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

//---------------FOR ASA ONLY------------------

function addASA(){
	var title = asa.elements["Name"].value;
	var details = asa.elements["Details"].value;
	var price = asa.elements["Price"].value;
	var tags = asa.elements["Tags"].value;

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
		data:"intent=addPost&cat=asa&title="+title+"&details="+details+"&price="+price+"&tags="+tags
		}).success(handleResponse);
	}
}


var uploadedImages  = 0;
clear = true;

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
					else{
						console.log("callback malformed");
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