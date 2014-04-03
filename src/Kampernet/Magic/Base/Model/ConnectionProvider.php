<?php
namespace Kampernet\Magic\Base\Model;

use Exception;
use Kampernet\Magic\Base\Configuration;

/**
 * The connection provider, also a singleton
 *
 * @package Kampernet\Magic\Base\Model
 */
class ConnectionProvider {

	/**
	 * static instance
	 *
	 * @var ConnectionProvider
	 */
	private static $_instance = null;

	/**
	 * private constructor
	 */
	private function __construct() {
	}

	/**
	 * implemented magic clone
	 *
	 * @throws Exception
	 */
	public function __clone() {

		throw new Exception("can not clone the singleton");
	}

	/**
	 * instance accessor
	 *
	 * @return ConnectionProvider
	 */
	public static function getInstance() {

		if (!self::$_instance) {
			self::$_instance = new ConnectionProvider();
		}

		return self::$_instance;
	}

	/**
	 * return the connection as laid out in configuration.xml
	 *
	 * @param string $role
	 * @return DataAccessInterface
	 */
	public function getConnection($role = 'master') {

		$root = basename(realpath(__DIR__ . "/../../../"));
		$database = Configuration::getInstance()->xpath("environment[@rootFolderName='$root']/database[@role='$role']");

		$class = (string) $database[0]->attributes()->class;
		$host = (string) $database[0]->attributes()->host;
		$name = (string) $database[0]->attributes()->name;
		$user = (string) $database[0]->attributes()->user;
		$pass = (string) $database[0]->attributes()->pass;

		return new $class($host, $user, $pass, $name);
	}
}