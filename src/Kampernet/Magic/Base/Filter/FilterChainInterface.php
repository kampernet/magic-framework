<?php
namespace Kampernet\Magic\Base\Filter;

use Kampernet\Magic\Base\AbstractRequest;

/**
 * The Filter Chain Interface
 * which the Base Filter implements
 *
 * @package Kampernet\Magic\Base\Filter
 */
interface FilterChainInterface {

	/**
	 * @param AbstractRequest $request
	 * @param FilterChainInterface $filter
	 */
	public function __construct(AbstractRequest &$request, FilterChainInterface $filter = null);

	/**
	 * do whatever processing you need
	 * referencing the request variable
	 * of the filter.
	 *
	 * @return void
	 */
	public function applyFilter();
}