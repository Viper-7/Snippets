<?php
/*
    HTTPRequest
    
    A simple wrapper function using php streams, to handle 90% of all GET/POST request needs.
    
    Arguments:
        $url            string      URL to request (Required)
        $method         string      HTTP method for request - 'GET' or 'POST'
        $params         mixed       Array of GET/POST variables to send, or a string of url encoded get/post data to send
        $cookie         mixed       Array of Cookie variables to send, or a string of url encoded cookie data to send
        $user_agent     string      User-Agent to send (uses php.ini default if not supplied)
        $proxy          string      Proxy server to tunnel through, user:pass@server:port 
        $headers        string      Any additional HTTP headers to send

        
    Return values:
        HTTPRequest returns an array on success,
        
        The result array always contains the following:
            content         String containing the entire response body
            uri             Final URL of the request
            headers         Array of name => value pairs for all returned headers
            http            HTTP Version
            response_msg    HTTP Response message
            response_code   HTTP Response code
        
        The result array may also contain these additional elements:
            cookie          Array of name => value pairs for any cookies set by the server
    
    
    Usage:
    
        Example GET request:
        <code>
            $result = HTTPRequest('http://www.viper-7.com/test.php');
            echo $result['content'];
        </code>
        
        
        Example POST request:
        <code>
            $result = HTTPRequest(
                $url = 'http://www.viper-7.com/test.php',
                $method = 'POST',
                $params = array('action' => 'getTime')
            );
            echo $result['content'];
        </code>
        
        
        Example POST image upload:
        <code>
            $result = HTTPRequest(
                $url = 'http://www.viper-7.com/test.php',
                $method = 'POST',
                $params = array('title' => 'My cool photo'),
                $files = array('photo' => 'uploads/myphoto.jpg')
            );
            echo $result['content'];
        </code>
        
        
        Example POST login and POST request with shared cookie:
        <code>
            $username = 'foo';
            $password = 'bar';
            $id = 5;
            
            $login_request = HTTPRequest(
                $url = 'http://www.viper-7.com/login.php',
                $method = 'POST',
                $params = array(
                    'user' => $username,
                    'pass' => $password
                )
            );
            
            if(isset($login_request['cookie']))
            {
                $data = HTTPRequest(
                    $url = 'http://www.viper-7.com/data.php',
                    $method = 'POST',
                    $params = array(
                        'action' => 'getReport',
                        'id' => $id
    				),
					$cookie = $login_request['cookie'],
    				$user_agent = ''
                )
            }
        </code>
    
    
    Example response array:
        [uri] => http://www.viper-7.com/test.php
        [response_msg] => OK
        [response_code] => 200
        [http] => HTTP/1.1
        [headers] => Array
            (
                [Server] => nginx/0.6.36
                [Date] => Mon, 23 Aug 2010 09:42:46 GMT
                [Content-Type] => text/html
                [Connection] => close
                [X-Powered-By] => PHP/5.3.0
            )
    
        [content] => Hello, world!
    
*/
/** 
 * Make a HTTP GET or POST Request to the specified URL.
 * 
 * @param $url string URL to request
 * @param $method string HTTP method for request - 'GET' or 'POST'
 * @param $params Array GET/POST variables to send, or a string of url encoded get/post data to send.
 * @param $files Array Absolute paths of files to send
 * @param $cookie Array Cookie variables to send, or a string of url encoded cookie data to send.
 * @param $user_agent string User-Agent to send (uses php.ini default if not supplied)
 * @param $proxy string HTTP Proxy server to use. user:pass@server:port
 * @param $headers Array/string Additional HTTP headers to send
 * 
 * @return Array
*/
    function HTTPRequest($url, $method = 'GET', $params = array(), $files = array(), $cookie = '', $user_agent = '', $proxy = '', $headers = '')
    {
        $options = array();
        $options['http']['header'] = array();
        
        if(is_array($params))
            $data = http_build_query($params);
        else
            $data = $params;
        
        if($files)
        {
            $boundary = "---------------------".substr(uniqid(), 0, 10);
            $data = "--$boundary\r\n";
            
            foreach($params as $key => $val)
            {
                $data .= "Content-Disposition: form-data; name=\"".$key."\"\r\n\r\n".$val."\r\n";
                $data .= "--$boundary\r\n";
            }
            
            foreach($files as $key => $file)
            {
                $size = @getimagesize($file);
                if(isset($size['mime']))
                    $mime = $size['mime'];
                else
                    $mime = 'application/octet-stream';
                
                $data .= "Content-Disposition: form-data; name=\"{$key}\"; filename=\"{$file}\"\r\n";
                $data .= "Content-Type: {$mime}\r\n";
                $data .= "Content-Transfer-Encoding: binary\r\n\r\n";
                $data .= file_get_contents($file)."\r\n";
                $data .= "--$boundary\r\n";
            } 
        }
    
        if($method == 'POST')
        {
            $options['http']['method'] = 'POST';
            
            if($files)
            {
                $options['http']['header'][] = "Content-Type: multipart/form-data; boundary={$boundary}\r\n";
            } else {
                $options['http']['header'][] = "Content-Type: application/x-www-form-urlencoded\r\n";
            }
            
            if($data)
                $options['http']['content'] = $data;
            
        } elseif($method == 'GET') {
            if($params)
                $url .= '?' . $data;
        }

        if($proxy)
            $options['http']['proxy'] = $proxy;
        
        if($user_agent)
            $options['http']['user_agent'] = $user_agent;
        
        if($headers)
		{
			if( is_array($headers) )
			{
				$options['http']['header'] = array_merge($options['http']['header'], $headers);
			} else {
				$options['http']['header'][] = $headers;
			}
		}
        
        if($cookie)
        {
            if(!is_scalar($cookie))
                $cookie = http_build_query($cookie);
            
            $options['http']['header'][] = "Cookie: {$cookie}\r\n";
        }
        
        $options['http']['header'] = implode('',$options['http']['header']);
        $options['http']['ignore_errors'] = 1;
        
        $context = stream_context_create($options);
        $fp = @fopen($url, 'r', false, $context);
        $result = array();
        
        if($fp)
        {
            $meta = stream_get_meta_data($fp);
            
            $headers = array_slice($meta['wrapper_data'], 1);
            $result['uri'] = $meta['uri'];
            list($result['http'],$result['response_code'],$result['response_msg']) = explode(' ', $meta['wrapper_data'][0]);

            foreach($headers as $header)
            {
                $tok = strpos($header, ':');
                $name = trim(substr($header, 0, $tok));
                $value = trim(substr($header,$tok+1));
                
                if($name == 'Set-Cookie')
                {
                    parse_str($value, $result['cookie']);
                } else {
                    $result['headers'][$name] = $value;
                }                        
            }

            $result['content'] = stream_get_contents($fp);

            fclose($fp);
            return $result;
        }
        
        return false;
    }
    
?>
