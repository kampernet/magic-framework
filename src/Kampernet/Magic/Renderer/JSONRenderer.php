<?php
namespace Kampernet\Magic\Renderer;

use Kampernet\Magic\Base\Renderer\RenderInterface;
use Kampernet\Magic\Base\ResponseContent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * echo the response as JSON
 *
 * @package Kampernet\Magic\Renderer
 */
class JSONRenderer implements RenderInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see RenderInterface::sendHeaders()
	 */
	public function sendHeaders(Response $response) {

		$response->headers = new ResponseHeaderBag([
			"Content-Type" => "application/json",
			"Cache-Control" => "no-cache, must-revalidate",
			"Expires" => "Sat, 26 Jul 1997 05:00:00 GMT"
		]);

		$response->sendHeaders();
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see RenderInterface::render()
	 */
	public function render(Request $request, Response $response, ResponseContent $content) {

		$response->setContent(json_encode($content));

		return $response;
	}
}