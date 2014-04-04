<?php
namespace Kampernet\Magic\Base\Filter;

use Symfony\Component\HttpFoundation\Request;

/**
 * The base filter class for the decorator filters
 *
 * @package Kampernet\Magic\Base\Filter
 */
abstract class BaseFilter implements FilterChainInterface {

	/**
	 * 
	 * @var Request
	 */
	public $request;

	/**
	 * the constructor can take another filter in it's constructor
	 * thereby chaining the constructors to run their applyFilters
	 *
	 * @param Request $request
	 * @param FilterChainInterface $filter
	 * @return BaseFilter
	 */
	public function __construct(Request &$request, FilterChainInterface $filter = null) {
		$this->request = $request;
		$this->applyFilter();
	}

}