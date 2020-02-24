<?php

/*************************************

* Name: download.php
* Authors: Cyrus Parsons and Matt Smith
* Date: January 17, 2020
* Purpose: downloads the images necessary, as determined by the $_GET data.

*************************************/

	global $allJSON;
	$allJSON = getAllJSON(); // all the JSON data
	$approvedImages = array(); // all images with approved set to true
	$visibleImages = json_decode($_COOKIE['visibleImages'], true); // the images the user is currently viewing
	
	// set approved images
	if (isset($visibleImages)){
		for ($i = 0; $i < sizeOf($visibleImages, 0); $i++){
			array_push($approvedImages,$visibleImages[$i]["UID"]);
		} // for
	} else {
		if (file_exists("galleryinfo.json") && is_countable($allJSON[0])){
			for ($i = 0; $i < sizeOf($allJSON[0], 0); $i++){
				if ($allJSON[0][$i]["approved"] == "true"){ array_push($approvedImages,$allJSON[0][$i]["UID"]);}
			} // for
		} // for
	} // if/else
		
		// DOWNLOAD ----
		if (isset($_GET["filename"]) && $_GET["filename"] == "all") {
			if (file_exists("images.zip")) {
				unlink("images.zip");
			} // if
			$files = array(); 
			// read the directory uploadedimages and 
			// add each image location to the $files array
			for ($i = 0; $i < sizeOf($allJSON[0], 0); $i++){
				if (in_array($allJSON[0][$i]["UID"], $approvedImages)) { 
					$imageNames = $allJSON[0][$i]["UID"] . "." . $allJSON[0][$i]["imageType"]; 
					 
					array_push($files,$imageNames);
				} // if
			} // for
			
			// check the content of files is correct
			//$filepath = "UploadedImages/"; 
			$zipname = 'images.zip';  // 
			$zip = new ZipArchive;
			
			$res = $zip->open('images.zip', ZipArchive::CREATE);
			foreach ($files as $file) {
				$zip->addFile("UploadedImages/" . $file);
			} // foreach

			$zip->close();
			header("Content-Type: application/zip");
			header("Content-Transfer-Encoding: Binary");
			header("Content-Length: ".filesize($zipname));
			header("Content-Disposition: attachment; filename=\"".basename($zipname)."\"");
			readfile($zipname);
			exit;
			
		} else if (isset($_GET["filename"])) {
			$file = $_GET["filename"]; 
			$filepath = "UploadedImages/" . $file;
		
		if(file_exists($filepath)) {
		    header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.$file.'"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filepath));
			ob_clean();
			flush();
			
			// download 
			readfile($filepath);
			exit;
		} // if
	} // End of Download ----
	
	
	
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
	} // getALLJSON
	
	function makeJSON($file) {
		if (!file_exists($file)) {
			touch($file);
		} // if
	} // makeJSON
?>