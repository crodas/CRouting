<?php
function routedadf7c866d8d2102949155e22bba90ea($url) {
	$curl = preg_replace('/^\\/+|(\\/)+|\\?.*/','$1',$url);
	$parts = explode('/',$curl);
	$length = count($parts);
	if (empty($parts[$length - 1])) {
		unset($parts[$length - 1]);
		$length = $length - 1;
	}
	$hasMethod = isset($_SERVER['REQUEST_METHOD']);
	switch ($length) {
		case 0:
			if (($hasMethod == true) AND (('POST' == $_SERVER['REQUEST_METHOD']) OR ('DELETE' == $_SERVER['REQUEST_METHOD']))) {
				/* / */
				return(array('controller' => 'request', 'action' => 'check'));
			}
			return(array('controller' => 'foo', 'action' => 'bar'));
			break;

		case 1:
			return(array('controller' => $parts[0], 'action' => 'index'));
			break;

		case 2:
			if ((($parts[0] == 'page')) AND ((false !== filter_var($parts[1],FILTER_VALIDATE_INT)))) {
				/* /page/{foo} */
				return(array('controller' => 'page', 'action' => 'index', 'foo' => $parts[1]));
			}
			if ((($parts[0] == 'get')) AND (($parts[1] == 'foo'))) {
				/* /get/foo */
				return(array('controller' => 'request', 'action' => 'check'));
			}
			if ((($parts[0] == 'post')) AND ((($offset_1_1 = strpos($parts[1],'-',0)) !== false) AND (($value_1_0 = substr($parts[1],0,$offset_1_1))) AND (false === strpos($value_1_0,'.') AND is_numeric($value_1_0)) AND (($value_1_2 = substr($parts[1],1 + $offset_1_1))))) {
				/* /post/{id}-{slug}/{page} */
				return(array('controller' => 'news', 'action' => 'index', 'id' => $value_1_0, 'slug' => $value_1_2, 'page' => 0));
			}
			if ((ctype_alpha($parts[1]))) {
				/* /{controller}/{action} */
				return(array('controller' => $parts[0], 'action' => $parts[1]));
			}
			break;

		case 3:
			if ((($parts[0] == 'y')) AND (($parts[1] == 'x')) AND ((($offset_3_1 = strpos($parts[2],'a',0)) !== false) AND (($offset_3_3 = strpos($parts[2],'b',$offset_3_1 + 1)) !== false) AND (($offset_3_5 = strpos($parts[2],'.',$offset_3_3 + 1)) !== false) AND ($value_3_0 = substr($parts[2],0,$offset_3_1) OR ($value_3_0 = 4) !== false) AND ($offset_3_1 == false OR false === strpos($value_3_0,'.') AND is_numeric($value_3_0)) AND ($value_3_2 = substr($parts[2],1 + $offset_3_1,$offset_3_3 - (1 + $offset_3_1)) OR ($value_3_2 = 5) !== false) AND ($offset_3_1 == false OR false === strpos($value_3_2,'.') AND is_numeric($value_3_2)) AND ($value_3_4 = substr($parts[2],1 + $offset_3_3,$offset_3_5 - (1 + $offset_3_3)) OR ($value_3_4 = 6) !== false) AND ($offset_3_3 == false OR false === strpos($value_3_4,'.') AND is_numeric($value_3_4)) AND ($value_3_6 = substr($parts[2],1 + $offset_3_5) OR ($value_3_6 = 'php') !== false) AND ($offset_3_5 == false OR ('php' == $value_3_6) OR ('xml' == $value_3_6) OR ('json' == $value_3_6)))) {
				/* /y/{three}/x/{four}a{five}b{six}.{ext} */
				return(array('controller' => 'news', 'action' => 'history', 'three' => 3, 'four' => $value_3_0, 'five' => $value_3_2, 'six' => $value_3_4, 'ext' => $value_3_6));
			}
			if ((($parts[0] == 'history')) AND (($parts[1] == 'year')) AND ((false === strpos($parts[2],'.') AND is_numeric($parts[2])))) {
				/* /history/year/{year}/{page} */
				return(array('controller' => 'news', 'action' => 'history', 'year' => $parts[2], 'page' => 0));
			}
			if ((($parts[0] == 'post')) AND ((($offset_1_1 = strpos($parts[1],'-',0)) !== false) AND (($value_1_0 = substr($parts[1],0,$offset_1_1))) AND (false === strpos($value_1_0,'.') AND is_numeric($value_1_0)) AND (($value_1_2 = substr($parts[1],1 + $offset_1_1)))) AND ((false === strpos($parts[2],'.') AND is_numeric($parts[2])))) {
				/* /post/{id}-{slug}/{page} */
				return(array('controller' => 'news', 'action' => 'index', 'id' => $value_1_0, 'slug' => $value_1_2, 'page' => $parts[2]));
			}
			break;

		case 4:
			if ((($parts[0] == 'y')) AND ((false === strpos($parts[1],'.') AND is_numeric($parts[1]))) AND (($parts[2] == 'x')) AND ((($offset_3_1 = strpos($parts[3],'a',0)) !== false) AND (($offset_3_3 = strpos($parts[3],'b',$offset_3_1 + 1)) !== false) AND (($offset_3_5 = strpos($parts[3],'.',$offset_3_3 + 1)) !== false) AND ($value_3_0 = substr($parts[3],0,$offset_3_1) OR ($value_3_0 = 4) !== false) AND ($offset_3_1 == false OR false === strpos($value_3_0,'.') AND is_numeric($value_3_0)) AND ($value_3_2 = substr($parts[3],1 + $offset_3_1,$offset_3_3 - (1 + $offset_3_1)) OR ($value_3_2 = 5) !== false) AND ($offset_3_1 == false OR false === strpos($value_3_2,'.') AND is_numeric($value_3_2)) AND ($value_3_4 = substr($parts[3],1 + $offset_3_3,$offset_3_5 - (1 + $offset_3_3)) OR ($value_3_4 = 6) !== false) AND ($offset_3_3 == false OR false === strpos($value_3_4,'.') AND is_numeric($value_3_4)) AND ($value_3_6 = substr($parts[3],1 + $offset_3_5) OR ($value_3_6 = 'php') !== false) AND ($offset_3_5 == false OR ('php' == $value_3_6) OR ('xml' == $value_3_6) OR ('json' == $value_3_6)))) {
				/* /y/{three}/x/{four}a{five}b{six}.{ext} */
				return(array('controller' => 'news', 'action' => 'history', 'three' => $parts[1], 'four' => $value_3_0, 'five' => $value_3_2, 'six' => $value_3_4, 'ext' => $value_3_6));
			}
			if ((($parts[0] == 'history')) AND (($parts[1] == 'year')) AND ((false === strpos($parts[2],'.') AND is_numeric($parts[2]))) AND ((false === strpos($parts[3],'.') AND is_numeric($parts[3])))) {
				/* /history/year/{year}/{page} */
				return(array('controller' => 'news', 'action' => 'history', 'year' => $parts[2], 'page' => $parts[3]));
			}
			break;

	}
	return(false);
}

function routedadf7c866d8d2102949155e22bba90eaBuild($name,$parts) {
	/* array to URL */
	switch ($name) {
		case 'checkType':
			if (empty($parts['foo'])) {
				return(false);
			}
			return('/page/'.$parts['foo']);
			break;

		case 'checkMethod':
			return('/get/foo');
			break;

		case 'onlyPostAndDelete':
			return('/');
			break;

		case 'longOptional':
			if (empty($parts['three'])) {
				$parts['three'] = 3;
			}
			if (empty($parts['four'])) {
				$parts['four'] = 4;
			}
			if (empty($parts['five'])) {
				$parts['five'] = 5;
			}
			if (empty($parts['six'])) {
				$parts['six'] = 6;
			}
			if (empty($parts['ext'])) {
				$parts['ext'] = 'php';
			}
			return('/y/'.$parts['three'].'/x/'.$parts['four'].'a'.$parts['five'].'b'.$parts['six'].'.'.$parts['ext']);
			break;

		case 'blog_post_two':
			if (empty($parts['year'])) {
				return(false);
			}
			if (empty($parts['page'])) {
				$parts['page'] = 0;
			}
			return('/history/year/'.$parts['year'].'/'.$parts['page']);
			break;

		case 'blog_post':
			if (empty($parts['id']) OR empty($parts['slug'])) {
				return(false);
			}
			if (empty($parts['page'])) {
				$parts['page'] = 0;
			}
			return('/post/'.$parts['id'].'-'.$parts['slug'].'/'.$parts['page']);
			break;

		case 'index':
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
