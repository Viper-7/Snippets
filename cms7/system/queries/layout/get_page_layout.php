<?php
return function($page_id, $layout_id) {
	$db = Registry::get('db');

	$query = $db->prepared_query('
		SELECT
			layout_id,
			layout_name,
			layout_description
		FROM
			cms7_layouts
		WHERE
			layout_id = i:layout_id:
		', $layout_id);

	$layout = $query->fetchObjs();
	if(count($layout) != 1)
		throw new Exception('Layout not found');

	$layout = $layout[0];
	$layout->positions = fetch_layout_positions($page_id, $layout_id);

	return (object)$layout;
};

function fetch_layout_positions($page_id, $layout_id, $parent_layout_position_id = NULL)
{
	$db = Registry::get('db');

	$query = $db->prepared_query('
		SELECT
			a.page_layout_position_id,
			a.page_layout_position_title,
			a.page_version_id,
			a.module_output_id,
			a.layout_position_id,
			a.layout_position_order,
			b.layout_position_id,
			b.layout_position_name,
			b.layout_id,
			b.parent_layout_position_id,
			coalesce(c.childcount, 0) childcount,
			d.site_id,
			e.module_name,
			e.module_path,
			f.module_output_name,
			f.module_output_path,
			g.attribute_count
		FROM
			cms7_pages as d,
			cms7_modules as e,
			cms7_module_outputs as f,
			cms7_page_layout_positions as a
			LEFT JOIN (
				SELECT
					page_layout_position_id,
					count(module_attribute_value_id) as attribute_count
				FROM
					cms7_module_attribute_values
				GROUP BY
					page_layout_position_id
			) as g
			ON (
				g.page_layout_position_id = a.page_layout_position_id
			),
			cms7_layout_positions as b
			LEFT JOIN (
				SELECT
					parent_layout_position_id,
					count(layout_position_id) childcount
				FROM
					cms7_layout_positions
				WHERE
					parent_layout_position_id IS NOT NULL
				GROUP BY
					parent_layout_position_id
			) as c
			ON (
				c.parent_layout_position_id = b.layout_position_id
			)
		WHERE
			a.page_version_id = d.live_page_version_id
			AND d.page_id = i:page_id:
			AND a.layout_position_id = b.layout_position_id
			AND b.layout_id = i:layout_id:
			AND e.module_id = f.module_id
			AND f.module_output_id = a.module_output_id
			AND b.parent_layout_position_id = i:parent_layout_position_id:
		ORDER BY
			a.layout_position_order
		', $page_id, $layout_id, $parent_layout_position_id);

	$result = $query->fetchObjs();

	foreach($result as &$row)
	{
		if($row->attribute_count)
		{
			$query = $db->prepared_query('
				SELECT
					a.module_attribute_id,
					a.module_attribute_name,
					a.module_attribute_label,
					a.module_attribute_order,
					a.module_attribute_default_value,
					b.attribute_type_name,
					b.attribute_type_code,
					b.attribute_type_data_type,
					c.module_attribute_value as value,
					c.module_attribute_value_id
				FROM
					cms7_module_attributes as a,
					cms7_attribute_types as b,
					cms7_module_attribute_values as c
				WHERE
					a.module_attribute_id = c.module_attribute_id
					AND (
						a.module_output_id = i:module_output_id:
						OR a.module_output_id IS NULL
					)
					AND a.attribute_type_id = b.attribute_type_id
					AND c.page_layout_position_id = i:page_layout_position_id:
					AND c.site_id = i:site_id:
				ORDER BY
					a.module_attribute_order
				', $row);

			$row->attributes = $query->fetchObjs();

			$attributes = array();
			foreach($row->attributes as $key => $value)
			{
				$attributes[$value->module_attribute_name] = $value;
			}
			$row->attributes = (object)$attributes;
		} else {
			$row->attributes = new StdClass();
		}

		if($row->childcount > 0)
		{
			$row->children = fetch_layout_positions($page_id, $layout_id, $row->layout_position_id);
		}
	}

	return $result;
}