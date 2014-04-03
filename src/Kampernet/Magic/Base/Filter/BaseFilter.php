<?php
namespace Kampernet\Magic\Base\Filter;

use Kampernet\Magic\Base\AbstractRequest;

/**
 * The base filter class for the decorator filters
 *
 * @package Kampernet\Magic\Base\Filter
 */
abstract class BaseFilter implements FilterChainInterface {

	/**
	 * 
	 * @var AbstractRequest
	 */
	public $request;

	/**
	 * the constructor can take another filter in it's constructor
	 * thereby chaining the constructors to run their applyFilters
	 *
	 * @param AbstractRequest $request
	 * @param FilterChainInterface $filter
	 * @return BaseFilter
	 */
	public function __construct(AbstractRequest &$request, FilterChainInterface $filter = null) {
		$this->request = $request;
		$this->applyFilter();
	}

}