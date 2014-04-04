<?php
namespace Kampernet\Magic\Base\Action;

use Iterator;
use Symfony\Component\HttpFoundation\Request;

/**
 * a collection of actions
 *
 * @package Kampernet\Magic\Base\Action
 */
class ActionChain implements CommandInterface, Iterator {

	private $index = 0;

	private $items = array();

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::rewind()
	 */
	final public function rewind() {

		$this->index = 0;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::current()
	 */
	final public function current() {

		return $this->items[$this->index];
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::key()
	 */
	final public function key() {

		return $this->index;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::next()
	 */
	final public function next() {

		++$this->index;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::valid()
	 */
	final public function valid() {

		return isset($this->items[$this->index]);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see CommandInterface::execute()
	 */
	final public function execute(Request &$request) {

		/**
		 * @var CommandInterface $command
		 */
		$previous = null;
		foreach ($this as $command) {
			$command->previous = $previous;
			if ($command->execute($request)) {
				$previous = $command;
			} else {
				do {
					$command = $command->undo();
				} while ($command);

				return false;
			}
		}

		return true;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see CommandInterface::undo()
	 */
	final public function undo() {

		$previous = null;
		/**
		 * @var CommandInterface $command
		 */
		foreach ($this as $command) {
			$previous = $command->undo();
		}

		return $previous;
	}

	/**
	 * add a command to this chain
	 *
	 * @param CommandInterface $action
	 */
	final public function add(CommandInterface $action) {

		array_push($this->items, $action);
	}
}