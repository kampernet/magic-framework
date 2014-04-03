<?php
namespace Kampernet\Magic\Base\Util;

use Reflector;

/**
 * a simple class to parse out annotations into an associative array
 *
 * @package Kampernet\Magic\Base\Util
 */
class AnnotationsParser {

	/**
	 * gets the annotations
	 *
	 * @param Reflector $reflector
	 * @return array
	 */
	public static function getAnnotations(Reflector $reflector) {

		$info = array();

		if ($comments = $reflector->getDocComment()) {
			if ($comments !== false) {
				$data = substr(trim(preg_replace('/\r?\n *\* */', ' ', $comments)), 0, -1);
				$yes = preg_match_all('/@([a-z]+)\s+(.*?)\s*(?=$|@[a-z]+\s)/s', $data, $matches);
				if ($yes) {
					$info = array_combine($matches[1], $matches[2]);
				}
			}
		}

		foreach ($info as &$inf) {
			$ack = explode("\n", $inf);
			$inf = $ack[0];
		}

		return $info;

	}
}