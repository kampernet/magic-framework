<?php
namespace Kampernet\Magic\Base\Filter;

use Symfony\Component\HttpFoundation\Request;

/**
 * The Filter Chain Interface
 * which the Base Filter implements
 *
 * @package Kampernet\Magic\Base\Filter
 */
interface FilterChainInterface {

	/**
	 * @param Request $request
	 * @param FilterChainInterface $filter
	 */
	public function __construct(Request &$request, FilterChainInterface $filter = null);

	/**
	 * do whatever processing you need
	 * referencing the request variable
	 * of the filter.
	 *
	 * @return void
	 */
	public function applyFilter();
}