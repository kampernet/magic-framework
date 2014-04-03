<?php
namespace Kampernet\Magic\Base;

use Kampernet\Magic\Base\Renderer\RenderInterface;

/**
 * an object to model the response
 * the request has a response object
 *
 * the response needs a render interface
 * implementation
 *
 * @package Kampernet\Magic\Base
 */
class Response {

	/**
	 * @var array
	 */
	public $data = array();

	/**
	 * @var array
	 */
	public $messages;

	/**
	 * @var RenderInterface
	 */
	private $renderer;

	/**
	 * @param RenderInterface $render
	 */
	public function setRenderer(RenderInterface $render) {

		$this->renderer = $render;
	}

	/**
	 * @return RenderInterface
	 */
	public function getRenderer() {

		return $this->renderer;
	}

	/**
	 * render the response
	 *
	 * @return string
	 */
	public function render() {

		return $this->renderer->render($this);
	}

	/**
	 * send any headers
	 *
	 * @return void
	 */
	public function sendHeaders() {

		$this->renderer->sendHeaders($this);
	}

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

	/**
	 * redirect
	 *
	 * @param string $url
	 * @return void
	 */
	public function redirect($url) {

		header("Location: " . $url);
		exit;
	}
}