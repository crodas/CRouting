<?php
function route7fbcb89e542b3ee5102474ea0cd75295($url) {
	/* route_regex.yml */
	$curl = preg_replace('/(\\/)+|\\?.*/','$1',$url);
	$length = substr_count($curl,'/');
	if (substr($curl,-1) == '/') {
		$length = $length - 1;
	}
	if ($length == 2) {
		/* /something/{action} */
		if (preg_match('~^/(:?something)/(?P<action>[a-zA-Z]+)/?$~',$curl,$match)) {
			return(array('action' => $match['action']));
		}
	}
	if ($length == 1) {
		/* /{action} */
		if (preg_match('~^/(?P<action>(:?bar|xxx)?foo)/?$~',$curl,$match)) {
			return(array('action' => $match['action']));
		}
	}
	if (($length >= 1) AND ($length <= 2)) {
		/* /{action}/{page} */
		if (preg_match('~^/(?P<action>[a-zA-Z0-9-_]+)(/(?P<page>\\d+)?)?/?$~',$curl,$match)) {
			return(array('action' => $match['action'], 'page' => empty($match['page']) ? 0 : $match['page']));
		}
	}
	return(false);
}

function route7fbcb89e542b3ee5102474ea0cd75295Build($name,$parts) {
	/* array to URL */
}
