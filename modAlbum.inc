 <h1>Moderator Gallery</h1>

<div class="uploader">
	<div class="container">
		
		<button class="albumButton" onclick="log('out')" type="button">Logout</button>
		<button class="albumButton" type="button" onclick="window.location.href = 'https://photouploader-gallery.herokuapp.com/index.php?page=approval';">Awaiting Approval</button> <br>
		<button class="albumButton" onclick="window.location.href = 'https://photouploader-gallery.herokuapp.com/index.php?page=delete';" type="button" >Delete Selected Images</button>
		<button id="downloadAll" class="albumButton" onclick="download('all')">Download All</button>
		<button id="editAll" class="albumButton" onclick="edit('all')">Edit All</button>

	</div>
	<div class="thumbnailBox">
		<div class="searchBarDiv">
			<input type="text" name="search" placeholder="Search..." id="searchBar"><button id="searchButton" onclick="search()"></button>
		</div>
		<div class="viewSettings" id="container">
		
			<button id="all" onclick="changeViewSettings('all')">All</button>
			<button id="public" onclick="changeViewSettings('public')">Public</button>
			<button id="private" onclick="changeViewSettings('private')">Private</button>
		</div>
		
		<div class="sort" id="container">
		
			<button id="date" onclick="sort('date')">Date</button>
			<button id="fName" onclick="sort('fName')">Firstname</button>
			<button id="lName" onclick="sort('lName')">Lastname</button>
		</div>
		
		
		
