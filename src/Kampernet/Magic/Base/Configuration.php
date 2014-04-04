<?php
namespace Kampernet\Magic\Base;

use SimpleXMLElement, Exception;

/**
 * Configuration is a mock Singleton SimpleXML wrapper class
 * so we don't have to keep re-opening
 * the configuration.xml file
 *
 * @package Kampernet\Magic\Base
 */
class Configuration {

	/**
	 * @var SimpleXMLElement
	 */
	private static $_instance = null;

	/**
	 * private constructor
	 */
	private function __construct() {
	}

	/**
	 * overrideen clone
	 *
	 * @throws Exception
	 */
	public function __clone() {

		throw new Exception("can not clone the singleton");
	}

	/**
	 * wraps the configuration.xml as a simplexml element
	 * note, the root path only need be set the first time
	 * you use it. ( ie: your front controller )
	 *
	 * @param string $path
	 * @return SimpleXMLElement
	 */
	public static function getInstance($path = "") {

		if (!self::$_instance) {
			self::$_instance = simplexml_load_file($path . "/configuration.xml");
		}

		return self::$_instance;
	}
}
