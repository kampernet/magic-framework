<?php
namespace Kampernet\Magic\Filter;

use Kampernet\Magic\Base\Filter\BaseFilter;
use Kampernet\Magic\Base\Renderer\RenderInterface;
use Kampernet\Magic\Base\Environment;
use Kampernet\Magic\Renderer\JSONRenderer;
use Kampernet\Magic\Renderer\XMLRenderer;

/**
 * sets what renderer to use based on the url of the request
 *
 * @package Kampernet\Magic\Filter
 */
class SetRendererFilter extends BaseFilter {

	/**
	 * Sets the renderer for the request's response
	 *
	 * @see FilterChainInterface::applyFilter()
	 */
	public function applyFilter() {

		$last = end($this->request->path);

		if (isset($last) && strstr($last, ".")) {
			$info = explode(".", $last);
			$replace = $info[0];
			$extension = $info[1];
			switch ($extension) {
				case "json":
					$this->request->response->setRenderer(new JSONRenderer());
					$this->request->path[count($this->request->path) - 1] = $replace;
					break;
				case "xml":
					$this->request->response->setRenderer(new XMLRenderer());
					$this->request->path[count($this->request->path) - 1] = $replace;
					break;
				default:
					$this->request->response->setRenderer($this->lookupRenderer());
					break;
			}
		} else {
			$this->request->response->setRenderer($this->lookupRenderer());
		}
	}

	/**
	 * @return RenderInterface
	 */
	private function lookupRenderer() {

		$renderClass = (string) Environment::getInstance()->renderer;
		if (trim($renderClass) == '') {
			$renderClass = "PHPRenderer";
		}

		return new $renderClass();
	}
}