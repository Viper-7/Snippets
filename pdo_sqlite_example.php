<?php

	// Connect to the DB.
	// In this case we're using sqlite, with a file path of /temp/barcodes.sqlite
	$db = new PDO('sqlite:/temp/barcodes.sqlite');

	// Create the table if it doesn't exist (you wouldn't have this on mysql or such)
	$db->query('
		CREATE TABLE IF NOT EXISTS Barcodes 
		(
			id INTEGER PRIMARY KEY AUTOINCREMENT,
			Barcode VARCHAR(255)
		)
	');

	// If a barcode has been submitted
	if(!empty($_POST['barcode']))
	{
		// Create a statement to insert the barcode into the database
		$stmt = $db->prepare('INSERT INTO Barcodes (Barcode) VALUES (:barcode)');

		// Execute the statement using the submitted barcode
		$stmt->execute($_POST);

		// Output the saved barcode to show in response
		echo htmlentities($_POST['barcode']);

		// End the script here
		die();
	}

?><!doctype html>
<html>
<head>
	<title>Barcode Scanner</title>
	<script src="http://code.jquery.com/jquery-1.4.2.min.js"></script>
	<script type="text/javascript">
		$(function() {
			// Fetch a handle to the Barcode text field and the Form that contains it
			var barcode = $('#barcode');
			var barcodeForm = $('#barcodeForm');

			// Create a new function for the KeyDown event on the Barcode field
			barcode.keydown(function(e) {

				// If the keycode of the key pressed is 13 (enter)
				if(e.which == 13) {

					// Make a new AJAX form post
					$.post(
						barcodeForm.action, // Use the action from HTML form
						barcodeForm.serialize(), // Use the data from the HTML form
						function(data){ // When the form submission is complete, run this function
							
							// Add the new barcode entry to the list of barcodes
							$('ul').prepend('<li>' + data + '</li>');

							// Empty out the Barcode textbox to be ready for the next submission
							barcode.val('');

						});

					// Return false to prevent the browser from processing the enter keypress
					return false;
				}
			});
		});
	</script>
</head>
<body>
	<form method="post" action="#" id="barcodeForm">
		<label for="barcode">Barcode:</label>
		<input type="text" name="barcode" id="barcode"/>
		<input type="submit" value="Send"/>
	</form>
	<ul>
		<?php
			// Get ALL barcodes from the database, ordered from last to first
			$stmt = $db->query('SELECT Barcode FROM Barcodes ORDER BY ID DESC');
			
			// Fetch all the returned rows into an array
			$rows = $stmt->fetchAll();
	
			// Loop over the array of rows
			foreach($rows as $row) {

				// Convert the barcode from plain text to HTML code, to prevent HTML injection
				$barcode = htmlentities($row['Barcode']);
				
				// Output each barcode as a list item
				echo "<li>$barcode</li>";

			}
		?>
	</ul>
</body>
</html>
