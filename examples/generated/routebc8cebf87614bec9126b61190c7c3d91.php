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
			if (((($offset_0_1 = strpos($parts[0],'-',0)) !== false) AND (($offset_0_3 = strpos($parts[0],'.',$offset_0_1 + 1)) !== false) AND (($value_0_0 = substr($parts[0],0,$offset_0_1))) AND (false === strpos($value_0_0,'.') AND is_numeric($value_0_0)) AND (($value_0_2 = substr($parts[0],1 + $offset_0_1,$offset_0_3 - (1 + $offset_0_1)))) AND ($value_0_4 = substr($parts[0],1 + $offset_0_3) OR ($value_0_4 = 'php') !== false) AND ($offset_0_3 == false OR ('php' == $value_0_4) OR ('html' == $value_0_4) OR ('js' == $value_0_4)))) {
				/* /{foo}-{bar}.{ext} */
				return(array('action' => 'index', 'foo' => $value_0_0, 'bar' => $value_0_2, 'ext' => $value_0_4));
			}
			if (((ctype_alnum($parts[0]) AND ctype_alpha($parts[0][0])))) {
				/* /{controller}/{action} */
				return(array('controller' => $parts[0], 'action' => 'index'));
			}
			break;

		case 2:
			if (((ctype_alnum($parts[0]) AND ctype_alpha($parts[0][0])))) {
				/* /{controller}/{action} */
				return(array('controller' => $parts[0], 'action' => $parts[1]));
			}
			break;

	}
	return(false);
}

function routebc8cebf87614bec9126b61190c7c3d91Build($name,$parts) {
	/* array to URL */
	switch ($name) {
		case 'blog_post':
			if (empty($parts['foo']) OR empty($parts['bar'])) {
				return(false);
			}
			if (empty($parts['ext'])) {
				$parts['ext'] = 'php';
			}
			return('/'.$parts['foo'].'-'.$parts['bar'].'.'.$parts['ext']);
			break;

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
