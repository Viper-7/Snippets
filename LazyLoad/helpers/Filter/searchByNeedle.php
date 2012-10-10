<?php
return function($needle) {
	return function($haystack) use ($needle) {
		return strpos($haystack, $needle) !== FALSE;
	};
};