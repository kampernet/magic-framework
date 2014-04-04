<?php
namespace Kampernet\Magic\Base\Renderer;

use Kampernet\Magic\Base\ResponseContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * the interface for renderers
 *
 * @package Kampernet\Magic\Base\Renderer
 */
interface RenderInterface {

	/**
	 * render the response
	 *
	 * @param Request $request
	 * @param Response $response
	 * @param ResponseContent $content
	 * @return Response $response
	 */
	public function render(Request $request, Response $response, ResponseContent $content);

	/**
	 * send appropriate headers
	 *
	 * @param Response $response
	 * @return void
	 */
	public function sendHeaders(Response $response);
}