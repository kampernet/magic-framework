<?php
namespace Kampernet\Magic\Filter;

use Kampernet\Magic\Base\Filter\BaseFilter;

/**
 * render the response
 *
 * @package Kampernet\Magic\Filter
 */
class RenderFilter extends BaseFilter {

	/**
	 * (non-PHPdoc)
	 *
	 * @see FilterChainInterface::applyFilter()
	 */
	public function applyFilter() {

		$this->request->response->render();
	}
}