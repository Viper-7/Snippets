<?php
$ffmpeg_max_files = Array();
// === START USER SETUP ===

$server_name = "Cerberus";				// The root path name to be shown to the user
$domains = array('viper-7.com', 'randomsuspects.com');	// List of domains accepted as referrers for file download
							//
$publicstore = 1;					// Allow hotlinking to files on the webserver? (paths configured in users.php)
$debug=0;						// Show debug information
$mkpasswd=0;						// Allow password creation (mkpasswd button)
							//
$vod=0;							// Use VLC VOD Server - Superseded by flv module, and clashes with Darwin for the 3gp module, but works great for intranets
$vodport = 4212;					// Port to run VOD Server on
$serverurl = "cerberus.viper-7.com";			// Hostname for VOD Server

$useflv=1;						// Use native FLV streaming (requires ffmpeg)
$use3gp=1;						// Use 3gp queuing\streaming (requires DarwinStreamingServer)
$alternate_flv_player=1;				// Use alternate flv player (Flowplayer), far more robust, but has a logo in the fullscreen mode
$use_mpcpl=1;						// Use Media Player Classic playlists to link direct format video (instead of using the VLC ActiveX Control)

$ffmpeg_max_servers = 2;				// Maximum number of ffmpeg transcoding servers to run at a time
$ffmpeg_max_files['high'] = 3;				// Maximum number of completed videos to keep in the encoder cache
$ffmpeg_max_files['medium'] = 24;			// 
$ffmpeg_max_files['tiny'] = 64;				// 
$ffmpeg_max_files['mobile'] = 64;			// 
$tmpfolder = '/opt/mediacache';				// Folder for temporary files (requires write access)
$browserdir='Browser';
$online=1;

// === END USER SETUP ===


session_start();

include('users.php');
include('functions.php');

if (isset($_GET['user'])) { $username = $_GET['user']; } 			// Comment these 2 lines to disable GET url logins 
if (isset($_GET['pass'])) { $password = $_GET['pass']; } 			// ie (http://your-site/?user=admin&pass=test)
if (isset($_POST['user'])) { $username = $_POST['user']; }			//
if (isset($_POST['pass'])) { $password = $_POST['pass']; }			// Or remove these lines and populate $username and 
if (isset($_SESSION['username'])) { $username = $_SESSION['username']; }	// $password from your own login system here
if (isset($_SESSION['password'])) { $password = $_SESSION['password']; }	//

$addr = split("\\.", $_SERVER['REMOTE_ADDR']);
if($addr[0] . $addr[1] == '100') {
	$local = 1;
	$validhost = 1;
}

if (isset($_SESSION['quality'])) { $quality = $_SESSION['quality']; }
if (isset($_GET['quality'])) { $quality = $_GET['quality']; }
if ($quality=='' && $isMobile) {
	$quality = 'mobile'; }
if ($quality=='' && $isPDA) {
	$quality = 'tiny'; }
if ($quality=='' && $local) {
	$quality = 'direct'; }
if ($quality == '') {
	$quality = 'medium'; }
unset($_SESSION['quality']);
$_SESSION['quality'] = $quality;


include('mobile.php');
include('session.php');
?>
