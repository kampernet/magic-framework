<?php
namespace Kampernet\Magic\Base;

/**
 * an object to model the response
 * the request has a response object
 *
 * the response needs a render interface
 * implementation
 *
 * @package Kampernet\Magic\Base
 */
class ResponseContent {

	/**
	 * @var array
	 */
	public $data = array();

	/**
	 * @var array
	 */
	public $messages;

	/**
	 * add a message to the response
	 *
	 * @param string $message
	 * @param string $type
	 * @return void
	 */
	public function addMessage($message, $type) {

		if (!isset($this->messages)) {
			$this->messages = array();
		}
		array_push($this->messages, array(
			"message" => array(
				"message" => $message,
				"type" => $type
			)
		));
	}
}