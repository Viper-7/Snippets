<?php include 'google.php'; ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head><title>Montage</title></head>
	<?php if(!isset($_GET['q'])) { ?>
		<body>
			<div style="width: 100%; text-align: center">
				<form method="GET" action="">
					<br/><br/><br/>
					<h2>Montage</h2><br/>
					<table border=0 cellspacing="5" align="center">
						<tr>
							<td>Enter Search Phrase:</td><td><input type="text" name="q"/></td>
						</tr>
						<tr>
							<td>Search Type:</td>
							<td>
								<select name="type">
									<option value="video">Video</option>
									<option value="image">Image</option>
								</select>
							</td>
						</tr>
					</table><br/>
					<input type="submit" value="Go">
				</form>
			</div>
		</body>
	<?php } else { ?>
		<body border="0" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0" bgcolor="#000000" link="#000000" alink="#000000" vlink="#000000">
			<div style="text-align: center; width:100%; height:100%; overflow: hidden">
				<div style="width: 1260px; height: 100%">
				<?php
					if(!isset($_GET['start'])) $_GET['start'] = 0;
					if(!isset($_GET['type'])) $_GET['type'] = 'video';
					
					$gs = new GoogleSearch();
					$searchtype = $_GET['type'] == 'video' ? GoogleSearch::VIDEO : GoogleSearch::IMAGE;
					
					$result = $gs->search($_GET['q'], $searchtype, 54, $_GET['start']);
					$i = 0;
					foreach($result[$_GET['type']] as $image)
					{
						$i++;
						if(isset($_GET['type']) && $_GET['type'] == 'image') {
							echo '<a href="' . $image->originalContextUrl . '" target="_blank">';
							echo '<img src="showthumb.php?image=' . rawurlencode($image->tbUrl) . '" border="0" title="[' . $image->titleNoFormatting . "] - " . $image->content . '"/>';
						} else {
							echo '<a href="' . $image->playUrl . '" target="_blank">';
							echo '<img src="showthumb.php?image=' . rawurlencode($image->tbUrl) . '" border="0" title="[' . $image->titleNoFormatting . "] - " . $image->content . " [Runtime: " . $image->duration . ' seconds]"/>';
						}
						echo '</a>';
						if($i % 9 == 0) { echo '<br/>'; }
					}
				?>
				</div>
			</div>
		</body>
	<?php } ?>
</html>