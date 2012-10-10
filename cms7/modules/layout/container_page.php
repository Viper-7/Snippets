<!doctype html>
<html>
	<head>
		<title><?php echo $page->page_version_title; ?></title>
		<meta content="text/html; charset=utf-8" http-equiv="content-type"/>
		<?php
			HTMLHead::getInstance()->outputHead();
		?>
	</head>
	<body>
		<div id="page_container">
			<?php echo implode("\n", $children); ?>
		</div>
	</body>
</html>