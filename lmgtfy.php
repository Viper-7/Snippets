<?php
######################################################################
##### LetMeGoogleThatForYou redirection mirror by edK and viper7 #####
######################################################################
#Version: 0.5
#Rev 29.05.2009
#Please report any bugs 2 The /*47*/ MemoryOfTre /*d0t*/ es
#My nicks @ irc.freenode.net(ORDER BY `total_time_used` DESC): _AnywhereIs_, InCaribbeanBlue, ADayWithoutRain, Web31337
#More: http://lmg.web31337.org/
#
#Hosted @ google code: http://lmg.googlecode.com/
#Hosted @ sourceforge: http://sf.net/projects/lmgtfy-redir/
#
# License: ./LICENSE
#
##########################################################################
## Copyright (C) 2009 edK & viper7                                      ##
## This program is free software: you can redistribute it and/or modify ##
## it under the terms of the GNU General Public License as published by ##
## the Free Software Foundation, either version 3 of the License, or    ##
## (at your option) any later version.                                  ##
##                                                                      ##
## This program is distributed in the hope that it will be useful,      ##
## but WITHOUT ANY WARRANTY; without even the implied warranty of       ##
## MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        ##
## GNU General Public License for more details.                         ##
##                                                                      ##
## You should have received a copy of the GNU General Public License    ##
## along with this program. If not, see <http://www.gnu.org/licenses/>. ##
##########################################################################


// Retrieve the query string from the URL
$script = trim(str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']),'/');
$q = trim(str_replace($script, '', $_SERVER['SCRIPT_NAME']),'/');

// Check for blank query
if(empty($q)) {
	@readfile("about.html");
	die();
}

// Display help / information if requested
$commands = array(	'--email' => "You just drop a line to `The _aT* MemoryOfTre d0t es` :)",
			'--source' => 'See <a href="http://sf.net/projects/lmgtfy-redir/">http://sf.net/projects/lmgtfy-redir/</a> and <a href="http://lmg.web31337.org/>http://lmg.web31337.org/</a> for sources."',
			'--v' => "<b>".$_SERVER['SERVER_NAME']."</b> uses 'LMGTFY PHP Redirect Mirror' ver 0.5 by <a href='http://www.viper-7.com' target='_blank'>Viper-7</a> - Based on 0.4 by <a href=\"http://The.MemoryOfTre.es/vcard\" target=\"_blank\">edK</a>",
			'favicon.ico' => '',
			'--help' => "<table style='min-width:800px'>
	<tr><td colspan='2'><b>".$_SERVER['SERVER_NAME']." usage:</b></td><td>[site|inurl]/term_to_search[/l]</td></tr>
	<tr><td colspan='3'>&nbsp;</td></tr>
	<tr><td>Example:</td><td>/hack netbios-ssn/l</td><td>redirect to http://lmgtfy.com/?q=hack+netbios-ssn&l=1</td></tr>
	<tr><td>Example:</td><td>/install php/</td><td>redirect to http://lmgtfy.com/?q=install+php</td></tr>
	<tr><td>Example:</td><td>/site/php.net header/</td><td>redirect to http://lmgtfy.com/?q=site:php.net+header</td></tr>
	<tr><td>Example:</td><td>/inurl/wp-content/themes style.css/l</td><td>redirect to http://lmgtfy.com/?q=inurl:wp-content/themes+style.css&l=1</td></tr>
	</table>"
);
if(isset($commands[strtolower($q)])) die($commands[strtolower($q)]);

// Handle prefixes - Strip them from the query and put them in $prefix
$prefix = strtolower(substr($q, 0, strcspn($q, '/')));
switch($prefix) {
	case 'inurl':
	case 'site':
		$q = trim(substr($q, strcspn($q, '/')),'/');
		$prefix .= ':';
}

// Handle the I'm Feeling Lucky switch
$lucky = FALSE;
if(substr($q, -2) == '/l') {
	$q = substr($q,0,-2);
	$lucky = TRUE;
}

// If theres no query left, show an error
if(empty($q)) die("No Query!");

// Build the URL and redirect them!
$redir="http://lmgtfy.com/?q=".urlencode($prefix.$q);
if($lucky) $redir.="&l=1";

header("Location: $redir");