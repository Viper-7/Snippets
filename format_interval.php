<?php
/**
* Simple PHP5 class to format time differences into human readable output
* ie: "5 Years, 2 Months, 9 Days, 3 Hours, 10 Minutes, 5 seconds"
*
* The divisions can be easily modified to make the output as descriptive
* or brief as you like.
*
* @author Viper-7
* @date 2009-10-18
* @package V7IRCFramework
* 
* <code>
*   $timespan = new TimeSpan();
*   echo $timespan->format_span('2009-10-05 5:00:00 GMT', '2009-10-09 13:50:01 GMT', 2);
* </code>
*
* ----------------------------------------------------------------------------
* "THE BEER-WARE LICENSE" (Revision 42):
* <viper7@viper-7.com> wrote this file. As long as you retain this notice you
* can do whatever you want with this stuff. If we meet some day, and you think
* this stuff is worth it, you can buy me a beer in return.   Dale Horton
* ----------------------------------------------------------------------------
*/
class TimeSpan {
	/**
	* Formats two string dates into an string that describes the interval between them
	*
	* @param string Date/Time to start from
	* @param string Date/Time to end at
	* @param int Number of divisors to return, ie (3) gives '1 Year, 3 Days, 9 Hours' whereas (2) gives '1 Year, 3 Days'
	* @param string Seperator to use between divisors
	* @param array Set of Name => Seconds pairs to use as divisors, ie array('Year' => 31536000)
	* @return string Formatted interval between the supplied dates
	*/
	public function format_span($fromDate, $toDate, $precision = -1, $separator = ', ', $divisors = NULL) {
		
		// Determine the difference between the largest and smallest date
		$dates = array(strtotime($fromDate), strtotime($toDate));
		$difference = max($dates) - min($dates);
		
		// Return the formatted interval
		return $this->format_interval($difference, $precision, $separator, $divisors);
		
	}
 
	/**
	* Formats any number of seconds into a readable string
	*
	* @param int Seconds to format
	* @param string Seperator to use between divisors
	* @param int Number of divisors to return, ie (3) gives '1 Year, 3 Days, 9 Hours' whereas (2) gives '1 Year, 3 Days'
	* @param array Set of Name => Seconds pairs to use as divisors, ie array('Year' => 31536000)
	* @return string Formatted interval
	*/
	public function format_interval($seconds, $precision = -1, $separator = ', ', $divisors = NULL)
	{
		
		// Default set of divisors to use
		if(!isset($divisors)) {
			$divisors = Array(
				'Year'		=> 31536000, 
				'Month'		=> 2628000, 
				'Day'		=> 86400, 
				'Hour'		=> 3600, 
				'Minute'	=> 60, 
				'Second'	=> 1);
		}
		
		arsort($divisors);
		
		// Iterate over each divisor
		foreach($divisors as $name => $divisor)
		{
			// If there is at least 1 of thie divisor's time period
			if($value = floor($seconds / $divisor)) {
				// Add the formatted value - divisor pair to the output array.
				// Omits the plural for a singular value.
				if($value == 1)
					$out[] = "$value $name";
				else
					$out[] = "$value {$name}s";
				
				// Stop looping if we've hit the precision limit
				if(--$precision == 0)
					break;
			}
			
			// Strip this divisor from the total seconds
			$seconds %= $divisor;
		}
		
		// Join the value - divisor pairs with $separator between each element
		return implode($separator, $out);
		
	}
}
