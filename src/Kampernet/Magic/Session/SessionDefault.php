<?php
namespace Kampernet\Magic\Session;

use Kampernet\Magic\Base\Session\SessionInterface;

/**
 * use the PHP config default
 *
 * @package Kampernet\Magic\Session
 */
class SessionDefault implements SessionInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see SessionInterface::start()
	 */
	public function start() {

		session_start();
	}

}