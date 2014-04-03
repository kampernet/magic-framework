<?php
namespace Kampernet\Magic\Filter;

use Kampernet\Magic\Base\Filter\BaseFilter;
use Kampernet\Magic\Base\Session\Session;

/**
 * puts the session object on the request
 *
 * @package Kampernet\Magic\Filter
 */
class SessionFilter extends BaseFilter {

	/**
	 * (non-PHPdoc)
	 *
	 * @see FilterChainInterface::applyFilter()
	 */
	public function applyFilter() {

		$this->request->session = Session::getInstance();
	}
}