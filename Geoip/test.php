<?php
	// Include the MaxMind GeoIP Libraries
	include 'geoip_timezone.php';

	// Get the real client IP from the webserver's reverse proxy if supplied
	$ip = isset($_ENV["HTTP_X_REAL_IP"]) ? $_ENV["HTTP_X_REAL_IP"] : $_SERVER['REMOTE_ADDR'];

	// Get the GeoIP information for the client's IP
	$geoip = geoip_get_details_by_addr($ip);
	
	// Translate the GeoIP information into a PHP Timezone
	$timezone = geoip_get_timezone($geoip);
	
	// Set the detected timezone as PHP's default
	date_default_timezone_set($timezone);

	// Output the detected timezone and the current date/time formatted for that timezone
	echo 'Your local timezone has been detected as: ' . $timezone . '<br/>';
	echo 'Your local time is ' . date("F j, Y, g:i a") . '<br/>';
	echo '<br/>';


	if($geoip)
	{
		// Extract the latitude/longitude from the GeoIP results
		$latlong = $geoip->latitude . ',' . $geoip->longitude;

		// Output the detected locational information
		echo 'Your latitude/longitude is estimated at: ' . $latlong . '<br/>';
		echo '<br/>';
		
		// Output a sample Google map pointed at the detected location
		?>
		<iframe width="425" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;sll=<?php echo $latlong; ?>&amp;ie=UTF8&amp;z=9&amp;output=embed"></iframe>
		<br />
		<small>
			<a href="http://maps.google.com/maps?f=q&amp;source=embed&amp;hl=en&amp;geocode=&amp;sll=<?php echo $latlong; ?>&amp;ie=UTF8&amp;z=11" style="color:#0000FF;text-align:left">
				View Larger Map
			</a>
		</small>
		<?php
	} else {
		echo 'Your location could not be detected using GeoIP';
	}
