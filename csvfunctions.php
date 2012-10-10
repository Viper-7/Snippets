<?php
if(!function_exists('str_getcsv')) {
    function str_getcsv($input, $delimiter = ',', $enclosure = '"') {
        $fp = fopen('php://temp', 'w+');
	fputs($fp, $input . "\n");
        rewind($fp);
        while(!feof($fp)) {
		$data[] = fgetcsv($fp, 0, $delimiter, $enclosure);
	}
        fclose($fp);
	return $data;
    }
}

if(!function_exists('str_putcsv')) {
    function str_putcsv($input, $delimiter = ',', $enclosure = '"') {
        $fp = fopen('php://temp', 'w+');
        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        while(!feof($fp)) {
		$data = fgets($fp) . "\n";
	}
        fclose($fp);
        return rtrim( $data, "\n" );
    }
}

print_r(str_getcsv('"test", "test2", "test3"'));

