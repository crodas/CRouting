<?php
function route1b2c668bee4ec5b56d398e824d9da4e9($url) {
	$curl = preg_replace('/^\\/+|(\\/)+|\\?.*/','$1',$url);
	$parts = explode('/',$curl);
	$length = count($parts);
	if (empty($parts[$length - 1])) {
		unset($parts[$length - 1]);
		$length = $length - 1;
	}
	if ($length == 0) {
		return(array('foo' => '1'));
	}
	return(false);
}
