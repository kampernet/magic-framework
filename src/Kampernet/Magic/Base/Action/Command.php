<?php
namespace Kampernet\Magic\Base\Action;

/**
 * this Command class is just to store the previous command
 * at a superclass level
 *
 * @package Kampernet\Magic\Base\Action
 */
abstract class Command implements CommandInterface {

	/**
	 *
	 * a place to store the previous command for
	 * automatic undo within a chain.
	 *
	 * @var CommandInterface
	 */
	public $previous;
}