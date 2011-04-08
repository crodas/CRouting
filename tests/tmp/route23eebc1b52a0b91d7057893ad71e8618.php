<?php
function route23eebc1b52a0b91d7057893ad71e8618($url) {
	$curl = preg_replace('/^\\/+|(\\/)+|\\?.*/','$1',$url);
	$parts = explode('/',$curl);
	$length = count($parts);
	if (empty($parts[$length - 1])) {
		unset($parts[$length - 1]);
		$length = $length - 1;
	}
	$hasMethod = isset($_SERVER['REQUEST_METHOD']);
	if ($length == 0) {
		return(array('foo' => '1'));
	}
	return(false);
}
