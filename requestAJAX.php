<?php

/*************************************

* Name: requestAJAX.php
* Authors: Cyrus Parsons and Matt Smith
* Date: January 17, 2020
* Purpose: rewrites the JSON data with the new 
	information given via the $_POST array.

*************************************/

	unlink("galleryinfo.json");

	// decode the new JSON data from the $_POST array
	$stringyJSON = json_decode($_POST["newJSON"], true);
	
	// encode the new JSON
	$finalJSON = json_encode($stringyJSON, JSON_PRETTY_PRINT);

	$file = "galleryinfo.json";
	touch($file);
	
	// write the new JSON into the galleryinfo file
	file_put_contents($file, $finalJSON);
?>