/*************************************

* Name: GradPhotoUploader.js
* Authors: Cyrus Parsons and Matt Smith
* Date: January 17, 2020
* Purpose: All javascript functions used in this website, used for 
	retrieving and editing JSON data, setting cookies, and controlling 
	visible images for the user.

*************************************/

var allJSON = []; // JSON array for all images 
var visibleImagesCookie;
var publicImagesCookie;
var CBclicked = false; // checkbox clicked
var DBclicked = false; //download button clicked
var inputClicked = false;  
var imagesToDelete = []; // array of images with a checkBox selected 
var isEditor = getCookie('isEditor'); // whether the user is a moderator or not (1 = true, 0 = false) 

// fills allJSON array with current JSON data every page reload
function getAllJSON() {
	var xmlhttp = new XMLHttpRequest();
	var url = "galleryinfo.json";
	
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			if (this.responseText.length){
				allJSON = JSON.parse(this.responseText);
			}
			if (isEditor == 0 && getCookie('publicVisibleImages').length) {
				publicImagesCookie = JSON.parse(getCookie('publicVisibleImages')); // JSON Array of images visible to the user in PUBLIC Gallery 
			} else if (isEditor == 1 && getCookie('visibleImages').length) {
				visibleImagesCookie = JSON.parse(getCookie('visibleImages')); // JSON Array of images visible to the user in MOD Gallery
			} // if/else if
			console.log("XML req JSON: " + JSON.stringify(allJSON));
			console.log("VICookie: " + JSON.stringify(visibleImagesCookie)); 
			console.log("PICookie: " + JSON.stringify(publicImagesCookie)); 
		} // if
	};
	
	xmlhttp.open("GET", url, true);
	xmlhttp.send();
} // getALLJSON

// shows every public/all image when you enter a gallery
function log(log) { 
	var imagesIn = []; // new array of visible Images
	if (log == "in") {
		for (var i = 0; i < allJSON.length; i++) {
				imagesIn.push(allJSON[i]); 
		} // for
		setCookie('visibleImages', JSON.stringify(spliceArray(imagesIn)), 1);
		window.location.href = 'http://142.31.53.141/~myrus/GradPhotoUploader/index.php?page=login';
	} else if (log == "out") {
		for (var i = 0; i < allJSON.length; i++) {
			if (allJSON[i].access == "public") {
				imagesIn.push(allJSON[i]); 
			} // if
		} // for
		setCookie('publicVisibleImages', JSON.stringify(spliceArray(imagesIn)), 1);
		window.location.href = 'http://142.31.53.141/~myrus/GradPhotoUploader/index.php?page=logout';
	} // if/else if
} // log

// display the light box, and change the lightbox's image source to desired image
function displayLightBox(imageID) {
	var image = new Image();
	var lightBox = document.getElementById("lightBox");
	var positionImage = document.getElementById("positionBigImage");
	var bigImage = document.getElementById("bigImage");
	var lightBoxInfo = document.getElementById("lightBoxInfo");
	
	// if there are no buttons clicked om the thumbnail, display the lightbox
	if (CBclicked == false && DBclicked == false && inputClicked == false) {
		if (imageID != -1){
			image.src = "UploadedImages/" + imageID;
			lightBox.style.zIndex = 25;
			positionImage.style.zIndex = 26;
			bigImage.style.zIndex = 27;
			positionImage.style.display = "block";
			// create buttons in lightbox
			if (isEditor == 1){
				createEditButton(imageID);
				createDownloadButton(imageID); 
			} // if 			
		} else {
			displayLightBoxInfo(-1);
			lightBox.style.zIndex = 0;
			positionImage.style.zIndex = 0;
			bigImage.style.zIndex = 0;
			positionImage.style.display = "none";
			removeElement("edit");
			removeElement("downloadBTN");
			removeElement("submission");
			removeElement("editFName");
			removeElement("editLName");
			removeElement("editDes");
			removeElement("editTag");
		} // if/else 
		
		// set boundary of lightbox based on image size
		image.onload = function() {
			var width = image.width;
			document.getElementById("boundaryBigImage").style.width = width + "px";
		};
		
		bigImage.src = image.src;
		changeVisibility("lightBox");
		changeVisibility("boundaryBigImage"); 
		
		// if the lightbox is visible, show the image's info
		if (document.getElementById("lightBox").className == "unhidden"){
			displayLightBoxInfo(imageID);
		}
	} // if CBclicked
	CBclicked = false;
	DBclicked = false; 	
} // displayLightBox

// first remove old, then create new edit button in lightbox
function createEditButton (imageID) {
	var positionImage = document.getElementById("positionBigImage");
	//remove old edit
	removeElement("edit");
	// create new edit
	var btn = document.createElement("BUTTON");  //<button> element
	var t = document.createTextNode("Edit"); // Create a text node
	btn.appendChild(t);   
	btn.onclick = function(){edit(imageID)};
	btn.className = "albumButton";
	btn.style.marginRight = "10px";
	btn.setAttribute("id", "edit");
	positionImage.appendChild(btn);//to show on myView
} // createEditButton

// first remove old, then create new download button in lightbox
function createDownloadButton (imageID) {
	var positionImage = document.getElementById("positionBigImage");
	// remove old download
	removeElement("downloadBTN");
	// create new download
	var btn = document.createElement("BUTTON");  //<button> element
	var t = document.createTextNode("Download"); // Create a text node
	btn.appendChild(t);   
	btn.onclick = function(){download(imageID)};
	btn.className = "albumButton";
	btn.style.marginRight = "10px";
	btn.setAttribute("id", "downloadBTN");
	positionImage.appendChild(btn);//to show on myView
} // createDownloadButton

function removeElement (id) {
	if (document.getElementById(id)) {
		document.getElementById(id).remove(); 
	} // if
} // removeElement

function setHiddenIfExists (id) {
	var elemnt = document.getElementById(id);
	if (elemnt) {
		elemnt.className = "hidden"; 
	} // if
} // setHiddenIfExists

// thumbnail checkmark toggle
function check (ID, UID) {
	var check = document.getElementById(ID);
	check.style.display = (check.style.display == "block") ? "none" : "block"; 
	if (check.style.display == "block") { 
		imagesToDelete.push(UID);
	} else {
		var index = imagesToDelete.indexOf(UID);
		if (index > -1) {
		   imagesToDelete.splice(index, 1);
		} // if  
	} // if/else

	CBclicked = true; 
	setCookie('imagesToDelete', JSON.stringify(imagesToDelete), 1);
} // check

// changes the visibility of given html div
function changeVisibility(divID) {
	var element = document.getElementById(divID);
	if (element) {
		element.className = (element.className == "hidden")? "unhidden" : "hidden";
	} // if
} // changeVisibility

// sort the images the user is currently viewing by date, first or last name
function sort(parameter) {
	var currentImages; 
	if (isEditor == 1) {
		currentImages = visibleImagesCookie; 
	} else if (isEditor == 0) {
		currentImages = publicImagesCookie;
	} // if/else if
	var currentUIDs = [];
	var parameterArray = [];
	var newCurrentImages = [];
	
	// find all current UIDs
	for(var i = 0; i < currentImages.length; i++){
		currentUIDs.push(currentImages[i].UID);
	} // for
	
	// make a new array of just the parameter
	for(var i = 0; i < currentImages.length; i++){
		if(parameter == "fName"){
			parameterArray.push(currentImages[i].firstName.toLowerCase());
		} else if (parameter == "lName"){
			parameterArray.push(currentImages[i].lastName.toLowerCase());
		} else if (parameter == "date"){
			parameterArray.push(currentImages[i].UID);
		} // if/else if
	} // for 
	
	// sort the parameter array based on desired parameter
	if (parameter == "fName" || parameter == "lName"){
		parameterArray.sort();
	} else {
		parameterArray.sort((a,b) => a - b);
	} // if/else if
	
	// reorder currentImages into newCurrentImages based on sorted order in parameterArray
	for (var i = 0; i < parameterArray.length; i++) {
		for (var j = 0; j < currentImages.length; j++) {
			if (parameter == "fName"){
				if (currentImages[j].firstName.toLowerCase() == parameterArray[i] && !newCurrentImages.includes(currentImages[j])){
					newCurrentImages.push(currentImages[j]);
				} // if
			} else if (parameter == "lName"){
				if (currentImages[j].lastName.toLowerCase() == parameterArray[i] && !newCurrentImages.includes(currentImages[j])){
					newCurrentImages.push(currentImages[j]);
				} // if
			} else if (parameter == "date"){
				if (currentImages[j].UID == parameterArray[i] && !newCurrentImages.includes(currentImages[j])){
					newCurrentImages.push(currentImages[j]);
				} // if
			} // if/else if
		} // for j
	} // for i
	
	if (isEditor == 1){
		setCookie('visibleImages', JSON.stringify(spliceArray(newCurrentImages)), 1);
	} else if (isEditor == 0){
		setCookie('publicVisibleImages', JSON.stringify(spliceArray(newCurrentImages)), 1);
	} // if/else if
	window.location.reload(false); 
} // sort

// runs the ajax to approve images
function runAJAX(imageID) {
	var myArr = allJSON;
		
		// sets approved of image clicked to true
		for (var i = 0; i < myArr.length; i++){
			if (myArr[i].UID == imageID){
				myArr[i].approved = "true"; 
			} // if
		} // for
		
		// ajax call to run the php to update the JSON file with the approved image
		$.ajax({
			type: "POST",
			url: 'requestAJAX.php',
			data: {newJSON: JSON.stringify(myArr)},
			success: function(){
				window.location.reload(false); 
			} // success   
		}); 
} // runAJAX

// downloads the single image, or all
function download (imageID) {
	var myArr = allJSON;
	var imageName = ""; 
	
	// sets a GET variable to "all" or the name of the specific image
	if (imageID == "all") {
		window.location="download.php?filename=all";
	} else {
		for (var i = 0; i < myArr.length; i++){
			if (myArr[i].UID == imageID){
				imageName = imageID + "." + myArr[i].imageType;
				window.location="download.php?filename=" + imageName; // runs the php download script
			
				DBclicked = true; 
			} // if
		} // for
	} // if/else
} // download

// editing the name, description and tags of photos
function edit (UID) {
	var myArr = visibleImagesCookie; 
	var positionImage = document.getElementById("positionBigImage");
	var thumbsBox = document.getElementById("thumbsBox"); 
	var editID = []; // array of ID/ID's of image/images to edit
	var singleID; //
	var counter = 0; 
	var array; // used for bandaiding plus signs in spaces
	if (visibleImagesCookie.length > 0) {
		for (var i = 0; i < myArr.length; i++){
			var firstName = myArr[i].firstName;
			var lastName = myArr[i].lastName;
			var description = myArr[i].description;
			var tags = myArr[i].tags;
			array = firstName.split('+'); // name without the plus's
			firstName = [""];
			for(var j = 0; j < array.length; j++) {
				firstName += array[j] + " ";
			} // for
			array = lastName.split('+');
			lastName = [""];
			for(var j = 0; j < array.length; j++) {
				lastName += array[j] + " ";
			} // for
			array = description.split('+');
			description = [""];
			for(var j = 0; j < array.length; j++) {
				description += array[j] + " ";
			} // for
			array = tags.split('+');
			tags = [""];
			for(var j = 0; j < array.length; j++) {
				tags += array[j] + " ";
			} // for
			if (UID == "all" && myArr[i].approved == "true") {
				removeElement("editAll");
				inputClicked = true; 
				editID[counter] = myArr[i].UID;
				singleID = "all";  				
				for (var j = 0; j < 4; j++) {
					switch (j) {
						case 0: createInput("editFName" + editID[counter], firstName, "First Name: ", false, editID[counter]); break;
						case 1: createInput("editLName" + editID[counter], lastName, "Last Name: ", false, editID[counter]); break; 					
						case 2: createInput("editDes" + editID[counter], description, "Description: ", false, editID[counter]); break; 
						case 3: createInput("editTag" + editID[counter], tags, "Tags: ", false, editID[counter]); break; 
					} // switch
				} // for
				counter++; 
			} else if (myArr[i].UID == UID){ 
				removeElement("edit");
				editID[0] = myArr[i].UID;
				singleID = i; 
				for (var j = 0; j < 4; j++) {
					switch (j) {
						case 0: createInput("editFName", firstName, "First Name: ", true, editID[counter]); break;
						case 1: createInput("editLName", lastName, "Last Name: ", true, editID[counter]); break; 		
						case 2: createInput("editDes", description, "Description: ", true, editID[counter]); break; 
						case 3: createInput("editTag", tags, "Tags: ", true, editID[counter]); break; 
					} // switch
				} // for 
			} // if
		} // for
		
		// create submit button
		var btn = document.createElement("BUTTON");  //<button> element
		var t; 
		if (UID == "all") {
			t = document.createTextNode("Submit All"); // Create a text node
		} else {
			t = document.createTextNode("Submit"); // Create a text node
		} // if/else if
		btn.appendChild(t);  			
		btn.onclick = function(){submission(editID, singleID)}; // when submit clicked
		btn.className = "albumButton";
		btn.style.marginLeft = "10px"; 
		btn.setAttribute("id", "submission");
		if (UID == "all") {
			thumbsBox.appendChild(btn);//to show on myView 
		} else {
			positionImage.appendChild(btn);//to show on myView
		} // if/else
	} // if 
} // edit

// creates the text input for each field
function createInput (inputID, name, title, lightbox, UID) {
	var displayText = document.getElementById("imageInfo");
	var inputThumbnails = document.getElementById("link" + UID);
	var input = document.createElement("input");
	var t = document.createTextNode(title); // Create a text node
	input.appendChild(t); 
	input.type = "text";
	input.value = name; 
	input.className = "editBoxes"; // set the CSS class
	input.setAttribute("id", inputID); 
	if (lightbox) {
		displayText.appendChild(input); // put it into the DOM
	} else if (inputThumbnails) {
		inputThumbnails.appendChild(input); // put it into the DOM
	} // if/else if
} // createInput

// saves all the updated JSON data to galleryInfo.json
function submission (IDtoEdit, UID) { 
	var myArr = allJSON; 
	var fname;
	var lname;
	var des;
	var tag;
	var toEdit; // index of cookie array to change
	if (UID == "all") {
		for (var i = 0; i < IDtoEdit.length; i++){  			
			fname = document.getElementById("editFName" + IDtoEdit[i]).value;
			lname = document.getElementById("editLName" + IDtoEdit[i]).value;
			des = document.getElementById("editDes" + IDtoEdit[i]).value;
			tag = document.getElementById("editTag" + IDtoEdit[i]).value;
			
			// sets the index of the cookie we need to edit 
			for (var j = 0; j < myArr.length; j++) {
				if (IDtoEdit[i] == myArr[j].UID) {
					toEdit = j;
				} // if
			} // for
			// updates cookie array
			myArr[toEdit].firstName = fname;
			myArr[toEdit].lastName = lname;
			myArr[toEdit].description = des; 
			myArr[toEdit].tags = tag;
		} // for
	} else {
		for (var j = 0; j < myArr.length; j++) {
			if (IDtoEdit[0] == myArr[j].UID) {
				toEdit = j;
			} // if
		} // for
		fname = document.getElementById("editFName").value;
		lname = document.getElementById("editLName").value;
		des = document.getElementById("editDes").value;
		tag = document.getElementById("editTag").value;
		// updates cookie array
		myArr[toEdit].firstName = fname;
		myArr[toEdit].lastName = lname;
		myArr[toEdit].description = des; 
		myArr[toEdit].tags = tag; 
	} // if/else
	// ajax call to update the JSON with the changed data
	$.ajax({
		type: "POST",
		url: 'requestAJAX.php',
		data: {newJSON: JSON.stringify(myArr)},
		success: function(){
			window.location.reload(false); 
		} // success   
	}); 

	// close the lightbox and remove all elements connected 
	removeElement("submission"); 
	if (UID == "all") {
		for (var i = 0; i < myArr.length; i++){
			removeElement("editFName" + i);
			removeElement("editLName" + i);
			removeElement("editDes" + i);
			removeElement("editTag" + i);
		} // for
	} else {
		removeElement("edit");
		removeElement("downloadBTN");
		removeElement("editFName");
		removeElement("editLName");
		removeElement("editDes");
		removeElement("editTag");
	} // if/else
	setCookie('visibleImages', JSON.stringify(spliceArray(myArr)), 1);
	inputClicked = false;
} // submission

// allows the arrows to move which lightbox image is selected
function imageSelect(direction) {
	var myArr; 
	if (isEditor == 1) {
		myArr = visibleImagesCookie;
	} else if (isEditor == 0) {
		myArr = publicImagesCookie;
	} // if/else
	var currentImage = document.getElementById("bigImage").src;
	var currentUID = parseInt(currentImage.split('/')[currentImage.split('/').length - 1]);
	// get the second to last element of the split current Image source to find the UID of the image
	var newUIDs = [];
	var nextUID;
	var currentJSON;
	var biggestUID = 0;
	var UIDChanged = false;
	
	// make a new array of just the UIDs of the images being shown
	for (var i = 0; i < myArr.length; i++){
			newUIDs.push(myArr[i].UID);
			if (myArr[i].UID > biggestUID) {
				biggestUID = myArr[i].UID; 
			} // if
	} // for

	// set the new UID based on desired direction
	if (direction == 'right'){
		for (var i = 1; i <= biggestUID - currentUID; i++){
			for (var j = 0; j < myArr.length; j++) {
				if (myArr[j].UID == currentUID + i && UIDChanged == false){
					nextUID = currentUID + i;
					UIDChanged = true;
				} // if
			} // for j
		} // for i
		if (nextUID == null) {
			nextUID = myArr[0].UID;
		} // if
	} else if (direction == 'left') {
		for (var i = 1; i <= currentUID; i++){
			// this is to check if the myArr with that UID has been deleted yet ie if one of them matches it doesnt matter as long as one does
			for (var j = 0; j < myArr.length; j++) {
				if (myArr[j].UID == currentUID - i && UIDChanged == false){
					nextUID = currentUID - i;
					UIDChanged = true;
				} // if
			} // for j
		} // for i
		if (nextUID == null) {
			nextUID = biggestUID;
		} // if
	} // if/else
	
	// find out the JSON data of the new image
	for (var i = 0; i < myArr.length; i++){
		if (myArr[i].UID == nextUID) currentJSON = myArr[i];
	} // for
	
	displayLightBox(-1);
	displayLightBox(nextUID + "." + currentJSON.imageType);
	
	if (isEditor == 1) {
		createEditButton(nextUID);
	} // if
} // imageSelect

// displays the data for the corresponding image
function displayLightBoxInfo(imageID) {
	var nameElement = document.getElementById("name");
	var descriptionElement = document.getElementById("description");
	var tagsElement = document.getElementById("tags");
	var firstName = "";
	var lastName = "";
	var description = "";
	var tags = "";
	var currentJSON; // the JSON data for the current image in lightbox
	var array; // used for bandaiding plus signs in image info
	
	// if close (x) button is pressed
	if (imageID == -1){
		nameElement.innerHTML = ""; 
		descriptionElement.innerHTML = "";
		tagsElement.innerHTML = "";
		
		setHiddenIfExists("editFName");
		setHiddenIfExists("editLName");
		setHiddenIfExists("editDes");
		setHiddenIfExists("editTag");
	} else {
		var myArr;
		if (isEditor == 1) {
			myArr = visibleImagesCookie;
		} else if (isEditor == 0) {
			myArr = publicImagesCookie;
		} // if/ else if
		var UID = parseInt(imageID.split(".")[0]);
			
		for (var i = 0; i < myArr.length; i++) {
			if (myArr[i].UID == UID) {
				currentJSON = myArr[i];
			} // if
		} // for

		firstName = currentJSON.firstName;
		lastName = currentJSON.lastName;
		description = currentJSON.description;
		tags = currentJSON.tags;
		
		array = firstName.split('+');
		firstName = [""];
		for(var i = 0; i < array.length; i++) {
			firstName += array[i] + " ";
		} // for
		array = lastName.split('+');
		lastName = [""];
		for(var i = 0; i < array.length; i++) {
			lastName += array[i] + " ";
		} // for
		array = description.split('+');
		description = [""];
		for(var i = 0; i < array.length; i++) {
			description += array[i] + " ";
		} // for
		array = tags.split('+');
		tags = [""];
		for(var i = 0; i < array.length; i++) {
			tags += array[i] + " ";
		} // for
			
		nameElement.innerHTML = "Name: " + firstName + " " + lastName; 
		descriptionElement.innerHTML = "Description: " + description;
		tagsElement.innerHTML = "tags: " + tags;		
	} // if/else
} // displayLightBoxInfo

// changes what images the user is viewing to all, public, or private
function changeViewSettings(newSetting) {
	var newImages = []; // int array of the UID of new images the user wants to view 
	var visibleImages = []; // int array of the UID of new images the user wants to view 
	var newJSON = []; // JSON array of the new images the user wants to view 
	var allImages = document.getElementById("all");
	var publicImages = document.getElementById("public");
	var privateImages = document.getElementById("private");	
	
	if (newSetting == "all") {
		allImages.style.backgroundColor = "#8a00e6"; 
	} // if
			
	var myArr = allJSON;
	var biggestUID = 0;
			
	// find biggestUID
	for(var i = 0; i < myArr.length; i++) {
		if (myArr[i].UID > biggestUID) {
			biggestUID = myArr[i].UID; 
		} // if
	} // for
			
	// if the user wants to see private or public images
	if (newSetting != "all"){
		
		// go through the images and record the UIDs of images that match the criteria in an array called newImages
		for(var i = 0; i < myArr.length; i++) {
			if (myArr[i].access.search(newSetting) != -1 ) {
				newImages.push(myArr[i].UID);
		    } // if
		} // for
				
		//go through all the images, and make the ones with UIDs that dont match the criteria hidden
		for (var i = 0; i < myArr.length; i++) {
			var img = document.getElementById(myArr[i].UID);
					
			for (var j = 0; j < newImages.length; j++) {
				if (myArr[i].UID == newImages[j]){
					visibleImages.push(i);
				} // if
			} // for j	
		} // for i
				
		for (var i = 0; i < visibleImages.length; i++){
			newJSON.push(myArr[visibleImages[i]]);
		} // for
				
		setCookie('visibleImages', JSON.stringify(spliceArray(newJSON)), 1);
		window.location.reload(false);
			
	} else {
				
		// make all images unhidden if the user wants to see all images
		for (var i = 0; i < myArr.length; i++) {
		    visibleImages.push(i);
		}
				
		for (var i = 0; i < visibleImages.length; i++){
			newJSON.push(myArr[visibleImages[i]]);
		}
				
		setCookie('visibleImages', JSON.stringify(spliceArray(newJSON)), 1);
		window.location.reload(false); 	
	} // if/else
} // changeViewSettings

// searches for in image containing the search term in the images the user is viewing
function search() {
	var searchBar = document.getElementById("searchBar");; 
	var searchTerm = searchBar.value;
	var newImages = []; 
	var newUIDs = [];
	var newJSON = []; // JSON array of the new images the user wants to view 
	var myArr; // JSON array of images user is currently viewing
	searchBar.value = "";
	
	// searches only within visible images	
	if (isEditor == 1) {
		myArr = visibleImagesCookie;
	} else if (isEditor == 0) {
		myArr = publicImagesCookie;
	} // if/else if
	
	for(var i = 0; i < myArr.length; i++) {
	   if (myArr[i].firstName.search(searchTerm) != -1 ) {
			newImages.push(myArr[i].UID);
	   } // if
	} // for
	for(var i = 0; i < myArr.length; i++) {
	   if (myArr[i].lastName.search(searchTerm) != -1 ) {
			newImages.push(myArr[i].UID);
	   } // if
	} // for
	for(var i = 0; i < myArr.length; i++) {
	   if (myArr[i].description.search(searchTerm) != -1 ) {
			newImages.push(myArr[i].UID);
	   } // if
	} // for
	for(var i = 0; i < myArr.length; i++) {
	   if (myArr[i].tags.search(searchTerm) != -1 ) {
			newImages.push(myArr[i].UID);
	   } // if
	} // for
	
	for (var i = 0; i < myArr.length; i++) {
		for (var j = 0; j < newImages.length; j++) {
			if (myArr[i].UID == newImages[j] && !newUIDs.includes(myArr[i].UID)){
				newUIDs.push(myArr[i].UID);
			} // if
		} // for j
	} // for i
	
	for (var i = 0; i < myArr.length; i++) {
		for (var j = 0; j < newUIDs.length; j++) {
			if (myArr[i].UID == newUIDs[j]){
				newJSON.push(myArr[i]);
			} // if
		} // for j
	} // for i
	
	if (isEditor == 1){
		setCookie('visibleImages', JSON.stringify(spliceArray(newJSON)), 1);
	} else if (isEditor == 0){
		setCookie('publicVisibleImages', JSON.stringify(spliceArray(newJSON)), 1);
	} // if/else if
	window.location.reload(false);
} //  search

// returns an array without any non-approved images
function spliceArray(myArr) {
	for (var i = 0; i < myArr.length; i++) {
		if (myArr[i].approved == "false") {
			myArr.splice(i, 1); 
		} // if
	} // for
	return myArr; 
} // spliceArray

// returns cookie full of object data
function getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    } // while
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    } // if
  } // for
  return "";
} // getCookie

// sets cookie full of object data
function setCookie(cname, cvalue, exdays) {
	
	// set expiry time
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = "expires="+ d.toUTCString();
	// make cookie
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
} // setCookie