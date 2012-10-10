<?php
require 'pdov.php';

$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");

$db = new PDOV('', 'viper7', '***', 'goliath:1521/v7dev', 'oci');
$db->useMemcache($memcache);

$id = isset($_GET['id']) ? $_GET['id'] : 1;

$stmt = $db->preparedQuery('
	SELECT
		test_id,
		title,
		dbms_lob.substr(body,4000) body
	FROM
		test_table
	WHERE
		test_id = i:id:
	',  $id);

$records = $stmt->fetchObjs();

if(!empty($records))
{
    echo "Title: {$records[0]->TITLE}<br/>";
	echo "Body: {$records[0]->BODY}";
}
else
{
    echo "Sorry, I couldn't find that title";
}
