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
	 * @return SimpleXMLElement
	 */
	public static function getInstance() {

		if (!self::$_instance) {
			self::$_instance = simplexml_load_file(realpath(__DIR__ . "/../") . "/app/application.xml");
		}

		return self::$_instance;
	}
}