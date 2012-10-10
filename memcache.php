<?php
class DB {
	private $dblink;
	private $memcache;
	private $expire = 300;
	
	function __construct($host, $user, $pass, $db, $memcachehost = NULL) {
		if(!isset($memcachehost)) $memcachehost = $host;

		$this->link = mysql_connect($host, $user, $pass);
		mysql_select_db($db, $this->link);

		$this->memcache = new Memcache;
		$this->memcache->connect($memcachehost, 11211);
	}

	function query($sql) {
		if(strtolower(substr($sql,0,6)) == 'select') {
			$out = $this->memcache->get(md5($sql))
			if($out !== FALSE) return $out;
		}

		$result = mysql_query($sql, $this->dblink);

		if(is_resource($result) && mysql_num_rows($result) > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$out[] = $row;
			}
			
			$this->memcache->set(md5($sql), $out, 0, $this->expire);
			return $out;
		}
		
		return FALSE;
	}
}

$db1 = new DB('localhost','root','','mydatabase');
$results = $db1->query("SELECT * FROM foo");

echo '<pre>';
foreach($results as $row) {
	print_r($row);
}
echo '</pre>';

?>