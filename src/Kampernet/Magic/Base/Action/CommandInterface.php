<?php
namespace Kampernet\Magic\Base\Action;

use Kampernet\Magic\Base\AbstractRequest;

/**
 * the command interface
 *
 * @package Kampernet\Magic\Base\Action
 */
interface CommandInterface {

	/**
	 * The command execute method
	 *
	 * @param \Kampernet\Magic\Base\AbstractRequest $request
	 * @return boolean
	 */
	public function execute(AbstractRequest &$request);

	/**
	 * The command undo method
	 */
	public function undo();
}