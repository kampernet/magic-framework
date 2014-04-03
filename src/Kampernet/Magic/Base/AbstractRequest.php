<?php
namespace Kampernet\Magic\Base;

use Kampernet\Magic\Base\Session\Session;

/**
 * abstract request to allow for testing
 * filters that take a request object that should have the
 * public properties listed below
 *
 * @package Kampernet\Magic\Base
 */
abstract class AbstractRequest {

	public $params = array();

	public $path = array();

	/**
	 * @var Session
	 */
	public $session;

	public $cookies = array();

	public $server = array();

	public $do = null;

	/**
	 * @var Response
	 */
	public $response = null;

}