<?php
namespace Kampernet\Magic\Renderer;

use Kampernet\Magic\Base\Renderer\RenderInterface;
use Kampernet\Magic\Base\Response;
use DOMDocument, XSLTProcessor;
use Kampernet\Magic\Base\Environment;

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

		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see RenderInterface::render()
	 */
	public function render(Response $response) {

		$renderer = new XMLRenderer();
		$xml = $renderer->to_domdocument($response);

		$xsl = new DOMDocument;

		$templates = Environment::getInstance()->templates;
		$path = realpath(dirname(__FILE__) . "/$templates");

		$template = 'index';
		$xsl->load("$path/$template.xsl");

		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl);

		return $proc->transformToXML($xml);
	}
}