<div class="block header">
<?php
	echo $config->site->attributes->site_header_content;
	if(isset($theme->assets->images['header']))
	{
		echo "<img src='{$theme->assets->images['header']->url}'/>";
	}
	if(isset($position->attributes->display_top_nav) && $position->attributes->display_top_nav->value)
	{

	}
?>
</div>
