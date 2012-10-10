<?php
error_reporting(-1);
$http = new VHTTP(VHTTP::STREAM);
$result = $http->get(
	'http://www.nimbler.com.au/phpinfo.php',
	array('foo' => 'bar'),
	array('myfile' => dirname(__FILE__) . '/images/avatar.gif')
);
echo $result['content'];

/**
 * HTTP Communication class, supports pluggable drivers, easy cookie session
 * persistance, file transfers, etc.
 *  
 * Simple GET request usage:
 *  <code>
 *  	$http = new VHTTP();
 *  	$result = $http->get('http://www.example.com');
 *  	echo $result['content'];
 *  </code>
 * 
 * Simple POST request usage:
 *  <code>
 *  	$http = new VHTTP();
 *  	$result = $http->post('http://www.example.com', array('foo' => 'bar'));
 *  	echo $result['content'];
 *  </code>
 *
 * POST file upload example:
 *  <code>
 *  	$http = new VHTTP();
 * 
 *  	$result = $http->post(
 *  		'http://www.example.com',
 *  		array(),
 *  		array('myfile' => 'images/avatar.png'
 *  	);
 * 
 *  	echo $result['content'];
 *  </code>
 * 
 * Shared cookie example:
 *  <code>
 *  	$http = new VHTTP();
 * 
 *  	$result = $http->post(
 *  		'http://www.example.com',
 *  		array('user' => $user, 'pass' => $pass)
 *  	);
 *  	$http->cookie = $result['cookie'];
 * 
 *  	$result = $http->get('http://www.example.com/members_only');
 *  	echo $result['content'];
 *  </code>
 * 
 *  Example return array:
 *  <code>
 * (
 *     [uri] => http://www.example.com.au
 *     [response_msg] => OK
 *     [response_code] => 200
 *     [http] => HTTP/1.1
 *     [request_time] => 0.815013
 *     [headers] => Array
 *         (
 *             [Date] => Sat, 11 Sep 2010 15:22:33 GMT
 *             [Vary] => Accept-Encoding,User-Agent
 *             [Content-Length] => 67980
 *             [Content-Type] => text/html
 *         )
 *     [content] => ...
 * ) 
 * @author Viper-7 (12/09/2010)
 */
class VHTTP
{
	const STREAM = 'VHTTP_Stream';
	const CURL = 'VHTTP_cURL';

	public $user_agent = '';
	public $proxy = '';
	public $cookie = '';
	public $headers = array();
	public $max_length = -1;
	public $timeout = 30;
	public $bindip;

	public $driver = null;

	public function __construct($mode = NULL)
	{
		if(!$mode)
			$mode = self::CURL;

		$class = $mode;

		if( class_exists($class) )
		{
			$this->driver = new $class($this);

			if( !is_a($this->driver, 'VHTTP_Driver') )
			{
				trigger_error("Class {$class} isn't a VHTTP_Driver. Check the VHTTP constructor \$mode argument.");
			}
		} else {
			trigger_error("Class {$class} not found. Check the VHTTP constructor \$mode argument.");
		}
	}

	public function post($url, $params = array(), $files = array(), $headers = array())
	{
		$context = $this->driver->_build_context($url, $params, 'POST', $headers, $files);

		$fp = $this->driver->_request($url, $context);
		
		if($fp)
		{
			return $this->driver->_build_response_array($fp);
		} else {
			return false;
		}
	}

	public function get($url, $params = array(), $headers = '')
	{
		$context = $this->driver->_build_context($url, $params, 'GET', $headers = array());

		$fp = $this->driver->_request($url, $context);

		if($fp)
		{
			return $this->driver->_build_response_array($fp);
		} else {
			return false;
		}
	}

	public function checkURL($url)
	{
		$context = $this->driver->_init_context();

		if( $this->driver->_request($url, $context) )
			return true;
		else
			return false;
	}
}

abstract class VHTTP_Driver
{
	protected $client = null;

	public function __construct($client)
	{
		$this->client = $client;
	}
	
	abstract public function _init_context();
	abstract public function _request($url, $context);
	abstract public function _build_context($url, $params, $method, $headers, $files);
	abstract public function _build_response_array($fp);
}

class VHTTP_Stream extends VHTTP_Driver
{
	protected $starttime;

	public function _init_context()
	{
		$options = array();

		if(!empty($this->client->proxy))
			$options['http']['proxy'] = $this->client->proxy;

		if(!empty($this->client->user_agent))
			$options['http']['user_agent'] = $this->client->user_agent;

		return stream_context_create($options);
	}

	public function _request($url, $context)
	{
		$this->starttime = microtime(true);

		$fp = @fopen($url, 'r', false, $context);

		return $fp;
	}

	public function _build_context($url, $params = '', $method = 'GET', $headers = '', $files = array())
	{
		$options = array();
		$options['http']['header'] = array();
		$options['http']['method'] = $method;
		$options['http']['timeout'] = $this->client->timeout;

		if($this->client->bindip)
			$options['socket']['bindto'] = $this->client->bindip;

		$context = $this->_init_context();

		if(is_array($params))
			$data = http_build_query($params);
		else
			$data = $params;
		
		if(!empty($files))
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
	
		if($headers)
		{
			if( is_array($headers) )
			{
				$options['http']['header'] = array_merge($options['http']['header'], $headers);
			} else {
				$options['http']['header'][] = $headers;
			}

			if( !empty($this->client->headers) )
			{
				$options['http']['header'] = array_merge($options['http']['header'], $this->client->headers);
			}
		}
		
		if(!empty($this->client->cookie))
		{
			if(!is_scalar($this->client->cookie))
				$cookie = http_build_query($this->client->cookie);
			else
				$cookie = $this->client->cookie;

			$options['http']['header'][] = "Cookie: {$cookie}\r\n";
		}
		
		$options['http']['header'] = implode("\r\n", array_map('trim', $options['http']['header']));
		$options['http']['ignore_errors'] = 1;
		
		stream_context_set_params($context, $options);

		return $context;
	}

	public function _build_response_array($fp)
	{
		$result = array();
		$meta = stream_get_meta_data($fp);

		$headers = array_slice($meta['wrapper_data'], 1);
		$result['uri'] = $meta['uri'];
		list($result['http'],$result['response_code'],$result['response_msg']) = explode(' ', $meta['wrapper_data'][0]);

		$result['request_time'] = number_format(microtime(true) - $this->starttime, 6);
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

		$result['content'] = stream_get_contents($fp, $this->client->max_length);

		return $result;
	}
}

class VHTTP_cURL extends VHTTP_Driver
{
	protected $ch;

	public function _init_context() {
		$ch = curl_init();

		$this->ch = $ch;

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);

		return $ch;
	}

	public function _request($url, $ch) {
		curl_setopt($ch, CURLOPT_URL, $url);
		return curl_exec($ch);
	}

	public function _build_context($url, $params=array(), $method='', $headers=array(), $files=array()) {
		$ch = $this->_init_context();

		foreach($files as $key => $file)
		{
			$params[$key] = "@{$file}";
		}

		if(!empty($this->client->cookie))
		{
			if(!is_scalar($this->client->cookie))
				$cookie = http_build_query($this->client->cookie);
			else
				$cookie = $this->client->cookie;
		} else {
			$cooke = false;
		}
		
		$headers = array_merge($headers, $this->client->headers);

		if( $this->client->proxy )
		{
			$proxy = explode('@', $this->client->proxy, 1);

			if( count($proxy) == 1 )
			{
				$proxy = $proxy[0];
				$auth = null;
			} else {
				list($proxy, $auth) = $proxy;
			}
		} else {
			$proxy = false;
			$auth = null;
		}

		$options = array(
			CURLOPT_COOKIE => $cookie,
			CURLOPT_POSTFIELDS => $params,
			CURLOPT_HTTPPROXYTUNNEL => (bool)$proxy,
			CURLOPT_PROXY => $proxy,
			CURLOPT_PROXYUSERPWD => $auth,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_TIMEOUT => $this->client->timeout,
			CURLOPT_CONNECTTIMEOUT => $this->client->timeout,
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_UPLOAD => !empty($files),
			CURLOPT_POST => $method == 'POST'
		);

		if( $this->client->max_length >= 0 )
			$options[CURLOPT_BUFFERSIZE] = $this->client->max_length;

		if( $this->client->bindip )
			$options[CURLOPT_INTERFACE] = $this->client->bindip;

		if( $this->client->user_agent )
			$options[CURLOPT_USERAGENT] = $this->client->user_agent;

		curl_setopt_array($ch, $options);

		return $ch;
	}

	public function _build_response_array($str) {
		$result = array();
		$meta = curl_getinfo($this->ch);

		if($str)
		{
			$content = $str;
			do {
				$arr = explode("\r\n\r\n", $content, 2);
				strtok($arr[0], ' ');
				$status = (int)strtok(' ');

				if($status == 0) 
					break;

				list($headers, $content) = $arr;
			} while($status != 200);

			$result['content'] = $content;
		} else {
			$headers = '';
			$result['content'] = '';
		}

		$result['uri'] = $meta['url'];

		$headers = explode("\r\n", $headers);

		$http = array_shift($headers);
		list($result['http'],$result['response_code'],$result['response_msg']) = explode(' ', $http);
		$result['request_time'] = $meta['total_time'];

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

		return $result;
	}

	public function __destruct()
	{
		curl_close($this->ch);
	}
}
?>
