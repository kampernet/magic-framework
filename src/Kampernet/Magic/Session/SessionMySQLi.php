<?php
namespace Kampernet\Magic\Session;

use Kampernet\Magic\Base\Session\SessionInterface;
use mysqli;

/**
 * use the database for sessions, preferable if you're 
 * relying on session data for your app and you are 
 * using a distributed environment
 * 
 * @package lib/sessions
 */
class SessionMySQLi implements SessionInterface {
	
	/**
	 * this object needs it's own db connection
	 * because of when the session save handlers
	 * get called
	 *
	 * @var MySQLi
	 */
	private $connection;
	
	/**
	 * (non-PHPdoc)
	 * @see SessionInterface::start()
	 */
	public function start() {
		
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);

		$this->connection = new MySQLi(DB_TYPE_MASTER, DB_USER, DB_PASSWORD, DB_NAME);
		if (!$this->connection->connect_errno) {
			session_start();
		}

	}

	/**
	 * Open the session
	 * @return bool
	 */
	public function open() {
		return true;
	}
	
	/**
	 * Close the session
	 * @return bool
	 */
	public function close() {
		return true;
	}
	
	/**
	 * Read the session
	 * @param int session id
	 * @return string string of the session
	 */
	public function read($id) {
	
		$data = '';
		$sql = "SELECT data FROM ".DBTABLE_PHP_SESSIONS." WHERE session_id = ?";
	
		if (!$stmt = $this->connection->prepare($sql)) {
//			send_error_report("Prepare failed: (" . $this->connection->errno . ") " . $this->connection->error);
		}
	
		if (!$stmt->bind_param("s", $id)) {
//			send_error_report("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	
		if (!$stmt->bind_result($data)) {
//			send_error_report("Binding results failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	
		if (!$stmt->execute()) {
//			send_error_report("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	
		$stmt->fetch();
	
		return $data;
	}
	
	/**
	 * Write the session
	 *
	 * @param int $id
	 * @param string $data
	 * @return mixed
	 */
	public function write($id, $data) {
	
		$sql = "INSERT INTO ".DBTABLE_PHP_SESSIONS." (data, date_touched, session_id) 
				VALUES (?, ?, ?) 
				ON DUPLICATE KEY 
				UPDATE data = VALUES(data), 
				date_touched = VALUES(date_touched)";
	
		if (!($stmt = $this->connection->prepare($sql))) {
			send_error_report("Prepare failed: (" . $this->connection->errno . ") " . $this->connection->error);
		}
	
		$now = time();
		if (!$stmt->bind_param("sis", $data, $now, $id)) {
			send_error_report("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	
		if (!$stmt->execute()) {
			send_error_report("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	
		return $data;
	}
	
	/**
	 * Destoroy the session
	 * @param int session id
	 * @return bool
	 */
	public function destroy($id) {
	
		$sql = "DELETE FROM ".DBTABLE_PHP_SESSIONS." WHERE session_id = ?";
	
		if (!($stmt = $this->connection->prepare($sql))) {
			send_error_report("Prepare failed: (" . $this->connection->errno . ") " . $this->connection->error);
		}
	
		if (!$stmt->bind_param("s", $id)) {
			send_error_report("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	
		if (!$stmt->execute()) {
			send_error_report("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	
		return true;
	}
	
	/**
	 * Garbage Collector
	 * @param int life time (sec.)
	 * @return bool
	 * @see session.gc_divisor      100
	 * @see session.gc_maxlifetime 1440
	 * @see session.gc_probability    1
	 * @usage execution rate 1/100
	 *        (session.gc_probability/session.gc_divisor)
	 */
	public function gc($max) {
	
		$now = time();
		$expiry = ($now - $max);
	
		$sql = "DELETE FROM ".DBTABLE_PHP_SESSIONS." WHERE date_touched < ?";
	
		if (!($stmt = $this->connection->prepare($sql))) {
			send_error_report("Prepare failed: (" . $this->connection->errno . ") " . $this->connection->error);
		}
	
		if (!$stmt->bind_param("i", $expiry)) {
			send_error_report("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	
		if (!$stmt->execute()) {
			send_error_report("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
		}
	
		return true;
	
	}
	
}