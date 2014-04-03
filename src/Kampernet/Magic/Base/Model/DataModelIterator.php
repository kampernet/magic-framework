<?php
namespace Kampernet\Magic\Base\Model;

use Iterator, Countable;

/**
 * a collection of DataModel objects
 *
 * @package lib/base/model
 */
class DataModelIterator implements Iterator, Countable {

	private $index = 0;

	public $items = array();

	/**
	 * constructor
	 */
	public function __construct() {

		$this->index = 0;
	}

	/**
	 * reset the index and return the first element
	 *
	 * @see Iterator::first
	 * @return mixed|null
	 */
	public function first() {

		$this->rewind();

		return $this->current();
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::rewind()
	 */
	public function rewind() {

		$this->index = 0;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::current()
	 */
	public function current() {

		return (isset($this->items[$this->index])) ? $this->items[$this->index] : null;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::key()
	 */
	public function key() {

		return $this->index;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::next()
	 */
	public function next() {

		++$this->index;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Iterator::valid()
	 */
	public function valid() {

		return isset($this->items[$this->index]);
	}

	/**
	 * add the DataModel object to the collection
	 *
	 * @param DataModel $o
	 */
	public function add(DataModel $o) {

		array_push($this->items, $o);
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see Countable::count()
	 */
	public function count() {

		return count($this->items);
	}
}