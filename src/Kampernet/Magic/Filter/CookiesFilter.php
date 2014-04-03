<?php
namespace Kampernet\Magic\Filter;

use Kampernet\Magic\Base\Filter\BaseFilter;
use Kampernet\Magic\Base\Cookie\Cookies;

/**
 * set Cookies on the Request
 *
 * @package Kampernet\Magic\Filter
 */
class CookiesFilter extends BaseFilter {

	/**
	 * (non-PHPdoc)
	 *
	 * @see FilterChainInterface::applyFilter()
	 */
	public function applyFilter() {

		if (isset($_COOKIE)) {
			$this->request->cookies = Cookies::getInstance();
		}
	}

}