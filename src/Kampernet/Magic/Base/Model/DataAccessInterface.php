<?php
namespace Kampernet\Magic\Base\Model;

/**
 * The Interface for The Data Access Layer that the model object uses
 * to access the database
 *
 * @package Kampernet\Magic\Base\Model
 */
interface DataAccessInterface {

	/**
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 */
	public function __construct($host, $user, $pass, $db);

	/**
	 * @param DataModel $o
	 * @return DataModel
	 */
	public function save(DataModel $o);

	/**
	 * @param DataModel $o
	 * @return DataModel
	 */
	public function delete(DataModel $o);

	/**
	 * @param DataModel $o
	 * @return DataModelIterator
	 */
	public function fetch(DataModel $o);

	/**
	 * @param string $sql
	 * @param array $params
	 * @param DataModel $o
	 * @return mixed array | DataModelIterator
	 */
	public function query($sql, $params = null, DataModel $o = null);

	/**
	 * @param DataModel $o
	 * @return array
	 */
	public function setKeys(DataModel $o);

	/**
	 * @return string
	 */
	public function quote();

	/**
	 * @param string $value
	 * @return string
	 */
	public function escape($value);
}