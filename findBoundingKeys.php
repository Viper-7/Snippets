<pre><?php
	$value = 125;
	$search = array(
		49 => 50,
		52 => 100,
		50 => 150,
		51 => 200,
	);

	$matches = findBoundingKeys($value, $search);
	var_dump($matches);



	function findBoundingKeys($needle, $haystack) {
		// Make sure we have an array
		if( !is_array($haystack) )
		{
			throw new Exception('findBoundingKeys expects $haystack to be an Array, got ' . get_type($haystack));
		}

		// Make sure we have an array of numbers we can compare
		if( max(array_map('is_numeric', $haystack)) )
		{
			throw new Exception('findBoundingKeys expects $haystack to be an Array of numeric values');
		}

		// Sort the array in order from lowest value to highest
		asort($haystack);

		// Rewind the array so we start from the start
		reset($haystack);

		// If the needle is lower than the smallest element in $haystack, return the smallest element
		if( $needle < min($haystack) )
		{
			return array(key($haystack));
		}

		// If the needle is higher than the largest value in $haystack, return the last element
		if( $needle > max($haystack) ) {
			end($haystack);
			return array(key($haystack));
		}

		do
		{
			// Get the key/value for the current array index
			$current = current($haystack);
			$currentkey = key($haystack);

			// Advance to the next index and get the key/value for that index
			$next = next($haystack);
			$nextkey = key($haystack);

			// Check if the needle is between the current and next elements
			if( $current < $needle && $needle < $next )
			{
				// If it is, return the current & next elements
				return array(
					$currentkey,
					$nextkey
				);
			}
		} while ( key($haystack) !== FALSE );

		// Something went utterly wrong, return FALSE
		return FALSE;
	}
?>
