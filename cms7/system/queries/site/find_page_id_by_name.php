<?php
return function($name) {
	$db = Registry::get('db');
	$site = Config::get('site');

	$query = $db->prepared_query('
			SELECT
				a.page_id
			FROM
				cms7_pages as a
			WHERE
				a.site_id = i:site_id:
				AND a.page_link_name = s:name:
			ORDER BY
				a.page_order
			', $site->site_id, $name);

	$page = $query->fetchObjs();

	if($page)
		return $page[0]->page_id;
};