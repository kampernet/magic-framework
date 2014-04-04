<?php
namespace Kampernet\Magic\Renderer;

use Kampernet\Magic\Base\Renderer\RenderInterface;
use Kampernet\Magic\Base\ResponseContent;
use DOMDocument, XSLTProcessor;
use Kampernet\Magic\Base\Configuration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * works with the XML renderer and uses an XSL template
 *
 * @package Kampernet\Magic\Renderer
 */
class XSLTRenderer implements RenderInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see RenderInterface::sendHeaders()
	 */
	public function sendHeaders(Response $response) {

		$response->headers = new ResponseHeaderBag([
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

		$renderer = new XMLRenderer();
		$xml = $renderer->to_domdocument($content);

		$xsl = new DOMDocument;

		$path = Configuration::getInstance()->templates;

		$template = $request->getBasePath();
		$xsl->load("$path/$template.xsl");

		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl);

		$response->setContent($proc->transformToXML($xml));

		return $response;
	}
}