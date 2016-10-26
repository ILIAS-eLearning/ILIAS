<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once('./Services/Database/classes/PDO/FieldDefinition/class.ilDBPdoMySQLFieldDefinition.php');
require_once('class.ilDBPdo.php');

/**
 * Class ilDBPdoMySQL
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilDBPdoMySQL extends ilDBPdo implements ilDBInterface {

	/**
	 * @return bool
	 */
	public function supportsTransactions() {
		return false;
	}


	public function initHelpers() {
		$this->manager = new ilDBPdoManager($this->pdo, $this);
		$this->reverse = new ilDBPdoReverse($this->pdo, $this);
		$this->field_definition = new ilDBPdoMySQLFieldDefinition($this);
	}


	/**
	 * @return bool
	 */
	public function supportsEngineMigration() {
		return true;
	}


	/**
	 * @return array
	 */
	protected function getAdditionalAttributes() {
		return array(
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
			PDO::ATTR_TIMEOUT                  => 300 * 60,
		);
	}


	/**
	 * @param string $engine
	 * @return array
	 */
	public function migrateAllTablesToEngine($engine = ilDBConstants::MYSQL_ENGINE_INNODB) {
		$engines = $this->queryCol('SHOW ENGINES');
		if (!in_array($engine, $engines)) {
			return array();
		}

		$errors = array();
		foreach ($this->listTables() as $table) {
			try {
				$this->pdo->exec("ALTER TABLE {$table} ENGINE={$engine}");
			} catch (Exception $e) {
				$errors[$table] = $e->getMessage();
			}
		}

		return $errors;
	}


	/**
	 * @param string $table_name
	 * @return int
	 */
	public function nextId($table_name) {
		$sequence_name = $this->quoteIdentifier($this->getSequenceName($table_name), true);
		$seqcol_name = 'sequence';
		$query = "INSERT INTO $sequence_name ($seqcol_name) VALUES (NULL)";
		try {
			$this->pdo->exec($query);
		} catch (PDOException $e) {
			// no such table check
		}

		$result = $this->query('SELECT LAST_INSERT_ID() AS next');
		$value = $result->fetchObject()->next;

		if (is_numeric($value)) {
			$query = "DELETE FROM $sequence_name WHERE $seqcol_name < $value";
			$this->pdo->exec($query);
		}

		return $value;
	}
}

