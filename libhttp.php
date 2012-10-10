<?php
define("WEBBOT_NAME", "Googlebot/2.1 (http://www.googlebot.com/bot.html)");


define("CURL_TIMEOUT", 200);


define("COOKIE_FILE", "C:/wamp/www/tor/cookie.txt");
define("HEAD", "HEAD");
define("GET",  "GET");
define("POST", "POST");


define("EXCL_HEAD", FALSE);
define("INCL_HEAD", TRUE);


function http_get($target, $ref)
    {
    return http($target, $ref, $method="GET", $data_array="", EXCL_HEAD);
    }


function http_get_withheader($target, $ref)
    {
    return http($target, $ref, $method="GET", $data_array="", INCL_HEAD);
    }


function http_get_form($target, $ref, $data_array)
    {
    return http($target, $ref, $method="GET", $data_array, EXCL_HEAD);
    }


function http_get_form_withheader($target, $ref, $data_array)
    {
    return http($target, $ref, $method="GET", $data_array, INCL_HEAD);
    }


function http_post_form($target, $ref, $data_array)
    {
    return http($target, $ref, $method="POST", $data_array, EXCL_HEAD);
    }

function http_post_withheader($target, $ref, $data_array)
    {
    return http($target, $ref, $method="POST", $data_array, INCL_HEAD);
    }

function http_header($target, $ref)
    {
    return http($target, $ref, $method="HEAD", $data_array="", INCL_HEAD);
    }


function http($target, $ref, $method, $data_array, $incl_head)
    {
    # Initialize PHP/CURL handle
    $ch = curl_init();
        
    # HEAD method configuration
    if($method == HEAD)
        {
        curl_setopt($ch, CURLOPT_HEADER, TRUE);                // No http head
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);                // Return body
        }
    else
        {
        curl_setopt($ch, CURLOPT_HEADER, $incl_head);   // Include head as needed
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);        // Return body
        # GET method configuration
        if($method == GET)
            {
            # Convert data array into a query string (ie animal=dog&sport=baseball)
            if(isarray($data_array))
                {
                foreach ($data_array as $key => $value)
                    {
                    if(strlen(trim($value))>0)
                        $temp_string[] = $key . "=" . urlencode($value);
                    else
                        $temp_string[] = $key;
                    }
                    $query_string = join('&', $temp_string);
                } else {
                    $query_string = (string)$data_array;
                }
            if(isset($query_string))
                $target = $target . "?" . $query_string;
                curl_setopt ($ch, CURLOPT_HTTPGET, TRUE);
            }
        # POST method configuration
        if($method == POST)
            {
            curl_setopt ($ch, CURLOPT_POST, TRUE);
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $data_array);
            }
        }
        
    curl_setopt($ch, CURLOPT_COOKIEJAR, COOKIE_FILE);   // Cookie management.
    curl_setopt($ch, CURLOPT_COOKIEFILE, COOKIE_FILE);
    //curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1:8118"); // ENABLE TOR
    curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT);    // Timeout
    curl_setopt($ch, CURLOPT_USERAGENT, WEBBOT_NAME);   // Webbot name
    curl_setopt($ch, CURLOPT_URL, $target);             // Target site
    curl_setopt($ch, CURLOPT_REFERER, $ref);            // Referer value
    curl_setopt($ch, CURLOPT_VERBOSE, FALSE);           // Minimize logs
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // No certificate
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);     // Follow redirects
    curl_setopt($ch, CURLOPT_MAXREDIRS, 4);             // Limit redirections to four
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);     // Return in string
  
    # Create return array
    $return_array['FILE']   = curl_exec($ch);
    $return_array['STATUS'] = curl_getinfo($ch);
    $return_array['ERROR']  = curl_error($ch);
    
    # Close PHP/CURL handle
      curl_close($ch);
    
    # Return results
      return $return_array;
    }
    

    	$target = 'http://www.viper-7.com/test2.php';
    	$ref = $PHP_SELF;
	$data_array['testvalue'] = 'hello';
	$contents = http_post_form($target, $ref, $data_array);
	echo($contents['FILE']);  
?>