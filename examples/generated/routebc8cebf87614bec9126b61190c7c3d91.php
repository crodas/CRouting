<?php
function routebc8cebf87614bec9126b61190c7c3d91($url) {
	/* ./simple.yml */
	$curl = preg_replace('/(\\/)+|\\?.*/','$1',$url);
	$length = substr_count($curl,'/');
	if (substr($curl,-1) == '/') {
		$length = $length - 1;
	}
	if ($length == 1) {
		if (preg_match('~^/(?P<foo>\\d+)(:?\\-)?(?P<bar>[a-zA-Z0-9-_]+)(:?\\.)(?P<ext>php|html|js)?/?$~',$curl,$match)) {
			return(array('action' => 'index', 'foo' => $match['foo'], 'bar' => $match['bar'], 'ext' => empty($match['ext']) ? 'php' : $match['ext']));
		}
	}
	if (($length >= 1) AND ($length <= 2)) {
		if (preg_match('~^/(?P<controller>[a-zA-Z][a-zA-Z0-9]+)(/(?P<action>[a-zA-Z0-9-_]+)?)?/?$~',$curl,$match)) {
			return(array('controller' => $match['controller'], 'action' => empty($match['action']) ? 'index' : $match['action']));
		}
	}
	if ($length == 0) {
		if (preg_match('~^/?$~',$curl,$match)) {
			return(array('controller' => 'home', 'action' => 'index'));
		}
	}
	return(false);
}

function routebc8cebf87614bec9126b61190c7c3d91Build($name,$parts) {
	/* array to URL */
}
