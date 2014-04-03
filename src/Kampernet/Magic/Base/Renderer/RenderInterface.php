<?php
namespace Kampernet\Magic\Base\Renderer;

use Kampernet\Magic\Base\Response;

/**
 * the interface for renderers
 *
 * @package Kampernet\Magic\Base\Renderer
 */
interface RenderInterface {

	/**
	 * render the response
	 *
	 * @param Response $response
	 * @return string
	 */
	public function render(Response $response);

	/**
	 * send appropriate headers
	 *
	 * @param Response $response
	 * @return void
	 */
	public function sendHeaders(Response $response);
}