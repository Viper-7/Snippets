<div class="block container vertical">
<?php
	if(isset($children) && is_array($children))
	{
		echo '<div>' . implode('</div><div>', $children) . '</div>';
	}
?>
</div>
