<?php
namespace Kampernet\Magic\Base\Event;

use \Kampernet\Magic\Base\Model\Model;

/**
 * the base class for all Listeners
 *
 * @package Kampernet\Magic\Base\Event
 */
abstract class Listener {

	/**
	 * @var Model
	 */
	public $model;

	/**
	 * constructor
	 *
	 * @param Model $model
	 */
	public function __construct(Model $model) {

		$this->model = $model;
	}
}