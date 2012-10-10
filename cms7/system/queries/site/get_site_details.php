<?php
return function() {
	$db = Registry::get('db');
	
	$query = $db->prepared_query('
		SELECT
			a.site_id,
			a.site_name,
			a.site_base_url,
			a.site_home_page_id,
			a.theme_id
		FROM
			cms7_sites as a,
			cms7_site_hosts as b
		WHERE
			a.site_id = b.site_id	
			AND b.host_name = s:host_name:
			AND b.activated = 1
		', $_SERVER['HTTP_HOST']);
	
	$site = $query->fetchObjs();
	
	if($site)
		$site = $site[0];
	else
		throw new Exception('Site not found');
	
	$query = $db->prepared_query('
			SELECT
				a.site_attribute_id,
				a.site_attribute_name,
				a.site_attribute_label,
				a.site_attribute_order,
				a.site_attribute_default_value,
				b.attribute_type_name,
				b.attribute_type_code,
				b.attribute_type_data_type,
				c.site_attribute_value as value,
				c.site_attribute_value_id
			FROM
				cms7_site_attributes as a,
				cms7_attribute_types as b,
				cms7_site_attribute_values as c
			WHERE
				a.site_attribute_id = c.site_attribute_id
				AND a.attribute_type_id = b.attribute_type_id
				AND c.site_id = i:site_id:
			ORDER BY
				a.site_attribute_order
			', $site);
	
	$attributes = $query->fetchObjs('CMS7_Attribute');
	
	$site->attributes = new StdClass();
	foreach($attributes as $attribute)
	{
		$site->attributes->{$attribute->site_attribute_name} = $attribute;
	}
	
	return $site;
};