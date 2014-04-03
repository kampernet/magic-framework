<?php
namespace Kampernet\Magic\Session;

use Kampernet\Magic\Base\Session\SessionInterface;
use Kampernet\Magic\Base\Environment;

/**
 * use the memcache session handler.
 * TODO remove and replace with Symfony session classes
 *
 * @package Kampernet\Magic\Session
 */
class SessionMemcache implements SessionInterface {
	
	/**
	 * (non-PHPdoc)
	 * @see SessionInterface::start()
	 */
	public function start() {

		/*
		 * in configuration.xml for example put
		 * <constant name="memcacheServer" value="127.0.0.1" /> 
		 * <constant name="memcachePort" value="11211" /> 
		 * in constants if you want to use this 
		 */
		$memcacheServer = Environment::getInstance()->memcacheServer;
		$memcachePort = Environment::getInstance()->memcachePort;
		
		$server = "tcp://$memcacheServer:$memcachePort";

		// set the handlers
		ini_set("session.save_handler", "memcache");
		ini_set("session.save_path", $server);
		
		session_start();

	}
	
}