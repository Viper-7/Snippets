<?php
return function($haystack) {
	return function($needle) use ($haystack) {
		return strpos($haystack, $needle) !== FALSE;
	};
};