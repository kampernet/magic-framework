<?php
namespace Kampernet\Magic\Base;

use Exception, SimpleXMLElement;

/**
 * Application is a mock Singleton SimpleXML wrapper class
 * so we don't have to keep re-opening
 * the application.xml file
 *
 * @package Kampernet\Magic\Base
 */
class Application {

	private static $_instance = null;

	/**
	 * private constructor
	 */
	private function __construct() {
	}

	/**
	 * override clone
	 *
	 * @throws Exception
	 */
	public function __clone() {

		throw new Exception("can not clone the singleton");
	}

	/**
	 * Wraps the application.xml as a SimpleXMLElement object
	 * note, the root path only needs be set the fisrt time you call it.
	 * ( ie: your front controller )
	 *
	 * @param string $path
	 * @return SimpleXMLElement
	 */
	public static function getInstance($path = "") {

		if (!self::$_instance) {
			self::$_instance = simplexml_load_file($path . "/application.xml");
		}

		return self::$_instance;
	}
}