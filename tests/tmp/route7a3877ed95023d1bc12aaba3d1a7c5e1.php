<?php
function route7a3877ed95023d1bc12aaba3d1a7c5e1($url) {
	$curl = preg_replace('/^\\/+|(\\/)+|\\?.*/','$1',$url);
	$parts = explode('/',$curl);
	$length = count($parts);
	if (empty($parts[$length - 1])) {
		unset($parts[$length - 1]);
		$length = $length - 1;
	}
	$hasMethod = isset($_SERVER['REQUEST_METHOD']);
	switch ($length) {
		case 1:
			if ((Validator::test($parts[0]))) {
				/* /{action}/{page} */
				return(array('action' => $parts[0], 'page' => '0'));
			}
			if ((mycustom_validator($parts[0]))) {
				/* /{action}/{page} */
				return(array('action' => $parts[0], 'page' => '0'));
			}
			break;

		case 2:
			if ((Validator::test($parts[0])) AND (is_numeric($parts[1]))) {
				/* /{action}/{page} */
				return(array('action' => $parts[0], 'page' => $parts[1]));
			}
			if ((mycustom_validator($parts[0])) AND (is_numeric($parts[1]))) {
				/* /{action}/{page} */
				return(array('action' => $parts[0], 'page' => $parts[1]));
			}
			break;

	}
	return(false);
}
