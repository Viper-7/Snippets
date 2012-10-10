<?php

$iv = '68724868';
$key = 'THISISASECUREENCRYPTIONKEY!!&@&#';
$instr = "password\n\n";

$str = bin2hex(mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $instr, MCRYPT_MODE_CBC, $iv));
echo $str . '<br>';

$str = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, $key, pack('H*',$str), MCRYPT_MODE_CBC, $iv));
echo $str . '<br>';

?>