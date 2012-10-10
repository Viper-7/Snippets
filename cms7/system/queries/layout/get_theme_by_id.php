<?php
return function($theme_id) {
	$db = Registry::get('db');

	$query = $db->prepared_query('
		SELECT
			theme_id,
			theme_name,
			theme_path,
			theme_base_style
		FROM
			cms7_themes
		WHERE
			theme_id = i:theme_id:
		', $theme_id);

	$theme = $query->fetchObjs();
	if(count($theme) != 1)
		throw new Exception('Theme not found');

	$query = $db->prepared_query('
		SELECT
			a.asset_id,
			b.theme_path,
			b.theme_base_style,
			c.asset_identifier,
			c.asset_title,
			c.asset_path,
			c.module_id,
			c.asset_created_date,
			c.uploaded_user_id,
			c.site_id,
			c.asset_original_filename,
			c.asset_secure,
			d.asset_format_name,
			d.asset_format_label,
			d.asset_format_path,
			d.asset_format_mime,
			d.asset_format_extension,
			"theme" as asset_module
		FROM
			cms7_theme_assets as a,
			cms7_themes as b,
			cms7_assets as c,
			cms7_asset_formats as d
		WHERE
			a.theme_id = b.theme_id
			AND (
				b.theme_id = i:theme_id:
				OR b.theme_base_style = 1
			)
			AND a.asset_id = c.asset_id
			AND c.asset_format_id = d.asset_format_id
		ORDER BY
			b.theme_base_style DESC
		', $theme_id);

	$assets = $query->fetchObjs('Asset');
	$assetList = array();

	foreach($assets as $asset)
	{
		$assetList[$asset->asset_format_path][$asset->asset_identifier] = $asset;
	}

	$theme[0]->assets = (object)$assetList;

	return $theme[0];
};
