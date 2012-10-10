<?php
	$secure=1;
	$dir='Movies';
	require_once('checkaddr.php');
	
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$starttime = $time;
//<BODY BGCOLOR="#050608" TEXT="808080" LINK="#505050" VLINK="#505050" ALINK="lightgrey">
?>

<HTML><HEAD><TITLE>Movie Thumbnails</TITLE>
<link rel="stylesheet" media="all" type="text/css" href="/css/balloons.css"/>
</HEAD>
<BODY BGCOLOR="#050608" TEXT="808080" LINK="#505050" VLINK="#505050" ALINK="lightgrey">
<TABLE WIDTH=100% HEIGHT=100% CELLSPACING=0 CELLPADDING=0><TR><TD COLSPAN=3>

<?php	
	$cells = 18;
	
	$tag='';
	$start=0;
	$sort='releasedate';
	if(isset($_GET['start'])) $start = $_GET['start'];
	
	$end=$start+$cells;
	if(isset($_GET['tag'])) $tag = htmlspecialchars($_GET['tag']);
	if(isset($_GET['sort'])) $sort = htmlentities($_GET['sort']);
	
	echo("<center>");
	echo("Sorting: &nbsp; <a href='movies.php?tag=$tag&start=0&sort=releasedate'>Release Date</a> &nbsp; ");
	echo("<a href='movies.php?tag=$tag&start=0&sort=rating'>Rating</a> &nbsp; ");
	echo("<a href='movies.php?tag=$tag&start=0&sort=name'>Name</a>");
	if($tag != '') echo("<BR><a href='movies.php?start=0&sort=$sort'>View All</a>");
	echo("<BR></TD></TR><TR><TD ALIGN=CENTER><center>");
	
	if($sort != 'name') { $sqlsort = $sort . ' DESC'; } else { $sqlsort = $sort; }
	
	if($tag != '') {
		$result = mysql_query("SELECT imdb.ID, Name, Title, Tagline, Plot, IMDBURL, BoxURL, Rating, ReleaseDate, Duration FROM imdb, imdbtags WHERE imdbtags.imdbid=imdb.id AND imdbtags.tag LIKE '$tag' ORDER BY $sqlsort LIMIT $start, " . ($cells + 1));
	} else {
		$result = mysql_query("SELECT ID, Name, Title, Tagline, Plot, IMDBURL, BoxURL, Rating, ReleaseDate, Duration FROM imdb ORDER BY $sqlsort LIMIT $start, " . ($cells + 1));
	}
	
	$prev = ($start-$cells);
	if($prev < 0) $prev = 0;
	
	echo "<TD width=80> &nbsp; ";
	if ($start > 0) echo ("<a href='movies.php?tag=$tag&start=$prev&sort=$sort'><img src='images/prev.png' border=0></a> &nbsp; &nbsp; &nbsp; ");
	echo ("</TD><TD ALIGN=CENTER>");
	echo ("<div class='balloon'><ul>\n");
	
	$count = 0;
	while (list($id, $name, $title, $tagline, $plot, $imdburl, $boxurl, $rating, $releasedate, $duration) = mysql_fetch_array($result)) {
		$count++;
		$releasedate=date('jS F Y',$releasedate);
		//if ($count % $numcols == 1) echo ("<TR>");
		echo ("<li id='movie$count'>");
		echo ("<A HREF='/flv/?imdbid=$id'>");
		echo ("<IMG SRC='/Media/" . $boxurl . "' ALT=\"\" TITLE=\"\"/>");
		echo ("<!--[if IE 7]><!--></a><!--<![endif]-->");
		echo ("<table><tr><td>"); // Tooltip
			echo ("<dl><dt>" . "&nbsp;</dt><dd>");
			echo ("<p align=center id='head'>" . $name . "</p><HR/>");
			echo ("<p id='plot'>$plot<BR>Released: $releasedate</p>");
			echo ("<p><table width=100% id='links'><TR><TD>");
				$hours = floor($duration / 3600);
				$mins = str_pad(floor(($duration % 3600) / 60),2,'0',STR_PAD_LEFT);
				$secs = str_pad(floor($duration % 60),2,'0',STR_PAD_LEFT);
				$runtime = "$hours:$mins:$secs";
				echo ("$rating/10 &nbsp; ");
				if ($online) { echo ("<a href='$imdburl' ALT='IMDB' TITLE='IMDB'>IMDB</a> &nbsp; "); }
				echo ("<a href='/flv/?imdbid=$id' ALT='Watch' TITLE='Watch'>Watch</a> &nbsp; ");
				echo ("<a href='download.php?imdbid=$id' ALT='Download' TITLE='Download'>Download</a>");
				echo ("</td><td align=right>$runtime</td>");
			echo ("</td></tr></table></p>");
			echo ("</dd></dl>");
		echo ("</td></tr></table>");
		echo ("<!--[if lte IE 6]></a><![endif]-->");
		echo ("</li>");
		if($count==$cells) break;
	}
	echo ("</ul></DIV></TD>");
	
	echo "<TD width=90>";
	if(mysql_num_rows($result) > $cells) echo ("&nbsp; &nbsp; &nbsp; <a href='movies.php?tag=$tag&start=$end&sort=$sort'><img src='images/next.png' border=0></a>");
	echo " &nbsp; </TD>";

	echo ("</TD></TR><TR HEIGHT=80><TD COLSPAN=3><CENTER>");
	
	$result = mysql_query("select tag, count(tag) as num from imdbtags group by tag order by num DESC LIMIT 500");
	
	while ($row = mysql_fetch_array($result)) {
	    if ($row['num'] > 5) {
	    	$tags[$row['tag']] = $row['num'];
	    }
	}
	
	if (count($tags) > 0)
		shuffle_with_keys($tags,12);

	$result = mysql_query("select tag, count(tag) as num from imdbtags group by tag order by num DESC LIMIT 16");

	while ($row = mysql_fetch_array($result)) {
	    if ($row['num'] > 5) {
	    	$tags[$row['tag']] = $row['num'];
	    }
	}

	if (count($tags) > 0)
		shuffle_with_keys($tags,18);
	
	// change these font sizes if you will
	$max_size = 200; // max font size in %
	$min_size = 100; // min font size in %
	
	// get the largest and smallest array values
	$max_qty = max(array_values($tags));
	$min_qty = min(array_values($tags));
	
	// find the range of values
	$spread = $max_qty - $min_qty;
	if (0 == $spread) { // we don't want to divide by zero
	    $spread = 1;
	}
	
	$step = ($max_size - $min_size)/($spread);
	
	$count=0;
	foreach ($tags as $key => $value) {
		$count += strlen($key);
		if($count>=80 && $count < 1000) { echo '<BR>'; $count=1000; }
		if($count>=1080) { break; }
		$size = $min_size + (($value - $min_qty) * $step);
		echo "<a href=\"movies.php?tag=$key&sort=$sort\" style=\"font-size: $size%\">$key</a> ";
	}

	
	list($nummovies) = mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM imdb WHERE ID in (SELECT imdbid FROM imdbtags WHERE tag LIKE '%$tag%')"));
	echo("<BR><font color='#333333'>$nummovies Movies");
	if($tag != '')
		echo " with the tag $tag";
	echo(" - ");
	chdir('/opt/filestore/Movies');
	echo(exec("du -sh | cut -f 1") . 'b');
	echo(" total");
		
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$finish = $time;
	$totaltime = round(($finish - $starttime),4);
	echo(" - Generated in $totaltime seconds.</font>");
?>
</TD></TR></TABLE></BODY></HTML>