<?php
/**
 * Automates the process of spawning an background php script from a webserver.
 * Supports Windows NT and most flavours of Linux/Unix. 
 * Passed arguments will appear inside the $argv array in the receiving script.
 *  
 * Usage: 
 * <code>
 * 	$pid = include_background('test2.php', array('arg1', 'arg2')); 
 * </code> 
 *  
 * @note You should define a constant named PHPCLI_PATH before calling this 
 *  	 function, with the full absolute path to your php binary. ie:
 *  		define('PHPCLI_PATH', "C:\php\php.exe"); on Windows
 *  		define('PHPCLI_PATH', "/usr/bin/php"); on Linux
 * 
 * @param $script string The command to execute, with full path.
 * @param $args mixed A string or number, or an array of strings & numbers - 
 *  			does not support complex nesting, use serialize() if you need to
 *  			pass large arrays or objects.
 * 
 * @return int Returns the PID of the spawned process (Linux/Unix only) 
 * 
 * @author Viper-7 (11/09/2010) 
 */ 
function include_background($script, $args = array()) {
	if( defined('PHPCLI_PATH') )
	{
		$php_path = PHPCLI_PATH;
	} else {
		$php_path = 'php';
	}

	// Always use an array for the arguments
	if(!is_array($args))
	{
		$args = func_get_args();
		
		array_shift($args);
	}

	// Escape each arg for usage inside a shell
	$args = array_map('escapeshellarg', $args);

	// Resolve the script's path
	$script = realpath($script);

	// Set the script's path to the current working directory
	chdir(dirname($script));

	// Build the command line to launch the script
	$cmd = $php_path . ' -f ' . escapeshellcmd($script);

	// Check OS
	if( strpos(PHP_OS, 'WIN') === 0 )
	{
		// Windows

		// Append any arguments to the command line
		if(!empty($args)) $cmd .= ' ' . implode(' ', $args);

		// Fallback to using the start utility and redirect its output away
		// to spawn the process in the background.
		$cmd = "start /B \"bg\" {$cmd}";

		// Run the command
		return pclose(popen($cmd, 'r'));
	} else {
		// *nix

		// Append any arguments to the command line
		if(!empty($args)) $cmd .= ' ' . implode(' ', $args);

		// Append a little magic to the command line to force it to 
		// run in the background and return the PID
		$cmd .= ' >/dev/null 2>/dev/null & echo $! & disown';

		// Run the command
		return exec($cmd);
	}
}
?>
