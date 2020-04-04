
	<h1>Grad Photo Uploader</h1>

<div class="formContainer"> 
	<div id="contact">
		<form method="post" action="upload.php" enctype="multipart/form-data">
			<fieldset>
				<input placeholder="First Name" type="text" name="firstName" value="<?php echo $firstName ?>" tabindex="1" autofocus>
				<span class="error"><?php echo $firstNameError; ?></span>
			</fieldset>
			<fieldset>
				<input placeholder="Last Name" type="text" name="lastName" value="<?php echo $lastName ?>" tabindex="2">
				<span class="error"><?php echo $lastNameError; ?></span>
			</fieldset>
			<fieldset>
				<label class="file">
					<span id="file-selected"></span>
					<input type="file" name="fileToUpload"/>
					<i class="fa fa-cloud-upload"></i>
					<span class="error"><?php echo $fileError; ?></span>
				</label>
			</fieldset>
			<fieldset>
				<input placeholder="Description of Photo" name="description" value="<?php echo $description ?>" type="text" tabindex="4">
				<span class="error"><?php echo $descriptionError; ?></span>
			</fieldset>
			<fieldset>
				<input placeholder="Photo Tags" type="text" name="tags" value="<?php echo $tags ?>" tabindex="5">
				<span class="error"><?php echo $tagsError; ?></span>
			</fieldset>
			<fieldset>
				<label class="checkboxContainer">I retain the copyright to this image and release it for use on this site.
					<input class="checkbox" type="checkbox" name="rights" tabIndex="6"
					<?php if(!empty($_POST["rights"])) echo "checked"; ?> >
					<span class="checkmark"></span>
				</label>
				<span class="error"><?php echo $rightsError; ?></span>
			</fieldset>
			<fieldset>
				<div id="radioButton1">
					<label class="radioContainer"> Private
						<input type="radio" name="access" value="private" id="private"
						<?php if($access == "private") echo "checked"; ?>>
						<span class="radioDot"></span>
					</label>						
				</div>
				<div id="radioButton2">
					<label class="radioContainer"> Public
						<input type="radio" name="access" value="public" id="public"
						<?php if($access == "public") echo "checked"; ?>>
						<span class="radioDot"></span>
					</label>
				</div>
				<span class="error"><?php echo $accessError; ?></span>
			</fieldset>
			<fieldset>
				<div class="submit">
					<button type="submit" id="contact-submit" data-submit="...Sending" onclick="window.location.href = 'https://photouploader-gallery.herokuapp.com/index.php';">Upload Image</button>
				</div>
			</fieldset>
		</form>
		<fieldset>
			<div class="viewAlbumDiv">
				<button class="albumButton" type="button" onclick="window.location.href = 'https://photouploader-gallery.herokuapp.com/index.php';">View Album</button>
			</div>
		</fieldset>
	</div>
</div>
