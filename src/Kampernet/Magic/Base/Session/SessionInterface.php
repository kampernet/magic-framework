<?php
namespace Kampernet\Magic\Base\Session;

/**
 * the interface for session handlers
 *
 * @package Kampernet\Magic\Base\Session
 */
interface SessionInterface {

	/**
	 * start the session
	 */
	public function start();
}