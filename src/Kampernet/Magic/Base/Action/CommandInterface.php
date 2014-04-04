<?php
namespace Kampernet\Magic\Base\Action;

use Symfony\Component\HttpFoundation\Request;

/**
 * the command interface
 *
 * @package Kampernet\Magic\Base\Action
 */
interface CommandInterface {

	/**
	 * The command execute method
	 *
	 * @param Request $request
	 * @return boolean
	 */
	public function execute(Request &$request);

	/**
	 * The command undo method
	 */
	public function undo();
}