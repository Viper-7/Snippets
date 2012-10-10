<?php
return function() {
	return function($string) {
		return preg_replace('/\s+/', '', $string);
	};
};