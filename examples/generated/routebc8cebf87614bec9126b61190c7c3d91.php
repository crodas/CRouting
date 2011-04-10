<?php
function routebc8cebf87614bec9126b61190c7c3d91($url) {
	$curl = preg_replace('/^\\/+|(\\/)+|\\?.*/','$1',$url);
	$parts = explode('/',$curl);
	$length = count($parts);
	if (empty($parts[$length - 1])) {
		unset($parts[$length - 1]);
		$length = $length - 1;
	}
	switch ($length) {
		case 0:
			return(array('controller' => 'home', 'action' => 'index'));
			break;

		case 1:
			return(array('controller' => $parts[0], 'action' => 'index'));
			break;

		case 2:
			return(array('controller' => $parts[0], 'action' => $parts[1]));
			break;

	}
	return(false);
}

function routebc8cebf87614bec9126b61190c7c3d91Build($name,$parts) {
	/* array to URL */
	switch ($name) {
		case 'normal':
			if (empty($parts['controller'])) {
				return(false);
			}
			if (empty($parts['action'])) {
				$parts['action'] = 'index';
			}
			return('/'.$parts['controller'].'/'.$parts['action']);
			break;

		case 'default':
			return('/');
			break;

	}
}
