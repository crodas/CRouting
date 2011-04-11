<?php
function route7fbcb89e542b3ee5102474ea0cd75295($url) {
	$curl = preg_replace('/^\\/+|(\\/)+|\\?.*/','$1',$url);
	$parts = explode('/',$curl);
	$length = count($parts);
	if (empty($parts[$length - 1])) {
		unset($parts[$length - 1]);
		$length = $length - 1;
	}
	switch ($length) {
		case 1:
			if ((preg_match('/^(:?bar|xxx)?foo$/',$parts[0]))) {
				/* /{action} */
				return(array('action' => $parts[0]));
			}
			if ((preg_match('/^foo(:?bar|xxx)?foo$/',$parts[0]))) {
				/* /{action}/{page} */
				return(array('action' => $parts[0], 'page' => 0));
			}
			break;

		case 2:
			if ((($parts[0] == 'something')) AND (ctype_alpha($parts[1]))) {
				/* /something/{action} */
				return(array('action' => $parts[1]));
			}
			if ((preg_match('/^foo(:?bar|xxx)?foo$/',$parts[0])) AND ((false === strpos($parts[1],'.') AND is_numeric($parts[1])))) {
				/* /{action}/{page} */
				return(array('action' => $parts[0], 'page' => $parts[1]));
			}
			break;

	}
	return(false);
}

function route7fbcb89e542b3ee5102474ea0cd75295Build($name,$parts) {
	/* array to URL */
	switch ($name) {
		case 'regex3':
			if (empty($parts['action'])) {
				return(false);
			}
			return('/something/'.$parts['action']);
			break;

		case 'regex2':
			if (empty($parts['action'])) {
				return(false);
			}
			return('/'.$parts['action']);
			break;

		case 'regex1':
			if (empty($parts['action'])) {
				return(false);
			}
			if (empty($parts['page'])) {
				$parts['page'] = 0;
			}
			return('/'.$parts['action'].'/'.$parts['page']);
			break;

	}
}
