<?php
namespace Kampernet\Magic\Base\Session;

/**
 * PHP class to represent the session, does lazy loading 
 * for session start, only starting the session when properties
 * are setted or getted
 *
 * @package Kampernet\Magic\Base\Session
 */
class Session {

	const SESSION_STARTED = 1;
	const SESSION_NOT_STARTED = 2;
	
	/**
	 * @var Session
	 */
	private static $_instance;
	
	/**
	 * store on the instance whether 
	 * the session was started or not
	 * @var int
	 */
	private $state;
	
	/**
	 * the session handling behaviour
	 * @var SessionInterface
	 */
	private $handler;
	
	/**
	 * private constructor for singleton
	 */
	private function __construct() { 
		$this->state = self::SESSION_NOT_STARTED;
	}

	/**
	 * get the instance
	 * @return Session
	 */
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new Session();
		}
		
		return self::$_instance;
	}
	
	/**
	 * @param SessionInterface $handler
	 */
	public function setHandler(SessionInterface $handler) {
		$this->handler = $handler;
	}
	
	/**
	 * starts the session if not already started
	 * @return Session
	 */
	public function start() {
		if ($this->state == self::SESSION_NOT_STARTED) {
			if (!isset($this->handler)) {
				// defaults to default handler
				$this->handler = new SessionDefault();
			}
			$this->handler->start();
			$this->state = self::SESSION_STARTED;
		}
		return $this;
	}

	/**
	 * magic set to work as new session
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value) {
		$this->start();
		$this->$name = $value;
		$_SESSION["data"][$name] = $value;
	}
	
	/**
	 * magic get to work with new session
	 * @param string $name
	 * @return string
	 */
	public function &__get($name) {
		$this->start();
		return (isset($_SESSION["data"][$name])) ? $_SESSION["data"][$name] : null;
	}

	/**
	 * checks the session property
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) {
		$this->start();
		return isset($_SESSION["data"][$name]);
	}
	
	/**
	 * so that it's allowed on the XML_Object
	 */
	public function __toString() {
		return ""; 
	}
	
}