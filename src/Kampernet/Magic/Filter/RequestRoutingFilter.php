<?php
namespace Kampernet\Magic\Filter;

use Kampernet\Magic\Base\Filter\BaseFilter;
use Kampernet\Magic\Base\Application;
use SimpleXMLElement;

/**
 * Routes the request
 *
 * @package Kampernet\Magic\Filter
 */
class RequestRoutingFilter extends BaseFilter {
	
	/**
	 * (non-PHPdoc)
	 * @see FilterChainInterface::applyFilter()
	 */
	public function applyFilter() {

		/**
		 * @var SimpleXmlElement[] $route
		 */
		$uri = "/".implode("/", $this->request->path);
		$route = Application::getInstance()->actions->xpath("action[@uri='$uri']");

		if (isset($route[0])) { 
			$this->request->path[0] = (string) $route[0]->attributes()->class;
			$this->request->path[1] = (string) $route[0]->attributes()->method;
		} else {
			if (empty($this->request->path)) {
				$this->request->path = array(
					"index", "__default"
				);					
			} else {
				if (!isset($this->request->path[1])) {
					$this->request->path[1] = "__default";
				}
			}
		}
	}
}