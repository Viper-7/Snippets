function checkFileType($filename) {
	$fp=fopen($filename,'rb')
	$magicnumber=fread($fp,2)
	fclose($fp)
	switch($magicnumber){
		case 0xFFD8:
			return 'JPEG';
			break;
		case 0x424D:
			return 'BMP';
			break;a
		case 0x0A05:
			return 'PCX';
			break;
		case 0x8950:
			return 'PNG';
			break;
		case 0x2321:
			return 'BASH';
			break;
		case 0x2550:
			return 'PDF';
			break;
		case 0x4D5A:
			return 'MSDOS_EXE';
			break;
		case 0x7F45:
			return 'ELF_EXE';
			break;
		case 0x4749:
			return 'GIF';
			break;
		case 0x504B:
			return 'ZIP';
			break;
		case 0x4949:
			return 'II_TIFF';
			break;
		case 0x4D4D:
			return 'MM_TIFF';
			break;
		case 0xFEFF:
			return 'UTF-8_BE';
			break;
		case 0xFFFE:
			return 'UTF-8_LE';
			break;
		default:
			echo 'Unhandled file type';
	}
}