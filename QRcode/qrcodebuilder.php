<?php
	include 'phpqrcode/qrlib.php';

	//QRcode::png('some othertext 1234', 'out.png', 'H');
	$qr = new QRencode();
	$tab = $qr->encode('PHP QR Code :)');
	QRspec::debug($tab, true);

?>