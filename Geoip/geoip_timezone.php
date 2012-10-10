<?php
	function geoip_get_details_by_addr($addr)
	{
		require_once 'geoipcity.php';
		
		$geoip = geoip_open('./GeoLiteCity.dat', GEOIP_STANDARD);
		$details = GeoIP_record_by_addr($geoip, $addr);
		geoip_close($geoip);
		
		return $details;
	}

	function geoip_get_details_by_host($host)
	{
		require_once 'geoipcity.php';
		
		$geoip = geoip_open('./GeoLiteCity.dat', GEOIP_STANDARD);
		$details = GeoIP_record_by_addr($geoip, gethostbyname($host));
		geoip_close($geoip);
		
		return $details;
	}

	function geoip_get_timezone($details)
	{
		if($details)
		{
			$timezone = get_time_zone($details->country_code, $details->region);
			if(!$timezone)
			{
				$timezone = $details->country_name . '/' . $details->city;
			}
			
			return $timezone;
		} else {
			return 'UTC';
		}
	}

	function geoip_get_timezone_by_addr($addr)
	{
		$details = geoip_get_details_by_addr($addr);
		
		return geoip_get_timezone($details);
	}
		
	function geoip_get_timezone_by_host($host)
	{
		return geoip_get_timezone_by_addr(gethostbyname($host));
	}