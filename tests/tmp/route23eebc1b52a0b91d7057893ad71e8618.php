<?php
function route23eebc1b52a0b91d7057893ad71e8618($url) {
	/* /home/crodas/projects/playground/CRouting/tests/mainTest.php */
	$curl = preg_replace('/(\\/)+|\\?.*/','$1',$url);
	$length = substr_count($curl,'/');
	if (substr($curl,-1) == '/') {
		$length = $length - 1;
	}
	if ($length == 0) {
		/* / */
		if (preg_match('~^/?$~',$curl,$match)) {
			return(array('foo' => 1));
		}
	}
	return(false);
}

function route23eebc1b52a0b91d7057893ad71e8618Build($name,$parts) {
	/* array to URL */
}
