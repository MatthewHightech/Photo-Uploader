<?php

/*************************************

* Name: index.php
* Authors: Cyrus Parsons and Matt Smith
* Date: January 17, 2020
* Purpose: PHP used for the public, moderator, and 
	awaiting approval galleries.

*************************************/

	session_start();
	
	$file = "galleryinfo.json"; // JSON file
	$dir = "UploadedImages"; // file with main images
	$dirThumb = "Thumbnails"; // file with thumbnails
	
	// default isEditor is False
	if (!isset($_SESSION["isEditor"])){
		$_SESSION["isEditor"] = false;
	} // if
	
	// sets the page variable based on the GET
	if (isset($_GET["page"])){
		if ($_GET["page"] == "approval") {
			$page = "approval";
		} else {
			$page = "gallery";
		} // if/else
	} else {
		$page = "gallery";
	} // if/else
	
	global $allJSON;
	$allJSON = getAllJSON(); // all the JSON data
	$newUIDs = array(); // UIDs of visible images
	$privateUIDs = array(); //UIDs of the private images
	$publicUIDs = array(); // UIDs of the public images
	$imagesToDelete = array(); // UIDs of the images to delete
	$visibleImages = array(); // JSON data of the visible images
	$unapprovedImages = array(); // UIDs of unapproved images 
	$biggestUID = 0;
	
	// fills publicVisibleImages with JSON data
	if (file_exists("galleryinfo.json") && is_countable($allJSON[0])){
		$initialPublicImages = array(); // public images when the site load for the first time without cookies set yet
		for ($i = 0; $i < sizeOf($allJSON[0], 0); $i++){
			if ($allJSON[0][$i]["access"] == "public" && $allJSON[0][$i]["approved"] == "true") {
				array_push($initialPublicImages,$allJSON[0][$i]);
			} // if
		} // for
		setCookie('publicVisibleImages', JSON_encode($initialPublicImages), null , "/");
		
	} // if
	
	include "Header.inc";
	
	// set the images to delete
	if (isset($_COOKIE['imagesToDelete'])) {
		$imagesToDelete = json_decode($_COOKIE['imagesToDelete']);
	} else {
		$ImagesToDelete = null;
	} // if/else
	
	// set privateUIDs and publicUIDs
	if (file_exists("galleryinfo.json") && is_countable($allJSON[0])){
		for ($i = 0; $i < sizeOf($allJSON[0], 0); $i++){
			if ($allJSON[0][$i]["access"] == "private" && $allJSON[0][$i]["approved"] == "true"){ array_push($privateUIDs,$allJSON[0][$i]["UID"]);}
			else if ($allJSON[0][$i]["access"] == "public" && $allJSON[0][$i]["approved"] == "true"){ array_push($publicUIDs,$allJSON[0][$i]["UID"]);}
		} // for
	} // if
	
	//find biggest UID
	if (file_exists("galleryinfo.json") && is_countable($allJSON[0])){
		for($i = 0; $i < sizeOf($allJSON[0], 0); $i++){
			if ($allJSON[0][$i]["UID"] > $biggestUID) $biggestUID = $allJSON[0][$i]["UID"];
		} // for
	} // if
	
	// set approved images
	if (file_exists("galleryinfo.json") && is_countable($allJSON[0])){
		for ($i = 0; $i < sizeOf($allJSON[0], 0); $i++){
			if ($allJSON[0][$i]["approved"] == "false"){ array_push($unapprovedImages,$allJSON[0][$i]["UID"]);}
		} // for
	} // if
	
	// changes isEditor based on page login/logout and also instatiates the visible images arrays
	if (isset($_GET["page"])){
		$allImagesVisible = array(); 
		if ($_GET["page"] == "login"){
			$_SESSION["isEditor"] = true;
			if (file_exists("galleryinfo.json") && is_countable($allJSON[0])){
				for ($i = 0; $i < sizeOf($allJSON[0]); $i++) {
					if ($allJSON[0][$i]["approved"] == "true") {
						array_push($allImagesVisible, $allJSON[0][$i]);
					} // if
				} // for
				setCookie('visibleImages', JSON_encode($allImagesVisible), null , "/");
			} // if
		} else if ($_GET["page"] == "logout"){
			$_SESSION["isEditor"] = false;
			if (file_exists("galleryinfo.json") && is_countable($allJSON[0])){
				for ($i = 0; $i < sizeOf($allJSON[0]); $i++) {
					if ($allJSON[0][$i]["approved"] == "true" && $allJSON[0][$i]["access"] == "public") {
						array_push($allImagesVisible, $allJSON[0][$i]);
					} // if
				} // for
				setCookie('publicVisibleImages', JSON_encode($allImagesVisible), null , "/");
			} // if
		} // if/else if
	} // if
	
	// set the isEditor cookie
	$isEditor = $_SESSION["isEditor"];
	if ($isEditor){
		setCookie('isEditor', 1);
	} else {
		setCookie('isEditor', 0);
	} // if/else
	
	// clean up incoming visible images cookie, making sure all images in cookie actually exist and also making sure public users cant see private images
	if ($isEditor) {
		if (isset($_COOKIE['visibleImages'])){
			$visibleImages = json_decode($_COOKIE['visibleImages'], true); // access UIDs using $visibleImages[element]["UID"]
			$arraySize = sizeOf($visibleImages, 0);
			$visibleImages = array_values($visibleImages);
			for ($i = 0; $i < $arraySize; $i++){
				// if the visible Images array includes images that are neither public nor private unset them
				if (!in_array($visibleImages[$i]["UID"], $publicUIDs, false) && !in_array($visibleImages[$i]["UID"], $privateUIDs, false)) {
					unset($visibleImages[$i]);
				} // if
			} // for
		} else {
			// if the cookie is not set, set the visible images to all the JSON data 
			if (file_exists("galleryinfo.json")){
				$visibleImages = $allJSON;
			} // if
		} // if/else
	} else {
		if (isset($_COOKIE['publicVisibleImages'])) {
			$visibleImages = json_decode($_COOKIE['publicVisibleImages'], true); // access UIDs using $visibleImages[element]["UID"]
			$arraySize = sizeOf($visibleImages, 0);
			$visibleImages = array_values($visibleImages);
			for ($i = 0; $i < $arraySize; $i++){
				// if the visible image is not in public array, unset it
				if (!in_array($visibleImages[$i]["UID"], $publicUIDs, false)) {
					unset($visibleImages[$i]);
				} // if
			} // for
		} else {
			if (file_exists("galleryinfo.json") && is_countable($allJSON[0])){
				$newUIDs = $publicUIDs;
				for ($i = 0; $i < sizeof($allJSON[0], 0); $i++){
					// if visible images cookie isnt set, make the user see all public images
					if ($allJSON[0][$i]["access"] == "public" && $allJSON[0][$i]["approved"] == "true"){
						array_push($visibleImages,$allJSON[0][$i]);
					} // if
				} // for
			} // if
		} // if/else
	} 
	
	// make sure there are no error in array such as messed up indexes from unsetting
	$visibleImages = array_values($visibleImages);
	
	// find new UIDs aka all the UIDs of visible Images
	if (isset($visibleImages)){
		for ($i = 0; $i < sizeOf($visibleImages, 0); $i++){
			if (!in_array($visibleImages[$i]["UID"], $newUIDs, false))
			array_push($newUIDs,$visibleImages[$i]["UID"]);
		} // for
	} else {
		$newUIDs == null;
	} // if/else
	
	// save this new newImages cookie so that the js cant access images that arent actually there
	if ($isEditor){
		setCookie('visibleImages', JSON_encode($visibleImages), null , "/");
	} else {
		setCookie('publicVisibleImages', JSON_encode($visibleImages), null , "/");
	} // if/else
	
	// ----- deleting images-----
	if (isset($_GET["page"]) && $_GET["page"] == "delete"){
		$existingImages = array();
	
		// delete's main images
		if (is_dir($dir)){
			if ($dh = opendir($dir)){
				while (($image = readdir($dh)) !== false){
					if ($image != "." && $image != ".." && in_array(explode(".", $image)[0], $imagesToDelete, false)){
						unlink($dir . "/" . $image);
						deleteJSONElement(explode(".", $image)[0]); // ***NEW***  delete JSON element of corridponding image  ***NEW***
						header("Refresh:0");
					} // if
				} // while
				closedir($dh);
			} // if
		} // if
		
		// delete's thumbnail images
		if (is_dir($dirThumb)){
			if ($dh = opendir($dirThumb)){
				while (($image = readdir($dh)) !== false){
					if ($image != "." && $image != ".." && in_array(explode(".", $image)[0], $imagesToDelete, false)){
						unlink($dirThumb . "/" . $image);
					} // if
				} // while
				closedir($dh);
			} // if
		} // if
		
		// find all images that exist still
		if (is_dir($dirThumb)){
			if ($dh = opendir($dirThumb)){
				while (($image = readdir($dh)) !== false){
					if ($image != "." && $image != ".."){
						array_push($existingImages, explode(".", $image)[0]);
					} // if
				} // while
				closedir($dh);
			} // if
		} // if
		
		// add json data to new array, if UID is not in existingImages, remove all the data corrisponding to the UID from the array, and overwrite the JSON file with the new array
		if (file_exists("galleryinfo.json")){
			$array = getAllJSON();
			$arraySize = sizeOf($array[0]);
			for ($i = 0; $i < $arraySize; $i++){
				if (!in_array($array[0][$i]["UID"], $existingImages, false)){ 
					array_splice($array[0], $i, $i);
					unset($array[0][$i]);
					array_splice($visibleImages, $i, $i);
					unset($array[0][$i]);
				} // if
			} // for
		} // if
	} // if "page" == "delete"  
	
	// -------DELETE THIS BEFORE HANDING IN-----------------------------------------------
	if (isset($_GET["page"]) && $_GET["page"] == "megadelete"){
		$page = "buzzOff";
		unlink($file);
		if (is_dir($dir)){
			if ($dh = opendir($dir)){
				while (($image = readdir($dh)) !== false){
					if ($image != "." && $image != ".."){
						unlink($dir . "/" . $image);
					}
				}
				closedir($dh);
			}
			rmdir($dir);
			unlink("identifier.txt");
			unlink("galleryinfo.json");
		}
		if (is_dir($dirThumb)){
			if ($dh = opendir($dirThumb)){
				while (($image = readdir($dh)) !== false){
					if ($image != "." && $image != ".."){
						unlink($dirThumb . "/" . $image);
					}
				}
				closedir($dh);
			}
			rmdir($dirThumb);
		}
	} // -----------------------------------------------------------------------------------

	// show image gallery based on what page the user is in
	if ($page == "gallery"){
		if (!$isEditor){
			// PUBLIC GALLERY PAGE
			include "Album.inc";
			outputImages($newUIDs, $unapprovedImages, $isEditor, false, false);
			include "AlbumBottom.inc";
		} else {
			// MOD GALLERY PAGE
			include "modAlbum.inc";
			$letter = 'a'; 
			outputImages($newUIDs, $unapprovedImages, true, false);
			echo "</div>";
			include "AlbumBottom.inc";
		} // if/else
	} else if ($page == "approval") {
		if ($isEditor) {
			// APPROVAL PAGE
			include "approval.inc";
			outputImages($newUIDs, $unapprovedImages, true, true);
			echo "</div></div>";
		} else {
			// if the user tries to access the approval page but is not an editor, send them to public gallery
			include "Album.inc";
			outputImages($newUIDs, $unapprovedImages, false, false);
			include "AlbumBottom.inc";
		} // if/else
	} // if/else if
	
	include "Footer.inc"; 
	
//-------------------functions-----------------

	// output images based on conditions like if user is editor and whether they are in approval page or not
	function outputImages($newUIDs, $unapprovedImages, $isEditor, $isApprovalPage) {
		
		$file = "galleryinfo.json";
		$dirThumb = "Thumbnails";
		$letter = 'a';
		$numberOfImages = 0;
		
		echo "<div class='images' id='thumbsBox'>";
		
		if(is_dir($dirThumb)){
			$images = scandir($dirThumb);
			if (!$isApprovalPage){
				// GALLERY
				if (isset($newUIDs)){ 
					foreach($newUIDs as $image){
						for($i = 0; $i < sizeOf($images); $i++){
							$UID = explode(".", $images[$i])[0];
							
							// if the image is in the array of UIDs that need to be shown and also it is approved than show it
							if ($image == $UID && !in_array($UID, $unapprovedImages, false) && $images[$i] != "." && $images[$i] != ".."){
								
								// if editor include more links for downloading and selecting
								if ($isEditor) {
									echo "<a href='javascript: displayLightBox(\"$UID\")' class='link' id='link" . $UID . "'><img class='imgThumb' id='" . $UID . "' src=Thumbnails/" . $UID . " alt='thumbnail'>";  
									echo "<div onclick='check(\"$letter\", \"$UID\")' class='checkboxThumb'><img id='". $letter ."' class='checkThumb' src='checkmark.png' alt='checkbox'></div>";
									echo "<div class='download' onclick='download(\"$UID\")'><img class='downloadIcon' src='download.png' alt='downloadImg'></div>"; 
									echo "</a>";
								} else {
									echo "<a href='javascript: displayLightBox(\"$UID\")' class='link'><img class='imgThumb' id='" . $UID . "' src=Thumbnails/" . $UID . " alt='thumbnail'></a>";  
								} // if/else
								$letter++; 
								$numberOfImages++;
							} // if
						} // for 
					} // foreach
				} else {
					// if newUIDs are not set aka the user is an editor with no visible images cookie
					for($i = 0; $i < sizeOf($images); $i++){
						$UID = explode(".", $images[$i])[0];
							
						// if editor include more links for downloading and selecting
						if ($isEditor) {
							echo "<a href='javascript: displayLightBox(\"$UID\")' class='link'><img class='imgThumb' id='" . $UID . "' src=Thumbnails/" . $UID . ">";  
							echo "<div onclick='check(\"$letter\", \"$UID\")' class='checkboxThumb'><img id='". $letter ."' class='checkThumb' src='checkmark.png'></div>";
							echo "</a>";
						} else {
							echo "<a href='javascript: displayLightBox(\"$UID\")' class='link'><img class='imgThumb' id='" . $UID . "' src=Thumbnails/" . $UID . "></a>";  
						} // if/else
						$letter++; 
						$numberOfImages++;
					} // for i
				} // if/else
			} else { 
				// APPROVAL PAGE
				for ($i = 0; $i < sizeOf($images); $i++){
					$UID = explode(".", $images[$i])[0];
					// if image is not approved aka in the array of unapproved images than show it
					if (in_array($UID, $unapprovedImages, false) && $images[$i] != "." && $images[$i] != ".."){
						echo "<a href='javascript: runAJAX(\"$UID\")' class='link'><img class='imgThumb' id='" . $UID . "' src=Thumbnails/" . $UID . "></a>";
						$numberOfImages++;
					} // if
				} // for
			} // if/else
		} // if
		
		if ($numberOfImages == 0) {
			echo "<p class='message'>No Images Found</p>";
		} // if
		
		echo "</div>";
	} // outputImages
	
	// retrieves all the JSON from the galleryinfo.JSON file and returns it as an array
	function getAllJSON() {
		$file = "galleryinfo.json";
		makeJSON($file);
		
		// read json file into array of strings
		$filearray = file($file);

		// create one string from the file
		$jsonstring = "";
		foreach ($filearray as $line) {
			$jsonstring .= $line;
		} // foreach

		//decode the string from json to PHP array
		$phparray [] = json_decode($jsonstring, true);
		
		return $phparray;
	} // getAllJSON
	
	// if the given file name doesnt exist than create it
	function makeJSON($file) {
		if (!file_exists($file)) {
			touch($file);
		} // if
	} // makeJSON
	
	// deletes the given JSON UID from the galleryinfo.json file
	function deleteJSONElement ($uidToDelete) {
		//get all your data on file
		$data = file_get_contents('galleryinfo.json');

		// decode json to associative array
		$json_arr = json_decode($data, true);
			
		for ($i = 0; sizeof($json_arr) > $i; $i++) {
			if ($json_arr[$i]["UID"] == $uidToDelete) {
				unset($json_arr[$i]);  
			} // if
		} // for
		
		// rebase array
		$json_arr = array_values($json_arr);

		// encode array to json and save to file
		file_put_contents('galleryinfo.json', json_encode($json_arr));
	} // deleteJSONElement
?>