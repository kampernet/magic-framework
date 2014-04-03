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
	 * magic get impl
	 *
	 * @param string $name
	 */
	public function __get($name) {

		return $this->$name;
	}

	/**
	 * wraps the configuration.xml as a simplexml element
	 *
	 * @return SimpleXMLElement
	 */
	public static function getInstance() {

		if (!self::$_instance) {
			self::$_instance = simplexml_load_file(realpath(__DIR__ . "/../") . "/app/configuration.xml");
		}

		return self::$_instance;
	}
}
