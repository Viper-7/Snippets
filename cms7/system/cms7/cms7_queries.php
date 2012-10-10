<?php
class CMS7_Queries extends LazyLoad
{
	protected function _init($path = 'system/queries', $parent_path = NULL)
	{
		if(!isset($parent_path))
			$parent_path = Config::get('base_path');

		parent::_init($path, $parent_path);
	}
}