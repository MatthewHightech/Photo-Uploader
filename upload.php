<?php

/*************************************

* Name: upload.php
* Authors: Cyrus Parsons and Matt Smith
* Date: January 17, 2020
* Purpose: Handles image uploading from user, with 
	error checking and JSON data uploading.

*************************************/
	
	$firstName = "";
	$firstNameError = "";
	$lastName = "";
	$lastNameError = "";
	$description = "";
	$descriptionError = "";
	$tags = "";
	$tagsError = "";
	$rights = "";
	$rightsError = "";
	$access = "";
	$accessError = "";
	$fileError = "";
	$errors = "false";
	
	// error checking
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		$errors = "false";
		if (empty($_POST["firstName"])) {
			$firstNameError = "*First name is required";
			$errors = "true";
		} else {
			$firstName = test_input($_POST["firstName"]);
		} // else
		if (empty($_POST["lastName"])) {
			$lastNameError = "*Last name is required";
			$errors = "true";
		} else {
			$lastName = test_input($_POST["lastName"]);
		} // else
		if (empty($_POST["description"])) {
			$descriptionError = "*Description is required";
			$errors = "true";
		} else {
			$description = test_input($_POST["description"]);
		} // else 
		if (empty($_POST["tags"])) {
			$tagsError = "*Image tags are required";
			$errors = "true";
		} else {
			$tags = test_input($_POST["tags"]);
		} // else
		if (empty($_POST["rights"])) {
			$rightsError = "*rights are required";
			$errors = "true";
		} else {
			$rights = test_input($_POST["rights"]);
		} // else
		if (empty($_POST["access"])) {
			$accessError = "*Access preferences are required";
			$errors = "true";
		} else {
			$access = test_input($_POST["access"]);
		} // else
		if (empty($_FILES["fileToUpload"]["name"])){
			$fileError = "*File necessary";
			$errors = "true";
		} else {
			$fileError = uploadImageCheck(false);
			if ($fileError != "") $errors = "true";
		} // else
	} // if
	include "Header.inc";
	
	// if there are no errors and submit is pressed
	if ($_SERVER["REQUEST_METHOD"] == "POST" && $errors == "false") {
		uploadImageCheck(true);
		if ($uploadOk == 1){
			$_POST["UID"] = $UID;
			$_POST["imageType"] = $imageType;
			$_POST["approved"] = "false";
			writeJSON();
			$firstName = "";
			$lastName = "";
			$description = "";
			$tags = "";
			$rights = "";
			$access = "";
			$_POST["rights"] = "";
		} // if
	} // if
	
	include "Body.inc";
	
	include "Footer.inc";
	
	//----------Functions-------------
	
	// cleans input by getting rid of characters that could pose a threat
	function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		$data = filter_var($data, FILTER_SANITIZE_STRING);
		return $data;
	} // test_input
	
	// makes a file if file does not exist
	function makeJSON($file) {
		if (!file_exists($file)) {
			touch($file);
		} // if
	} // makeJSON
	
	// makes a folder if the folder name doesnt yet exist
	function makeFolder($folder) {
		if (!file_exists($folder)) {
			mkdir($folder, 0777);
		} // if
	} // MakeFolder
	
	// finds the next UID 
	function makeUID($file) { 
		if (!file_exists($file)){
			return 0;
		} else {
			$openedFile = fopen($file, "r");
			$UID = (int)fread($openedFile,filesize($file));
			fclose($openedFile);
			return $UID;
		} // else
	} // makeUID
	
	// increments the UID in the UID text file
	function incrementUID($file, $UID) {
		$openedFile = fopen($file, "w");
		fwrite($openedFile, (int)$UID + 1);
		fclose($openedFile);
	} // incrementUID
	
	// writes the post array to the JSON file galleryInfo.json
	function writeJSON() {
		
		$file = "galleryinfo.json";
		makeJSON($file);
		
		// read json file into array of strings
		$filearray = file($file);

		// create one string from the file
		$jsonstring = "";
		foreach ($filearray as $line) {
		$jsonstring .= $line;
		}

		//decode the string from json to PHP array
		$phparray = json_decode($jsonstring, true);

		// add form submission to data (this does NOT remove submit button)
		$phparray [] = $_POST;

		// encode the php array to formatted json 
		$jsoncode = json_encode($phparray, JSON_PRETTY_PRINT);

		// write the json to the file
		file_put_contents($file, $jsoncode); 
	}
	
	// checks if the image is ok to upload, and if $uploadImage == true it uploads the image
	function uploadImageCheck($uploadImage) {
		$target_dir = "UploadedImages/";
		makeFolder($target_dir);
		global $UID;
		$UID = makeUID("identifier.txt");
		global $imageType;
		$imageType = explode("/", $_FILES["fileToUpload"]["type"])[1];
		$targetFile = $target_dir . $UID . "." . $imageType;
		global $uploadOk;
		$uploadOk = 1;
		$imageFileType = strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));
		// Check if image file is an actual image or fake image
		if(isset($_POST["submit"])) {
			$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
			if($check !== false) {
				$uploadOk = 1;
			} else {
				$uploadOk = 0;
				return "*File is not an image.";
			} // else
		} // if
		// Check if file already exists
		if (file_exists($targetFile)) {
			$uploadOk = 0;
			return "*Sorry, file already exists.";
		} // if
		// Allow certain file formats
		if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
			$uploadOk = 0;
			return "*Sorry, only JPG, PNG files are allowed.";
		} // if
		// check if file is too large
		if((int)$_FILES["fileToUpload"]["size"] > 4000000){
			$uploadOk = 0;
			return "*Sorry, the file must be under 4mb";
		} // if
		// Check if $uploadOk is set to 1 and if uploadImage is true, otherwise do nothing and error message gets sent
		if ($uploadOk == 1 && $uploadImage == true) {
			if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
				incrementUID("identifier.txt", $UID);
				if ($imageFileType == "png"){
					$sourceImg = @imagecreatefrompng($targetFile);
					// first parameter is true if png image
					makeThumbnail(true, $sourceImg, $UID);
				} else {
					$sourceImg = @imagecreatefromjpeg($targetFile);
					// first parameter is true if png image
					makeThumbnail(false, $sourceImg, $UID);
				} // else 
			} // if
		} // if
	} // uploadImageCheck
	
	// creates a thumbnail for the uploaded image
	function makeThumbnail($imageFileType, $sourceImg, $UID) {
		
		$dir = "Thumbnails/";
		makeFolder($dir);
		
		$new_w = 120;
		$new_h = 120;
			
		$orig_w = imagesx($sourceImg);
		$orig_h = imagesy($sourceImg);
			
		$w_ratio = ($new_w / $orig_w);
		$h_ratio = ($new_h / $orig_h);
			
		if ($orig_w > $orig_h ) {//landscape
			$crop_w = round($orig_w * $h_ratio);
			$crop_h = $new_h;
		} elseif ($orig_w < $orig_h ) {//portrait
			$crop_h = round($orig_h * $w_ratio);
			$crop_w = $new_w;
		} else {//square
			$crop_w = $new_w;
			$crop_h = $new_h;
		} // else
		
		$destImg = imagecreatetruecolor($new_w,$new_h);
		
		// if image is png
		if ($imageFileType) {
			imageAlphaBlending($destImg, false);
			imageSaveAlpha($destImg, true);
		} // if
		imagecopyresampled($destImg, $sourceImg, 0 , 0 , 0, 0, $crop_w, $crop_h, $orig_w, $orig_h);
		
		if($imageFileType){
			if(imagepng($destImg, $dir . $UID . ".png")) {
				imagedestroy($destImg);
				imagedestroy($sourceImg);
			} else {
				echo "could not make thumbnail image";
				exit(0);
			} // else 
		} else {
			if(imagejpeg($destImg, $dir . $UID . ".jpeg")) {
				imagedestroy($destImg);
				imagedestroy($sourceImg);
			} else {
				echo "could not make thumbnail image";
				exit(0);
			} // else 
		} // else
	} // makeThumbnail
	
?>