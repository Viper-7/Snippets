<?php
return function($page_id) {
	$db = Registry::get('db');

	$query = $db->prepared_query('
		SELECT
			a.page_version_id,
			a.page_id,
			a.page_version_title,
			a.page_version_content,
			a.layout_id,
			a.theme_id,
			a.page_version_secure,
			a.page_version_status,
			a.creator_user_id,
			a.owner_user_id,
			a.approver_user_Id,
			a.page_version_live_date,
			a.page_version_created_date,
			a.page_version_expiry_date
		FROM
			cms7_page_versions as a
		WHERE
			page_id = i:page_id:
		ORDER BY
			a.page_version_id DESC
		', $page_id);

	$result = $query->fetchObjs();

	if($result)
		return $result[0];
};