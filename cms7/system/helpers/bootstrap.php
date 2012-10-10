<?php
/*
 * bootstrap.php
 *
 * Contains the procedural code to be executed at the start of new a request.
 * Miscellaneous global scope functions should be included from here.
 *
 */

	include 'system/helpers/singleton.php';
	include 'system/helpers/registry.php';
	include 'system/helpers/config.php';
	include 'system/helpers/autoload.php';

	function cms7_exception_handler($e)
	{
		ob_clean();
		echo '<b>A Fatal error has occured</b><br/>';
		echo $e->getMessage() . '<br/>';
		echo '<pre>', print_r($e, TRUE), '</pre>';
	}

	set_exception_handler('cms7_exception_handler');
	
	function cms7_error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$trace = debug_backtrace();
?>
<fieldset><legend>A Fatal error has occured</legend>
<table border="0">
	<thead></thead>
	<tfoot></tfoot>
	<tbody>
		<tr>
			<td>
				<strong>Message:</strong> <?php echo $errstr; ?>
			</td>
		</tr>
		<?php foreach($trace as $line) { ?>
			<tr>
				<td>
					<?php echo '<strong>Source:</strong> ' . $line['file'] . ':' . $line['line']; ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
</fieldset>
<?php
	}

	set_error_handler('cms7_error_handler');

	function join_path()
	{
		$args = func_get_args();

		array_map(function($path) { return rtrim($path, DIRECTORY_SEPARATOR); }, $args);

		return implode(DIRECTORY_SEPARATOR, $args);
	}

	function getWebserverPath($target_path)
	{
		$org_target_path = $target_path;

		// Get the real physical path of the file we want to link to
		$target_path = realpath($target_path);

		// Handle . and .. folders which realpath wont add a trailing slash to
		if($target_path == '.' || $target_path == '..') $target_path .= '/';

		// Get the physical and virtual paths of the script requested by the user
		$webserver_path = dirname($_SERVER['SCRIPT_NAME']);
		$local_path = dirname($_SERVER['SCRIPT_FILENAME']);

		// Convert the paths into arrays of elements
		$webserver_path_elem = explode('/', ltrim($webserver_path,'/'));
		$local_path_elem = explode(DIRECTORY_SEPARATOR, ltrim($local_path,DIRECTORY_SEPARATOR));


		// Iterate over each path element (from right to left)
		end($webserver_path_elem); end($local_path_elem);
		do
		{
			// If the element exists in both paths, remove it
			if(current($webserver_path_elem) == current($local_path_elem))
			{
				array_pop($webserver_path_elem);
				array_pop($local_path_elem);
			}

		} while(prev($webserver_path_elem) && prev($local_path_elem));

		// Generate the physical path of the virtual root from the remaining elements
		$local_webserver_root = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $local_path_elem) . DIRECTORY_SEPARATOR;


		// Remove the physical path of the webserver root from the target path, and replace it with the virtual root path
		if(strpos($target_path, $local_webserver_root) !== FALSE)
		{
			$target_path = '/' . str_replace($local_webserver_root, '', $target_path);
			if(!empty($webserver_path_elem))
				$target_path = '/' . implode('/', $webserver_path_elem) . $target_path;
		}
		else
		{
			if(!$target_path)
				trigger_error('File not found: ' . $org_target_path);
			else
				trigger_error('Target path is outside webroot: ' . $org_target_path);

			return FALSE;
		}


		// Return the virtual path to the target file
		return $target_path;
	}

	function asset_link($type, $asset)
	{
		$path = $type . '_path';
		return join_path(Config::get('webroot'), $type . 's', $asset->$path, $asset->asset_format_path, $asset->asset_path);
	}