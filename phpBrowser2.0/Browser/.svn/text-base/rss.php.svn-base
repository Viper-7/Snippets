<?php
require_once('../Browser/checkaddr.php');

$now = format_date(time(),'rss-datetime');

header("Content-Type: application/rss+xml");

echo "<?xml version=\"1.0\"?>
            <rss version=\"2.0\">
                <channel>
                    <title>Latest Videos</title>
                    <link>http://www.viper-7.com/Browser/rss.php</link>
                    <description>Last 10 FLV videos</description>
                    <language>en-us</language>
                    <pubDate>$now</pubDate>
                    <lastBuildDate>$now</lastBuildDate>
                    <managingEditor>viper7@viper-7.com</managingEditor>
                    <webMaster>viper7@viper-7.com</webMaster>
            ";
            
$result = mysql_query("select Filename, Ticket, Quality, Timestamp, Resolution FROM flvTickets ORDER BY Timestamp DESC limit 10");
while ($line = mysql_fetch_assoc($result))
{
    echo "<item><title>" . str_replace('.',' ',basename(substr($line['Filename'],0,strrpos($line['Filename'],'.')))) . "</title>";
    echo "<link>http://www.viper-7.com/flv?ticket=" . $line['Ticket'] . "</link>";
    echo "<description>" . ucfirst($line['Quality']) . " quality - " . $line['Resolution'] .  " - " . format_date(strtotime($line['Timestamp']),'datetime') . "</description>";
    echo "</item>\n";
}
echo "</channel></rss>";
?>
