<?php

require_once 'Services/Database/interfaces/interface.ilDBStatement.php';

/**
 * Class ilPDOStatement is a Wrapper Class for PDOStatement
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilPDOStatement implements ilDBStatement {

	/**
	 * @var PDOStatement
	 */
	protected $pdo;


	/**
	 * @param $pdoStatement PDOStatement The PDO Statement to be wrapped.
	 */
	public function __construct($pdoStatement) {
		$this->pdo = $pdoStatement;
	}


	/**
	 * @param int $fetchMode
	 * @return mixed
	 * @throws ilDatabaseException
	 */
	public function fetchRow($fetchMode = ilDBConstants::FETCHMODE_ASSOC) {
		if ($fetchMode == ilDBConstants::FETCHMODE_ASSOC) {
			return $this->pdo->fetch(PDO::FETCH_ASSOC);
		} elseif ($fetchMode == ilDBConstants::FETCHMODE_OBJECT) {
			return $this->pdo->fetch(PDO::FETCH_OBJ);
		} else {
			throw new ilDatabaseException("No valid fetch mode given, choose ilDBConstants::FETCHMODE_ASSOC or ilDBConstants::FETCHMODE_OBJECT");
		}
	}


	/**
	 * @param int $fetchMode
	 * @return mixed|void
	 */
	function fetch($fetchMode = ilDBConstants::FETCHMODE_ASSOC) {
		return $this->fetchRow($fetchMode);
	}


	/**
	 * Pdo allows for a manual closing of the cursor.
	 */
	public function closeCursor() {
		$this->pdo->closeCursor();
	}


	/**
	 * @return int
	 */
	function rowCount() {
		return $this->pdo->rowCount();
	}


	/**
	 * @return stdClass
	 */
	function fetchObject() {
		return $this->fetch(ilDBConstants::FETCHMODE_OBJECT);
	}


	/**
	 * @return int
	 */
	function numRows() {
		return $this->pdo->rowCount();
	}
}