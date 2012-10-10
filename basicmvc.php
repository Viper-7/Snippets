<?php
class Config {
	public static $dbhost;
	public static $dbuser;
	public static $dbpass;
	public static $dbname;
}

class DB {
	private $db;
	
	// Singleton Methods
	private static $instance;
	
	private function __construct() { } // Don't allow new DB() from outside
	
	public static function getInstance() {
		if(!isset(self::$instance)) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}
	// End Singleton Methods
	
	public function connect() {
		if(!isset($this->db)) {
			$dsn = 'mysql:dbname=' . Config::$dbname . ';host=' . Config::$dbhost;

			try {
				$this->db = new PDO($dsn, Config::$dbuser, Config::$dbpass);
			} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
			}
		}
	}
	
	public function query($sql, $args=NULL) {
		$this->connect();
		
		$stmt = $this->db->prepare($sql);
		if(!empty($args)) {
			$stmt->execute($args);
		} else {
			$stmt->execute();
		}
		$results = $stmt->fetchAll();
		
		return $results;
	}
}

class User {
	public $ID, $Name, $Email, $Access;
	
	const ACCESS_READ=1;
	const ACCESS_WRITE=2;
	const ACCESS_ADMIN=4;
	
	public function isAdmin() {
		$admin = $this->Access & User::ACCESS_ADMIN == User::ACCESS_ADMIN;
		return $admin;
	}
}

class UserFactory {
	// Singleton Methods
	private static $instance;
	
	private function __construct() { } // Dont allow new UserFactory() from outside
	
	public static function getInstance() {
		if(!isset(self::$instance)) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}
	// End Singleton Methods
	
	public function getUserByID($id) {
		$db = DB::getInstance();
		
		$result = $db->query("SELECT ID, Name, Email, Access FROM User WHERE ID = ?", array($id));
		
		if(empty($result) || count($result) != 1) return FALSE;	// Idiot Check - We should only have 1 user for that ID
		
		$user = new User();
		$result = $result[0];
		
		foreach($result as $key => $value) {
			$user->$key = $value;
		}
		
		return $user;
	}
}



Config::$dbuser = 'db';
Config::$dbpass = 'db';
Config::$dbhost = 'localhost';
Config::$dbname = 'test';


// now pretend this is in a controller
$userfactory = UserFactory::getInstance();
$user = $userfactory->getUserByID(1);

if($user->isAdmin()) {
	echo "Woot! you're an admin!";
} else {
	echo "You're a pleb";
}