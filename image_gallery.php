<?php
header('Content-Type: application/xhtml+xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head><title>Images</title>
<style type="text/css">
	img { border: 0px; }
	p.images { text-align: center; min-width: 800px; margin: 0 auto; }
</style>
</head>
<body>
	<p class="images">
		<?php
		// Specify the maximum height & width of the thumbnails
		$thumbwidth = 150;
		$thumbheight = 200;
		
		// Specify how many images to display per line
		$imagesperline = 5;
		
		// Setup the path to find images in (trailing / required)
		$imagepath = 'funny/';

		// Setup the base path to store thumbnails in (trailing / required)
		$thumbpath = 'funnythumb/';
		
		// Fetch all images in the images/ subfolder
		$images = glob($imagepath . '*');
		
		// Ignore files with these names
		$ignore = array('Thumbs.db','.','..');
		
		$i=0;
		foreach($images as $image) {
			// If the filename is in our ignore list, move to the next one
			if(in_array(basename($image),$ignore)) continue;
			
			// Build the thumbnail filename from the thumnail path & image name
			$thumb = $thumbpath . basename($image);
			
			// If we dont already have a thumbnail for this image, we need to generate one
			if(!file_exists($thumb)) {
				// Get the image dimensions for resizing
				$size = @getimagesize($image);
				
				// If the file was unreadable or corrupted, skip it and move to the next one
				if(empty($size)) continue;
				
				list($oldwidth, $oldheight) = $size;
				$newwidth = $thumbwidth;

				// Generate the height of the thumbnail based on the selected 
				// width & image aspect ratio
				if($oldheight > 1 && $oldwidth > 1) {
					$newheight = ($oldheight / $oldwidth) * $newwidth;
				}
				
				// If the generated height is larger than specified, limit to
				// the specified height and generate the width instead
				if($newheight > $thumbheight) {
					$newheight = $thumbheight;
					$newwidth = ($oldwidth / $oldheight) * $newheight;
				}

				// Allocate the output image buffer
				$image_o = imagecreatetruecolor($newwidth, $newheight);
				
				// Load the image from disk, using imagecreatefromstring to auto-detect the filetype
				$image_i = @file_get_contents($image);
				$image_i = @imagecreatefromstring($image_i);
				
				if(is_resource($image_i)) {
					// Resize the image to the generated size
					imagecopyresampled($image_o, $image_i, 0, 0, 0, 0, $newwidth, $newheight, $oldwidth, $oldheight);
					
					// Write it to the thumbs folder
					imagejpeg($image_o, $thumb, 40);
				} else {
					// Failed to load image, skip to the next one
					continue;
				}
			}
			
			// Display each thumbnail and link it to the real image
			echo '<a href="' . $imagepath . basename($image) . '"><img src="' . $thumbpath . basename($image) . '" alt="' . basename($image) . '"/></a> &nbsp;';
			
			// Wrap lines after a certain number of images
			$i++;
			if($i % $imagesperline == 0) echo "<br/>\n";
		}
		?>
		<br/>
	</p>
	<p style="text-align: center;">
		<a href="http://validator.w3.org/check?uri=referer">
			<img src="http://www.w3.org/Icons/valid-xhtml10-blue" alt="Valid XHTML 1.0 Strict" height="31" width="88" />
		</a>
	</p>
</body>
</html>
