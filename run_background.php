<?php
function run_background($cmd, $args = array(), $cwd = null) {
	if($cwd)
		chdir($cwd);

	$args = array_map('escapeshellarg', $args);
	
	// Check OS
	if( strpos(PHP_OS, 'WIN') === 0 ) {
		// Windows

		if(!empty($args)) $run = escapeshellcmd($cmd) . ' ' . implode(' ', $args);

		// Use the start utility and redirect its output away to spawn the process in the background.
		$run = "start /B \"bg\" {$run}";

		$pids = array_map('trim', explode("\n", shell_exec("Wmic process where (commandline like '%{$cmd}%') get ProcessId")));

		// Run the command
		pclose(popen($run, 'r'));
		
		$newpids = array_map('trim', explode("\n", shell_exec("Wmic process where (commandline like '%{$cmd}%') get ProcessId")));
		
		$new = array_diff($newpids, $pids);
		
		if($new)
			return reset($new);
		
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
