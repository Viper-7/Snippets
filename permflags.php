<?php
	function getPermFlags($data)
	{
		$codes = ($data & 2048 ? 'u' : '-').($data & 1024 ? 'g' : '-').($data & 512 ? 's' : '-');
		$owner = ($data & 256 ? 'r' : '-').($data & 128 ? 'w' : '-').($data & 64 ? 'x' : '-');
		$group = ($data & 32 ? 'r' : '-').($data & 16 ? 'w' : '-').($data & 8 ? 'x' : '-');
		$world = ($data & 4 ? 'r' : '-').($data & 2 ? 'w' : '-').($data & 1 ? 'x' : '-');
		
		return array(
			'codes' => $codes,
			'owner' => $owner, 
			'group' => $group, 
			'world' => $world, 
			'octal' => str_pad(decoct($data),4,'0',STR_PAD_LEFT),
			'flags' => $codes . $owner . $group . $world
		);
	}
		
	xdebug_var_dump(getPermFlags(63));
?>
