<?php
class AssetException extends Exception { }
class AssetNoModuleException extends AssetException { }

class Asset
{
	public $asset_id;
	public $asset_identifier;
	public $asset_title;
	public $asset_path;
	public $asset_module;
	public $module_id;
	public $asset_format_id;
	public $asset_created_date;
	public $uploaded_user_id;
	public $site_id;
	public $asset_original_filename;
	public $asset_secure;
	public $asset_format_name;
	public $asset_format_label;
	public $asset_format_path;
	public $asset_format_mime;
	public $asset_format_extension;

	public function __construct()
	{
		$args = func_get_args();
		if(!$args)
		{
			// Query instantiation
		} else {
			// Manual instantiation

			$this->asset_module = $args[0];
		}
	}

	public function getURL()
	{
		$path = $this->asset_module . '_path';
		if(!isset($this->$path))
			throw new AssetNoModuleException('Asset path not found! ' . var_export($this));

		return join_path(Config::get('webroot'), $this->asset_module . 's', $this->$path, $this->asset_format_path, $this->asset_path);
	}

	public function __get($var)
	{
		switch(strtolower($var))
		{
			case 'url':
				return $this->getURL();
			default:
				throw new AssetException("Undefined property: $var");
		}
	}
}