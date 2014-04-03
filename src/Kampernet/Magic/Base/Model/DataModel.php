<?php
namespace Kampernet\Magic\Base\Model;

use Kampernet\Magic\Base\Util\AnnotationsParser;
use ReflectionClass, Reflector;

/**
 * The abstract base class for the object that represents data
 *
 * @package lib/base/model
 */
abstract class DataModel {

	/**
	 * @var DataAccessInterface
	 */
	private $dao = null;

	private $className = null;

	private $keys = array();

	protected $_table = null;

	/**
	 * Creates a new DataModel subclass object and
	 * if a DataAccessInterface implementation is
	 * passed in it sets it as the dao property
	 *
	 * You may not want a dao associated with this
	 * DataModel if it is just being used as bean
	 * or model object for the view.
	 *
	 * @param DataAccessInterface $dao
	 */
	public function __construct(DataAccessInterface $dao = null) {

		$this->init(); // a hook to the constructor

		$this->className = get_class($this);

		if (is_null($this->_table)) {
			$info = AnnotationsParser::getAnnotations(new ReflectionClass($this->className));
			if (isset($info['table'])) {
				$this->_table = $info['table'];
			}
		}

		if ($dao) {
			$this->setDAO($dao);
		}
	}

	/**
	 * @param DataAccessInterface $dao
	 * @return void
	 */
	public function setDAO(DataAccessInterface $dao) {

		$this->dao = $dao;
		$this->setKeys();
	}

	/**
	 * @return DataAccessInterface
	 */
	public function getDAO() {

		return $this->dao;
	}

	/**
	 * populates the model object
	 *
	 * @param array $params
	 * @return void
	 */
	public function populate($params = null) {

		if (is_array($params)) {
			foreach ($params as $key => $val) {
				$this->$key = $val;
			}
		}
	}

	/**
	 * saves the model object
	 *
	 * @return DataModel|null
	 */
	public function save() {

		return ($this->dao) ? $this->dao->save($this) : null;
	}

	/**
	 * deletes the model object
	 *
	 * @return DataModel|null
	 */
	public function delete() {

		return ($this->dao) ? $this->dao->delete($this) : null;
	}

	/**
	 * selects based on the model object
	 *
	 * @return DataModelIterator
	 */
	public function fetch() {

		return ($this->dao) ? $this->dao->fetch($this) : null;
	}

	/**
	 * runs custom sql and poplulates a DataModelIterator
	 *
	 * @param string $sql
	 * @param array $params
	 * @param DataModel $o
	 * @return DataModelIterator
	 */
	public function query($sql, $params, DataModel $o = null) {

		return ($this->dao) ? $this->dao->query($sql, $params, $o) : null;
	}

	/**
	 * returns the model classname
	 *
	 * @return string
	 */
	public function getClassName() {

		return $this->className;
	}

	/**
	 * set the model classname
	 *
	 * @param string $classname
	 * @return void
	 */
	public function setClassName($classname) {

		$this->className = $classname;
	}

	/**
	 * @return array
	 */
	public function getKeys() {

		return $this->keys;
	}

	/**
	 * gets the table name
	 *
	 * @return string
	 */
	public function getTableName() {

		if ($this->_table !== null) {
			return $this->_table;
		}

		return $this->getClassName();
	}

	/**
	 * sets the keys based on the database
	 *
	 * @return void
	 */
	private function setKeys() {

		$this->keys = ($this->dao) ? $this->dao->setKeys($this) : null;
	}

	/**
	 * methods available to be overridden / used as hooks
	 */
	public function init() {
	}

	public function onBeforeSave() {
	}

	public function onAfterSave() {
	}

	public function onBeforeDelete() {
	}

	public function onAfterDelete() {
	}

	public function onBeforeFetch() {
	}

	public function onAfterFetch() {
	}

}