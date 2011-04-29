<?php
function routedadf7c866d8d2102949155e22bba90ea($url) {
	/* route_simple.yml */
	$curl = preg_replace('/(\\/)+|\\?.*/','$1',$url);
	$length = substr_count($curl,'/');
	if (substr($curl,-1) == '/') {
		$length = $length - 1;
	}
	$hasMethod = isset($_SERVER['REQUEST_METHOD']);
	if ($length == 2) {
		/* /page/{foo} */
		if (preg_match('~^/(:?page)/(?P<foo>\\d+)/?$~',$curl,$match)) {
			return(array('controller' => 'page', 'action' => 'index', 'foo' => $match['foo']));
		}
		/* /get/foo */
		if (preg_match('~^/(:?get)/(:?foo)/?$~',$curl,$match)) {
			return(array('controller' => 'request', 'action' => 'check'));
		}
		/* /rest/{id}.{action}.{format} */
		if (preg_match('~^/(:?rest)/(?P<id>[a-zA-Z0-9-_]+)(:?\\.)?(?P<action>[a-zA-Z0-9-_]+)?(:?\\.)?(?P<format>[a-zA-Z0-9-_]+)?/?$~',$curl,$match)) {
			return(array('controller' => 'rest', 'id' => $match['id'], 'action' => empty($match['action']) ? 'status' : $match['action'], 'format' => empty($match['format']) ? 'json' : $match['format']));
		}
	}
	if ($length == 0) {
		/* / */
		if (($hasMethod == true) AND preg_match('~POST|DELETE~',$_SERVER['REQUEST_METHOD']) AND preg_match('~^/?$~',$curl,$match)) {
			return(array('controller' => 'request', 'action' => 'check'));
		}
		/* / */
		if (preg_match('~^/?$~',$curl,$match)) {
			return(array('controller' => 'foo', 'action' => 'bar'));
		}
	}
	if (($length >= 3) AND ($length <= 4)) {
		/* /y/{three}/x/{four}a{five}b{six}.{ext} */
		if (preg_match('~^/(:?y)(/(?P<three>\\d+)?)?/(:?x)/(?P<four>\\d+)?(:?a)(?P<five>\\d+)?(:?b)(?P<six>\\d+)?(:?\\.)?(?P<ext>php|xml|json)?/?$~',$curl,$match)) {
			return(array('controller' => 'news', 'action' => 'history', 'three' => empty($match['three']) ? 3 : $match['three'], 'four' => empty($match['four']) ? 4 : $match['four'], 'five' => empty($match['five']) ? 5 : $match['five'], 'six' => empty($match['six']) ? 6 : $match['six'], 'ext' => empty($match['ext']) ? 'php' : $match['ext']));
		}
		/* /history/year/{year}/{page} */
		if (preg_match('~^/(:?history)/(:?year)/(?P<year>\\d+)(/(?P<page>\\d+)?)?/?$~',$curl,$match)) {
			return(array('controller' => 'news', 'action' => 'history', 'year' => $match['year'], 'page' => empty($match['page']) ? 0 : $match['page']));
		}
	}
	if (($length >= 2) AND ($length <= 3)) {
		/* /post/{id}-{slug}/{page} */
		if (preg_match('~^/(:?post)/(?P<id>[0-9]+)(:?\\-)?(?P<slug>[a-zA-Z0-9-_]+)?(/(?P<page>\\d+)?)?/?$~',$curl,$match)) {
			return(array('controller' => 'news', 'action' => 'index', 'id' => $match['id'], 'slug' => empty($match['slug']) ? '' : $match['slug'], 'page' => empty($match['page']) ? 0 : $match['page']));
		}
	}
	if (($length >= 1) AND ($length <= 2)) {
		/* /{controller}/{action} */
		if (preg_match('~^/(?P<controller>[a-zA-Z0-9-_]+)(/(?P<action>[a-zA-Z]+)?)?/?$~',$curl,$match)) {
			return(array('controller' => $match['controller'], 'action' => empty($match['action']) ? 'index' : $match['action']));
		}
	}
	return(false);
}

function routedadf7c866d8d2102949155e22bba90eaBuild($name,$parts) {
	/* array to URL */
}
