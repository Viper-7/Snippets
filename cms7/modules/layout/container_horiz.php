<div class="block container horizontal">
<?php
	if(isset($children) && is_array($children))
	{
		echo '<table><tr><td>' . implode('</td><td>', $children) . '</td></tr></table>';
	}
?>
</div>
