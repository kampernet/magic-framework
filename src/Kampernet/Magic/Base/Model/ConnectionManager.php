<?php
namespace Kampernet\Magic\Base\Model;

/**
 * singleton connection manager class, stores
 * previous connections by a host user pass dbname key
 * and retrieves them by that key
 *
 * @package Kampernet\Magic\Base\Model
 */
class ConnectionManager {

	private static $_instance = null;

	/**
	 * @var \MySQLi[]
	 */
	private $connections;

	/**
	 * private constructor
	 */
	private function __construct() {

		$this->connections = array();
	}

	/**
	 * instance access
	 *
	 * @return ConnectionManager
	 */
	public static function getInstance() {

		if (!self::$_instance) {
			self::$_instance = new ConnectionManager();
		}

		return self::$_instance;
	}

	/**
	 * add a db connection to the pool
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $db_name
	 * @param \MySQLi $conn
	 * @return void
	 */
	public function addConnection($host, $user, $password, $db_name, \MySQLi $conn) {

		$this->connections[md5($host . $user . $password . $db_name)] = $conn;
	}

	/**
	 * get the connection from the pool
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $db_name
	 * @return \MySQLi
	 */
	public function getConnection($host, $user, $password, $db_name) {

		if (isset($this->connections[md5($host . $user . $password . $db_name)])) {
			return $this->connections[md5($host . $user . $password . $db_name)];
		} else {
			return false;
		}
	}

	/**
	 * implemented destructor just to close all connections
	 */
	public function __destruct() {

		foreach ($this->connections as &$conn) {
			if ($conn) {
				@$conn->close();
			}
		}
	}
}