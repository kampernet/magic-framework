<?php
namespace Kampernet\Magic\Renderer;

use Kampernet\Magic\Base\Renderer\RenderInterface;
use Kampernet\Magic\Base\Response;
use Kampernet\Magic\Base\Environment;
use Kampernet\Magic\Base\Request;

/**
 * use PHP templates.  $response is available to the templates
 *
 * @package Kampernet\Magic\Renderer
 */
class PHPRenderer implements RenderInterface {

	/**
	 * (non-PHPdoc)
	 *
	 * @see RenderInterface::sendHeaders()
	 */
	public function sendHeaders(Response $response) {

	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see RenderInterface::render()
	 */
	public function render(Response $response) {

		$templates = Environment::getInstance()->templates;
		$path = realpath(dirname(__FILE__) . "/$templates");
		$template = Request::getInstance()->path[0];

		return $this->getIncludeContents("$path/$template.phtml", $response);

	}

	/**
	 * Note, the $response is not used in this method, but is made available to this method scope to be
	 * referenced inside the included php template
	 *
	 * @param string $filename
	 * @param Response $response
	 * @return bool|string
	 */
	private function getIncludeContents($filename, Response $response) {

		if (is_file($filename)) {
			ob_start();
			include $filename;

			return ob_get_clean();
		}

		return false;
	}
}