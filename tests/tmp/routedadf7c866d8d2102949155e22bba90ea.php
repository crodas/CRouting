<?php
function routedadf7c866d8d2102949155e22bba90ea($url) {
	/* route_simple.yml */
	$curl = preg_replace('/(\\/)+|\\?.*/','$1',$url);
	$length = substr_count($curl,'/');
	if (substr($curl,-1) == '/') {
		$length = $length - 1;
	}
	$hasMethod = isset($_SERVER['REQUEST_METHOD']);
	/* /page/{foo} */
	if (($length == 2) AND preg_match('~^/(page)/(?P<foo>\\d+)/?$~',$curl,$match)) {
		return(array('controller' => 'page', 'action' => 'index', 'foo' => $match['foo']));
	}
	/* /get/foo */
	if (($length == 2) AND preg_match('~^/(get)/(foo)/?$~',$curl,$match)) {
		return(array('controller' => 'request', 'action' => 'check'));
	}
	/* / */
	if (($hasMethod == true) AND preg_match('~POST|DELETE~',$_SERVER['REQUEST_METHOD']) AND ($length == 0) AND preg_match('~^/?$~',$curl,$match)) {
		return(array('controller' => 'request', 'action' => 'check'));
	}
	/* /y/{three}/x/{four}a{five}b{six}.{ext} */
	if (($length >= 3) AND ($length <= 4) AND preg_match('~^/(y)(/(?P<three>\\d+)?)?/(x)/(?P<four>\\d+)?(a)(?P<five>\\d+)?(b)(?P<six>\\d+)?(\\.)?(?P<ext>php|xml|json)?/?$~',$curl,$match)) {
		return(array('controller' => 'news', 'action' => 'history', 'three' => empty($match['three']) ? 3 : $match['three'], 'four' => empty($match['four']) ? 4 : $match['four'], 'five' => empty($match['five']) ? 5 : $match['five'], 'six' => empty($match['six']) ? 6 : $match['six'], 'ext' => empty($match['ext']) ? 'php' : $match['ext']));
	}
	/* /rest/{id}.{action}.{format} */
	if (($length == 2) AND preg_match('~^/(rest)/(?P<id>[a-zA-Z0-9-_]+)(\\.)?(?P<action>[a-zA-Z0-9-_]+)?(\\.)?(?P<format>[a-zA-Z0-9-_]+)?/?$~',$curl,$match)) {
		return(array('controller' => 'rest', 'id' => $match['id'], 'action' => empty($match['action']) ? 'status' : $match['action'], 'format' => empty($match['format']) ? 'json' : $match['format']));
	}
	/* /history/year/{year}/{page} */
	if (($length >= 3) AND ($length <= 4) AND preg_match('~^/(history)/(year)/(?P<year>\\d+)(/(?P<page>\\d+)?)?/?$~',$curl,$match)) {
		return(array('controller' => 'news', 'action' => 'history', 'year' => $match['year'], 'page' => empty($match['page']) ? 0 : $match['page']));
	}
	/* /post/{id}-{slug}/{page} */
	if (($length >= 2) AND ($length <= 3) AND preg_match('~^/(post)/(?P<id>[0-9]+)(\\-)(?P<slug>[a-zA-Z0-9-_]+)(/(?P<page>\\d+)?)?/?$~',$curl,$match)) {
		return(array('controller' => 'news', 'action' => 'index', 'id' => $match['id'], 'slug' => $match['slug'], 'page' => empty($match['page']) ? 0 : $match['page']));
	}
	/* /{controller}/{action} */
	if (($length >= 1) AND ($length <= 2) AND preg_match('~^/(?P<controller>[a-zA-Z0-9-_]+)(/(?P<action>[a-zA-Z]+)?)?/?$~',$curl,$match)) {
		return(array('controller' => $match['controller'], 'action' => empty($match['action']) ? 'index' : $match['action']));
	}
	/* / */
	if (($length == 0) AND preg_match('~^/?$~',$curl,$match)) {
		return(array('controller' => 'foo', 'action' => 'bar'));
	}
	return(false);
}

function routedadf7c866d8d2102949155e22bba90eaBuild($name,$parts) {
	/* array to URL */
}
