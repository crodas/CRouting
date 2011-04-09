<?php
function route23eebc1b52a0b91d7057893ad71e8618($url) {
	$curl = preg_replace('/^\\/+|(\\/)+|\\?.*/','$1',$url);
	$parts = explode('/',$curl);
	$length = count($parts);
	if (empty($parts[$length - 1])) {
		unset($parts[$length - 1]);
		$length = $length - 1;
	}
	switch ($length) {
		case 0:
			return(array('foo' => '1'));
			break;

	}
	return(false);
}

function route23eebc1b52a0b91d7057893ad71e8618Build($name,$rules) {
	/* array to URL */
	switch ($name) {
		case 'foobar':
			break;

	}
}
