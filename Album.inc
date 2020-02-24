<h1>Grad Photo Uploader</h1>

<div class="uploader">
	<div class="container">
		<button class="albumButton" type="button" onclick="window.location.href = 'http://142.31.53.141/~myrus/GradPhotoUploader/upload.php';">Upload</button>
		<button class="albumButton" onclick="log('in')" type="button">Login as moderator</button>
		<button class="albumButton" onclick="log('out')" type="button">Show All</button>

	</div>
	<div class="thumbnailBox">
		<div class="publicSearchBarDiv">
			<input type="text" name="search" placeholder="Search..." id="searchBar"><button id="searchButton" onclick="search()"></button>
		</div>
	
	<div class="sort" id="container">
		
		<button id="date" onclick="sort('date')">Date</button>
		<button id="fName" onclick="sort('fName')">Firstname</button>
		<button id="lName" onclick="sort('lName')">Lastname</button>
	</div>
