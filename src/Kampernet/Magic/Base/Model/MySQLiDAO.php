<?php
namespace Kampernet\Magic\Base\Model;

use mysqli;
use ReflectionClass, Exception, ReflectionProperty;
use Kampernet\Magic\Base\Util\AnnotationsParser;

/**
 * This is the mysqli implementation of the DataAccessInterface
 * currently the only implementation
 *
 * @package lib/base/model
 */
class MySQLiDAO implements DataAccessInterface {

	/**
	 * @var mysqli
	 */
	private $conn;

	/**
	 * connects to the mysql database using the mysqli functions provided in php
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 */
	public function __construct($host, $user, $pass, $db) {

		$this->connect($host, $user, $pass, $db);
	}

	/**
	 * saves the instance of the object to the database
	 *
	 * @param DataModel $o
	 * @throws Exception
	 * @return DataModel
	 */
	public function save(DataModel $o) {

		$o->onBeforeSave();

		$columns = array();
		$values = array();
		$keys = $o->getKeys();

		$class = new ReflectionClass($o);
		$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($properties as $property) {
			if ($property->getDeclaringClass() == $class) {
				$propertyName = $property->getName();
				if ((isset($keys["PRI"])) &&
					(($propertyName == $keys["PRI"]["Field"]) && ($keys["PRI"]["auto_increment"])) &&
					(!$o->$propertyName)
				) {
					// property is an auto incremented primary key and is not set, ignore it
				} else {
					if (isset($o->$propertyName) && !is_object($o->$propertyName)) {
						array_push($columns, $propertyName);
						array_push($values, trim(stripslashes((string) $o->$propertyName)));
					}
				}
			}
		}

		if (count($columns) > 0) {
			$sql = "INSERT INTO {$this->quote()}{$o->getTableName()}{$this->quote()} (";
			$sql .= $this->quote() . implode("{$this->quote()}, {$this->quote()}", $columns);
			$sql .= $this->quote() . ") VALUES (";
			for ($i = 0; $i < count($columns); $i++) {
				$sql .= "?, ";
			}
			$sql = substr($sql, 0, -2) . ") ON DUPLICATE KEY UPDATE ";
			foreach ($columns as $column) {
				$sql .= "{$this->quote()}$column{$this->quote()} = VALUES({$this->quote()}$column{$this->quote()}), ";
			}
			$sql = substr($sql, 0, -2);

			if (!$stmt = $this->conn->prepare($sql)) {
				throw new Exception('Please check your sql statement : unable to prepare');
			}

			$stmt_params = array();
			foreach ($values as $k => &$param) {
				$stmt_params[$k] = & $param;
			}
			array_unshift($stmt_params, str_repeat('s', count($values)));
			array_unshift($stmt_params, $stmt);

			call_user_func_array('mysqli_stmt_bind_param', $stmt_params);

			if ($stmt->execute() === false) {
				throw new Exception(mysqli_stmt_error($stmt));
			} else {
				if ($keys["PRI"]["auto_increment"]) {
					$primaryKey = $keys["PRI"]["Field"];
					if (!isset($o->$primaryKey)) {
						$o->$primaryKey = $stmt->insert_id;
					}
				}
				$o->onAfterSave();
			}
		}

		return $o;
	}

	/**
	 * performs a delete on the table based on the datamodel object passed in
	 *
	 * @param DataModel $o
	 * @throws Exception
	 * @return DataModel
	 */
	public function delete(DataModel $o) {

		$o->onBeforeDelete();

		$columns = array();
		$values = array();
		$keys = $o->getKeys();

		$class = new ReflectionClass($o);
		$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);
		foreach ($properties as $property) {
			if ($property->getDeclaringClass() == $class) {
				$propertyName = $property->getName();
				if (isset($o->$propertyName)) {
					array_push($columns, $propertyName);
					array_push($values, (string) $o->$propertyName);
				}
			}
		}

		$delete = "DELETE FROM {$this->quote()}{$o->getTableName()}{$this->quote()}";
		if (count($columns) == 0) {
			$values = null;
		} else {
			$where = "WHERE {$this->quote()}" . implode("{$this->quote()} = ? AND {$this->quote()}", $columns) . "{$this->quote()} = ?";
		}

		$sql = "$delete $where";

		if (!$stmt = $this->conn->prepare($sql)) {
			throw new Exception('Please check your sql statement : unable to prepare');
		}

		$stmt_params = array();
		foreach ($values as $k => &$param) {
			$stmt_params[$k] = & $param;
		}
		array_unshift($stmt_params, str_repeat('s', count($values)));
		array_unshift($stmt_params, $stmt);

		call_user_func_array('mysqli_stmt_bind_param', $stmt_params);

		if ($stmt->execute() === false) {
			throw new Exception(mysqli_stmt_error($stmt));
		} else {
			$o->onAfterDelete();
		}

		return $o;
	}

	/**
	 * fetches a collection of the datamodel from the db
	 *
	 * @param DataModel $o
	 * @return DataModelIterator
	 */
	public function fetch(DataModel $o) {

		$columns = array();
		$values = array();
		$where = "";
		$objectProperties = array();

		$class = new ReflectionClass($o);
		$properties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

		foreach ($properties as $property) {
			if ($property->getDeclaringClass() == $class) {
				$propertyName = $property->getName();
				if (isset($o->$propertyName)) {
					if (!is_object($o->$propertyName)) {
						array_push($columns, $propertyName);
						array_push($values, $o->$propertyName);
					} else {
						$objectProperties [] = $property;
					}
				}
			}
		}

		$select = "SELECT * FROM {$this->quote()}{$o->getTableName()}{$this->quote()}";
		if (count($columns) == 0) {
			$where = "LIMIT 1000";
			$values = null;
		} else {
			$where = "WHERE {$this->quote()}" . implode("{$this->quote()} = ? AND {$this->quote()}", $columns) . "{$this->quote()} = ?";
		}

		$sql = "$select $where";
		$results = $this->query($sql, $values, $o);

		foreach ($objectProperties as $property) {
			foreach ($results as $o) {
				$propertyName = $property->getName();
				$info = AnnotationsParser::getAnnotations($property);
				if (array_key_exists('model', $info)) {
					$keys = $this->setKeys($o);
					foreach ($keys['PRI'] as $key) {
						if (property_exists($o->$propertyName, $key) && isset($o->$key)) {
							$o->$propertyName->$key = $o->$key;
						}
					}
					if ($a = $o->$propertyName->fetch()) {
						if ($z = $a->current()) {
							$o->$propertyName = $z;
						}
					}
				}
			}
		}

		$results->rewind();

		return $results;
	}

	/**
	 * populates values from associative array passed in
	 * onto the DataModel object properties.  No Reflection
	 * is used in this function, and anything in the array
	 * becomes an assigned property of the object.
	 *
	 * @param DataModel $o
	 * @param array $rec
	 * @return DataModel
	 */
	public function populate(DataModel $o, array $rec) {

		$obj = clone($o);
		foreach ($rec as $key => $val) {
			$obj->onBeforeFetch();
			$obj->$key = $val;
			$obj->onAfterFetch();
		}

		return $obj;
	}

	/**
	 * if params are passed in, as an array of values, sql gets executed as
	 * a prepared statement.  if a DataModel is passed in
	 * it returns a DataModelIterator of DataModel objects.
	 * otherwise, it returns the results of the query as an
	 * associative array
	 *
	 * @param string $sql
	 * @param array $params
	 * @param DataModel $o
	 * @throws Exception
	 * @return mixed array or DataModelIterator
	 */
	public function query($sql, $params = null, DataModel $o = null) {

		$results = array();

		if (!$params) {
			if ($result = $this->conn->query($sql)) {
				if ($result !== true) {
					while ($row = $result->fetch_assoc()) {
						array_push($results, $row);
					}
					$result->close();
				}
			}
		} else {
			if (!$stmt = $this->conn->prepare($sql)) {
				throw new Exception('Please check your sql statement : unable to prepare');
			}
			$stmt_params = array();
			foreach ($params as $k => &$param) {
				$stmt_params[$k] = & $param;
			}
			array_unshift($stmt_params, str_repeat('s', count($params)));
			array_unshift($stmt_params, $stmt);

			call_user_func_array('mysqli_stmt_bind_param', $stmt_params);

			if ($stmt->execute() === false) {
				throw new Exception(mysqli_stmt_error($stmt));
			}

			$result = $stmt->result_metadata();
			$fields = array();
			while ($field = mysqli_fetch_field($result)) {
				$name = $field->name;
				$fields[$name] = & $$name;
			}
			array_unshift($fields, $stmt);
			call_user_func_array('mysqli_stmt_bind_result', $fields);

			array_shift($fields);
			while (mysqli_stmt_fetch($stmt)) {
				$temp = array();
				foreach ($fields as $key => $val) {
					$temp[$key] = $val;
				}
				array_push($results, $temp);
			}

			mysqli_free_result($result);
			mysqli_stmt_close($stmt);
		}

		if ($o) {
			// populate referenced data model object
			$dmc = new DataModelIterator();
			foreach ($results as $rec) {
				$dmc->add($this->populate($o, $rec));
			}

			return $dmc;
		} else {
			// return as associative array 
			return $results;
		}
	}

	/**
	 * sets the keys array of the object.  this is based
	 * on the table with the same name of the class existing
	 * in the database connection
	 *
	 * @param DataModel $o
	 * @return array
	 */
	public function setKeys(DataModel $o) {

		$keys = array();
		if ($result = $this->query("DESCRIBE {$this->quote()}{$o->getTableName()}{$this->quote()}")) {
			foreach ($result as $rec) {
				if ($rec["Key"] == "PRI") {
					$keys[$rec["Key"]] = array("Field" => $rec["Field"], "auto_increment" => ($rec["Extra"] == "auto_increment") ? true : false);
				}
			}
		}

		// now get the unique indexes
		if ($result = $this->query("SHOW INDEX FROM {$this->quote()}{$o->getTableName()}{$this->quote()} WHERE Non_unique = FALSE AND Key_name != 'PRIMARY'")) {
			foreach ($result as $rec) {
				if (array_key_exists($rec["Key_name"], $keys)) {
					array_push($keys[$rec["Key_name"]], $rec["Column_name"]);
				} else {
					$keys[$rec["Key_name"]] = array($rec["Column_name"]);
				}
			}
		}

		return $keys;
	}

	/**
	 * the quote for mysql
	 *
	 * @return string
	 */
	public function quote() {

		return "`";
	}

	/**
	 * the escaping for mysqli
	 *
	 * @param string $value
	 * @return string
	 */
	public function escape($value) {

		return "?";
	}

	/**
	 * connects to the database
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 * @return void
	 */
	private function connect($host, $user, $pass, $db) {

		if (!$this->conn) {

			$cm = ConnectionManager::getInstance();
			if (!$this->conn = $cm->getConnection($host, $user, $pass, $db)) {
				$this->conn = new mysqli($host, $user, $pass, $db);
				$cm->addConnection($host, $user, $pass, $db, $this->conn);
			}

			if (mysqli_connect_errno()) {
				printf("Connect failed: %s\n", mysqli_connect_error());
				exit();
			}
		}
	}

	/**
	 * disconnects from the database
	 *
	 * @return void
	 */
	private function disconnect() {

		$cm = ConnectionManager::getInstance();
		unset($cm);
	}

	/**
	 * ensure disconnection
	 *
	 * @return void
	 */
	public function __destruct() {

		$this->disconnect();
	}
}