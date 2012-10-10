<?php 
require_once('config.php'); 
if ($isMobile || $isPDA) {
?>
<HEAD>

<SCRIPT LANGUAGE="JavaScript">
<!-- Begin
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,addressbar=0,statusbar=0,menubar=0,resizable=0,width=220,height=185,left = 465,top = 412');");
}
// End -->
</script>
</HEAD>
<body class="body" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor=#FAFAFA>
<link href="default.css" rel="stylesheet" type="text/css">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" bgcolor=#FAFAFA>
	<tr> 
		<td height="8" valign="top"></td>
	</tr>
	<tr> 
		<td align="center" valign="top">
			<table width="200" border="0" cellspacing="0" cellpadding="0">
				<tr> 
					<td>
						<table width="100%" height="10" border="0" cellpadding="0" cellspacing="0">
							<tr> 
								<td width="12" height="10"><img src="images/end_1.gif" width="12" height="10"></td>
								<td width="100%" height="10" background="images/glo_tile2.gif"></td>
								<td width="13" height="10"><img src="images/end_2.gif" width="13" height="10"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr> 
					<td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr> 
								 <td width="100%" height="13" align="right" bgcolor="#EEEEEE" style="background-image: url(images/format_greybar.jpg); background-repeat: no-repeat;"><img src="images/add_format_text.gif" width="135" height="13"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr> 
					<td width="100%" height="54">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr> 
								<td width=16 background="images/format_greybar_leftside.gif">
								<td width="169" height="100%" bgcolor=#EEEEEE>
									<table align="center" border="0" cellpadding="0" width="169"><TR><TD>
										<?php include('browser.php'); ?>
									</table>
								</td>
								<td width=15 background='images/right.jpg'>
							</tr>
						</table>
					</td>
				</tr>
				<tr> 
					<td>
						<table width=100% cellspacing=0 cellpadding=0 border=0 background="images/bottom_grey_centre.gif">
							<TR>
								<TD align="left"><img src="images/bottom_grey_left.gif">
								<TD align="right"><img src="images/bottom_grey_right.gif">
							</TR>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
<?php
} else {
?>
<HEAD>

<SCRIPT LANGUAGE="JavaScript">
<!-- Begin
function popUp(URL) {
day = new Date();
id = day.getTime();
eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,addressbar=0,statusbar=0,menubar=0,resizable=0,width=220,height=185,left = 465,top = 412');");
}
// End -->
</script>
</HEAD>
<body class="body" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor=#FAFAFA>
<link href="default.css" rel="stylesheet" type="text/css">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" bgcolor=#FAFAFA>
	<tr> 
		<td height="23" valign="top"></td>
	</tr>
	<tr> 
		<td height="34" align="center" valign="top">
			<table width="600" border="0" cellspacing="0" cellpadding="0">
				<tr> 
					<td>
						<table width="100%" height="10" border="0" cellpadding="0" cellspacing="0">
							<tr> 
								<td width="12" height="10"><img src="images/end_1.gif" width="12" height="10"></td>
								<td width="100%" height="10" background="images/glo_tile2.gif"></td>
								<td width="13" height="10"><img src="images/end_2.gif" width="13" height="10"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr> 
					<td>
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr> 
								<td width="100%" height="23" align="right" bgcolor="#EEEEEE" style="background-image: url(images/format_greybar.jpg); background-repeat: no-repeat;"><img src="images/add_format_text.gif" width="135" height="23"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr> 
					<td width="100%" height="54">
						<table width="100%" border="0" cellspacing="0" cellpadding="0">
							<tr> 
								<td width=16 background="images/format_greybar_leftside.gif">
								<td width="569" height="213" bgcolor=#EEEEEE>
									<table align=center border=1 cellpadding=15 width=550><TR><TD>
										<?php include('browser.php'); ?>
									</table>
								</td>
								<td width=15 background='images/right.jpg'>
							</tr>
						</table>
					</td>
				</tr>
				<tr> 
					<td>
						<table width=100% cellspacing=0 cellpadding=0 border=0 background="images/bottom_grey_centre.gif">
							<TR>
								<TD align="left"><img src="images/bottom_grey_left.gif">
								<TD align="right"><img src="images/bottom_grey_right.gif">
							</TR>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</body>
<?php
}
?>