<?php
$dir = $_GET['dir'];
$file = $_GET['file'];
$act = $_GET['act'];
?>
<body bgcolor=#000000 text=#ffffff link=#ffffff alink=#ffffff vlink=#ffffff>
<center>Select Quality<BR><BR>
<table width=90% border=0>
<TR><TD><a href="vod.php?dir=<?=$dir?>&file=<?=$file?>&act=<?=$act?>&quality=1" target='_blank'>Lowest</a></TD><TD>(256 kbit ADSL)</TD></TR>
<TR><TD><a href="vod.php?dir=<?=$dir?>&file=<?=$file?>&act=<?=$act?>&quality=2" target='_blank'>Low</a></TD><TD>(512 kbit ADSL)</TD></TR>
<TR><TD><a href="vod.php?dir=<?=$dir?>&file=<?=$file?>&act=<?=$act?>&quality=3" target='_blank'>Medium</a></TD><TD>(768 kbit Cable)</TD></TR>
<TR><TD><a href="vod.php?dir=<?=$dir?>&file=<?=$file?>&act=<?=$act?>&quality=4" target='_blank'>High</a></TD><TD>(1.5 mbit ADSL)</TD></TR>
<TR><TD><a href="vod.php?dir=<?=$dir?>&file=<?=$file?>&act=<?=$act?>&quality=5" target='_blank'>Highest</a></TD><TD>(> 2 mbit LAN)</TD></TR>
</table>
<BR><BR>
<form action='vod.php' method='POST'><input type=hidden name=dir value='<?=$dir?>'><input type=hidden name=file value='<?=$file?>'><input type=hidden name=act value='<?=$act?>'>
<input type='text' name='tcor' value='output #transcode{vcodec=mp4v,acodec=mpga,vb=$vbr,ab=$abr,channels=2,fps=24}' size=15><input type=submit value="Go" size=3></form>
</body>
