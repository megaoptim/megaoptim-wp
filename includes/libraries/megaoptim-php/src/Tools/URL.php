<?php
namespace MegaOptim\Tools;

class URL {
	/**
	 * Validates url
	 * @param $resource
	 *
	 * @return bool
	 */
	public static function validate($resource) {
		$pattern = "#(?i)\b((?:[a-z][\w-]+:(?:/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))#iS";
		preg_match( $pattern, $resource, $matches );
		return !empty($matches);
	}
}