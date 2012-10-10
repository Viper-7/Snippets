<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>DaisyDiff-php</title>
		<link rel="stylesheet" href="diff.css" type="text/css" media="screen"/>
	</head>
	<body>
		<?php
		include 'diffclass.php';
		
		if(!empty($_REQUEST['from']) && !empty($_REQUEST['to'])) {
			$diff = new DiffClass();
			echo $diff->diffURL($_REQUEST['from'], $_REQUEST['to']);
		} else {
			?>
			<form method="post" action="">
				<table>
					<thead>
						<tr>
							<th colspan="2">
								Diff Changes
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="2">
								<input type="submit" value="Diff!"/>
							</td>
						</tr>
					<tbody>
						<tr>
							<td>
								From URL:
							</td>
							<td>
								http://<input type="text" name="from" size="40" value="www.viper-7.com/diff/sample1.htm"/>
							</td>
						</tr>
						<tr>
							<td>
								To URL:
							</td>
							<td>
								http://<input type="text" name="to" size="40" value="www.viper-7.com/diff/sample2.htm"/>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		<?php
		}
		?>
	</body>
</html>
