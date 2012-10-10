<?php
     error_reporting(E_ALL);
     
     class Keywords {
	// Declare $ch as static, so it will be shared with all future getKeywords calls
	private static $ch = null;
        
        // Declare getKeywords as a static function, so we dont need to instantiate the class before calling the function
        public static function getKeywords($searchterm){
	  $hits = NULL;

	  // if the static variable $ch is not initialized yet, do it now
	  if(!isset(self::$ch)) self::$ch = curl_init();
	  
	  // Set our curl options for this request
          curl_setopt(self::$ch, CURLOPT_URL, 'http://clients1.google.com/complete/search?hl=en&gl=us&q=' . urlencode(trim($searchterm)));
          curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, TRUE);
          
          // Run the curl request
          $fc = curl_exec(self::$ch);
          
          // Filter out the Google Javascript API wrapping
          if(preg_match('/window.google.ac.h\((.+?)\)/', $fc, $results)) {
	          // Decode the array as JSON
	          $content = json_decode($results[1]);
		  
		  // Loop through each search result
	          foreach($content[1] as $result) {
			// Strip the commas and " results" from the number of hits returned
	          	$value = str_replace(array(',',' results'),'',$result[1]);
	          	
	          	// Create a key in $hits named by the search result, with the number of hits as the value
	          	$hits[$result[0]] = $value;
	          }
	          
	          // Confirm we actually found hits
	          if(is_array($hits)) {
	                // Sort the array by the number of hits, as a number, in reverse order
	          	arsort($hits,SORT_NUMERIC);
	                return $hits;
	          } else {
	          	return false;
	          }
	  } else {
	          return false;
	  }
        }
      }
     
     // Call the static function getKeywords inside the Keywords class, and pass it the GET or POST var 'q'
     $result = Keywords::getKeywords($_REQUEST['q']);
     
     // Display the results
     echo '<pre>' . print_r($result,true) . '</pre>';
?>