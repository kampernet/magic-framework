<?php
namespace Kampernet\Magic\Renderer;

use Kampernet\Magic\Base\Configuration;
use Kampernet\Magic\Base\Renderer\RenderInterface;
use Kampernet\Magic\Base\ResponseContent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * use PHP templates.  $response is available to the templates
 *
 * @package Kampernet\Magic\Renderer
 */
class PHPRenderer implements RenderInterface {

	/**
	 * send appropriate headers
	 *
	 * @param Response $response
	 * @return void
	 */
	public function sendHeaders(Response $response) {

		$response->sendHeaders();
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see RenderInterface::render()
	 */
	public function render(Request $request, Response $response, ResponseContent $content) {

		$path = Configuration::getInstance()->templates;
		$template = $request->getBasePath();

		$response->setContent($this->getIncludeContents("$path/$template.phtml", $content));

		return $response;
	}

	/**
	 * Note, the $response is not used in this method, but is made available to this method scope to be
	 * referenced inside the included php template
	 *
	 * @param string $filename
	 * @param ResponseContent $response
	 * @return bool|string
	 */
	private function getIncludeContents($filename, ResponseContent $response) {

		if (is_file($filename)) {
			ob_start();
			include $filename;

			return ob_get_clean();
		}

		return false;
	}

}