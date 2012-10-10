<p>ASCII to Binary &amp; Binary to ASCII</p>
<form method="post">
<input type="text" name="input" size="40"> &nbsp; <input type="submit" value="Convert">
</form>
<pre>
<?php
if(isset($_POST['input'])) {
	if(preg_match('/^[01 ]+$/', $_POST['input'])) {
		$bin = $_POST['input'];
		while(strlen($bin) >= 8) {
			$char = substr($bin,0,8);
			$bin = trim(substr($bin,8));
			echo "Binary: " . chr(bindec($char)) . "\n";
			echo "Hex: " . implode(' ', str_split(dechex(bindec($char)), 2)) . "\n";
		}
	} else {
		$string = $_POST['input'];
		$chars = str_split($string);
		foreach($chars as $char) {
			echo str_pad(decbin(ord($char)),8,'0', STR_PAD_LEFT) . ' ';
		}
	}
}