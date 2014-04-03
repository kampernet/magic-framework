<?php
namespace Kampernet\Magic\Base;

/**
 * an object representing the request
 * it is a singleton object so you get the request
 * by calling the getInstance method.
 *
 * @package Kampernet\Magic\Base
 */
class Request extends AbstractRequest {

	private static $_instance = null;

	/**
	 * Instantiates an HTTP Request from the PHP superglobals
	 */
	private function __construct() {

		$this->params = array_merge($_GET, $_POST);

		if (isset($_SERVER['REQUEST_URI'])) {
			$uriParts = explode('?', $_SERVER['REQUEST_URI']);

			$path = $uriParts[0];
			$path = trim($path, '/');

			($path) ? $pathParts = explode('/', urldecode($path)) : $pathParts = array();
		} else {
			$pathParts = $_SERVER['argv'];
			array_shift($pathParts);
		}

		$this->path = $pathParts;
		$this->server = $_SERVER;

		$this->response = new Response();

	}

	/**
	 * @return Request
	 */
	public static function getInstance() {

		if (!self::$_instance) {
			self::$_instance = new Request();
		}

		return self::$_instance;
	}
}