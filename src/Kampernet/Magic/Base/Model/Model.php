<?php
namespace Kampernet\Magic\Base\Model;

use Kampernet\Magic\Base\Exception\NoSuchMethodException;

/**
 * abstract base model class
 *
 * @package lib/base
 */
abstract class Model extends DataModel {

	private $originalClassName;

	/**
	 * @return DataModelIterator
	 */
	public function __default() {

		return $this->fetch();
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @throws NoSuchMethodException
	 * @return mixed
	 */
	public function __call($name, $args) {

		if (!method_exists($this, $name)) {
			throw new NoSuchMethodException('The class ' . get_class($this) . ' does not support the method ' . $name);
		} else {
			return call_user_func_array(array($this, $name), $args);
		}
	}

	/**
	 * if referenced as a string
	 */
	public function __toString() {

		return $this->__default();
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see DataModel::init()
	 */
	public function init($properties = []) {

		$this->populate($properties);
	}

	/**
	 * @param string $className
	 */
	public function setOriginalClassName($className) {

		$this->originalClassName = $className;
	}

	/**
	 * @return string
	 */
	public function getOriginalClassName() {

		return $this->originalClassName;
	}

}