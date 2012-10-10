<?php
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$starttime = $time;

	$secure=1;
	$dir='Series 1';
	require_once('checkaddr.php');
?>

<HTML><HEAD><TITLE>Series Thumbnails</TITLE>
<link rel="stylesheet" media="all" type="text/css" href="/css/seriesballoons.css" />
</HEAD>
<BODY BGCOLOR="#050608" TEXT="808080" LINK="#505050" VLINK="#505050" ALINK="lightgrey">
<TABLE WIDTH=100% HEIGHT=100% CELLSPACING=0 CELLPADDING=0>

<?php	
	$cells = 14;

	$tag='';
	$start=0;
	$sort='releasedate';
	if(isset($_GET['start'])) $start = $_GET['start'];
	
	$end=$start+$cells;
	if(isset($_GET['tag'])) $tag = htmlspecialchars($_GET['tag']);
	if(isset($_GET['sort'])) $sort = htmlentities($_GET['sort']);

	echo("<TR><TD ALIGN=CENTER><center>");
	
	if($sort != 'name') { $sqlsort = $sort . ' DESC'; } else { $sqlsort = $sort; }
	
	$result = mysql_query("SELECT DISTINCT series.ID, Name, Folder, Summary, IMDBURL, ImageURL, count(tag) as pop FROM series left join seriestags on series.id=seriestags.seriesid GROUP BY series.id ORDER BY pop DESC LIMIT $start, " . ($cells+1));
	
	$prev = ($start-$cells);
	if($prev < 0) $prev = 0;
	
	echo "<TD width=80> &nbsp; ";
	if ($start > 0) echo ("<a href='?start=$prev'><img src='images/prev.png' border=0></a> &nbsp; &nbsp; &nbsp; ");
	echo ("</TD><TD ALIGN=CENTER>");
	echo ("<div class='balloon'><ul>\n");

	$count = 0;
	while (list($id, $name, $folder, $summary, $imdburl, $boxurl) = mysql_fetch_array($result)) {
		$count++;
		$summary = substr($summary,0,180) . '...';
		if ($count > $cells)
			break;
		echo ("<li id='movie$count'>");
		echo ("<A HREF='/Browser/?dir=&symlink=$folder'>");
		echo ("<IMG SRC='/Media/$boxurl' ALT=\"\" TITLE=\"\" WIDTH='379' HEIGHT='70'/>");
		echo ("<!--[if IE 7]><!--></a><!--<![endif]-->");
		echo ("<table><tr><td>"); // Tooltip
			echo ("<dl><dt>" . "&nbsp;</dt><dd>");
			echo ("<p align=center id='head'>" . $name . "</p><HR/>");
			echo ("<p id='plot'>$summary</p>");
			echo ("<p><table width=100% id='links'><TR><TD>");
			if ($online) { echo ("<a href='$imdburl' ALT='IMDB' TITLE='IMDB'>IMDB</a> &nbsp; &nbsp; "); }
			echo ("<a href='/Browser/?dir=&symlink=$folder' ALT='Watch' TITLE='Watch'>Browse Files</a> &nbsp; &nbsp; ");
			echo ("<a href='#' ALT='Download' TITLE='Download'>Watch Next Episode</a>");
			echo ("</td></tr></table></p>");
			echo ("</dd></dl>");
		echo ("</td></tr></table>");
		echo ("<!--[if lte IE 6]></a><![endif]-->");

		echo ("</li>");
	}
	echo ("</ul></DIV></TD>");
	
	echo "<TD width=90>";
	if($count > $cells) echo ("&nbsp; &nbsp; &nbsp; <a href='?start=$end'><img src='images/next.png' border=0></a>");
	echo " &nbsp; ";

	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$finish = $time;
	$totaltime = round(($finish - $starttime),4);
	echo("<TR><TD COLSPAN=3><center><font color='#333333'>Page generated in $totaltime seconds.</font>");
?>
</TD></TR></TABLE></BODY></HTML>