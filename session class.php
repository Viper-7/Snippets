<?php
class Session
{
	private static $save_path;
	private static $prefix = 'sess';
	
	private static function get_session_file($id)
	{
		return self::$save_path . '/' . self::$prefix . '_$id';
	}
	
	public static function open($path, $name)
	{
		self::$save_path = $path;
		
		return true;
	}

	public static function close()
	{
		return true;
	}
	
	public static function read($id)
	{
		$session_file = self::get_session_file($id);
		
		if(file_exists($session_file))
		{
			return file_get_contents($session_file);
		}
	}
	
	public static function write($id, $data)
	{
		$session_file = self::get_session_file($id);
		
		return file_put_contents($session_file, $data);
	}
	
	public static function destroy($id)
	{
		return unlink(self::get_session_file($id));
	}
	
	public static function gc($maxlifetime)
	{
		$file_list = glob(self::get_session_file('*'));
		foreach($file_list as $file)
		{
			if(filemtime($file) + $maxlifetime < time())
			{
				unlink($file);
			}
		}
		
		return true;
	}
}

ini_set('session.save_path', '/tmp');

session_set_save_handler(
  array("Session", "open"),
  array("Session", "close"),
  array("Session", "read"),
  array("Session", "write"),
  array("Session", "destroy"),
  array("Session", "gc")
); 

session_start();


echo $_SESSION['hi'] . '<br/>';
$_SESSION['hi'] = 'hello';
echo $_SESSION['hi'] . '<br/>';

?>
