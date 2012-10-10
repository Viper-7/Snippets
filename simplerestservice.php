<?php
/**
*	Example 1 - A basic REST webservice that responds only to GET requests
*
*	GET http://example.com/time - Returns the current time
*	GET http://example.com/date - Returns the current date
*
**/
class MyBasicService extends SimpleRESTService {
	public static $allowed_methods = array( 'GET' );
	public static $default_action = 'date';
	public static $default_content_type = 'text/plain';
	
	public function time() {
		return date('h:i:sa');
	}
	
	public function date() {
		return date('Y-m-d');
	}
}

$service = new MyBasicService();
$service->handleRequest();



/**
*	Example 2 - A more complex REST webservice with a model controller
*
*	GET /Help		- Displays Help
*	GET /Users		- Returns a list of users
*	GET /Users/5	- Returns details about user 5
*
*	PUT /Users/5	- Updates details about user 5
*
*	DELETE /Users/5	- Deletes user 5
*
**/
class MyComplexService extends SimpleRESTService {
	public function Users() {
		return new UserController($this);
	}
	
	public function Help() {
		return "This would give you help information.... maybe :P";
	}
}

class UserController extends SimpleRESTService {
	public static $allowed_methods = array( 'GET', 'PUT', 'DELETE' );
	
	public function index() {
		$method = strtolower($this->request_method);
		$args = func_get_args();
		
		return call_user_func_array(array($this, $method), $args);
	}
	
	public function get($id = null) {
		if($id)
			return "This would return the details about user {$id}";
		
		return "This would return the entire list of users";
	}
	
	public function put($id = null) {
		if($id)
			return "This would save the following data into user {$id}'s record: \r\n{$this->request_body}";
		
		trigger_error('PUT requests are not supported without an ID');
	}
	
	public function delete($id = null) {
		if($id)
			return "This would delete user {$id}";
		
		trigger_error('DELETE requests are not supported without an ID');
	}
}

$service = new MyComplexService();
$service->handleRequest();


/**
*	The SimpleRESTService class.
*	Extend this class with your own code to provide REST webservices with a minimum of fuss.
*
*	This class handles:
*	  * Decoding the URL into seperate parts
*	  * Reading the query string and/or request body
*	  * Checking wether the request method is allowed
*	  * Calling controller methods based on URL parts
*	  * Sending the response back to the client, including the Content-Type header
*
**/
class SimpleRESTService {
	
	public static $allowed_methods = array( 'GET', 'POST', 'PUT', 'DELETE' );	// Array of allowed REQUEST_METHOD values for this controller
	public static $default_action = 'index';									// Default controller method to call if none was found in the URL
	public static $default_content_type = 'application/json';					// Default Content-Type to use for responses
	
	public $request_uri;	// Request URL sent from the client. For child controllers will contain only the parts used by this child.
	public $request_method;	// Request Method used by the client
	public $request_body;	// Request Body sent from the client (for PUT & POST requests)
	public $content_type;	// Content-Type to be used when responding to this request. Change this value
	
	
	public function __construct($parent_controller = null) {
		if(!isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['REQUEST_URI']))
			trigger_error('Required server variables missing, check webserver configuration.');
		
		if($parent_controller) {
			$class = get_class($parent_controller);
			if(!$parent_controller instanceof SimpleRESTService) {
				trigger_error("Child controller {$class} must extend SimpleRESTService", E_USER_FATAL);
			}
			
			foreach(array('request_method', 'request_body') as $key) {
				$this->$key = $parent_controller->$key;
			}
		} else {
			$this->request_uri = trim($_SERVER['REQUEST_URI'], '/');
			list($this->request_method, $query_string) = explode('?', $_SERVER['REQUEST_METHOD'], 2) + array('', '');
			
			if($this->request_method != 'GET')
				$this->request_body = file_get_contents('php://input');
		}
	}
	
	public function contentType() {
		if($this->content_type)
			return $this->content_type;
		
		return static::$default_content_type;
	}
	
	public function handleRequest() {
		$parts = explode('/', $this->request_uri);
		
		if(empty($parts[0])) {
			$parts[0] = static::$default_action;
		}
		
		if(!in_array($this->request_method, static::$allowed_methods)) {
			trigger_error("Method {$this->request_method} not allowed for {$this->request_uri}", E_USER_FATAL);
		}
		
		$slug = array_shift($parts);
	
		if(method_exists($this, $slug)) {
			$result = call_user_func_array(array($this, $slug), $parts);
			
			if(is_object($result)) {
				$class = get_class($result);
				if(!$result instanceof SimpleRESTService) {
					trigger_error("Child controller {$class} must extend SimpleRESTService", E_USER_FATAL);
				}
				
				$result->request_uri = implode('/', $parts);
				$result->handleRequest();
			} elseif(is_string($result)) {
				header('Content-Type: ' . $this->contentType());
				echo $result;
				return strlen($result);
			}
		} else {
			$class = get_class($this);
			trigger_error("No method to handle '{$slug}' in controller {$class}", E_USER_FATAL);
		}
	}
}
