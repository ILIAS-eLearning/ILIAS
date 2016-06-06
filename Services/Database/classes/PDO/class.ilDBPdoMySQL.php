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
		return true;
	}


	/**
	 * @param bool $return_false_for_error
	 * @return bool
	 * @throws \Exception
	 */
	public function connect($return_false_for_error = false) {
		if (!$this->getDSN()) {
			$this->generateDSN();
		}
		try {
			$this->pdo = new PDO($this->getDSN(), $this->getUsername(), $this->getPassword(), $this->additional_attributes);
			$this->manager = new ilDBPdoManager($this->pdo, $this);
			$this->reverse = new ilDBPdoReverse($this->pdo, $this);
			$this->field_definition = new ilDBPdoMySQLFieldDefinition($this);
		} catch (Exception $e) {
			$this->error_code = $e->getCode();
			if ($return_false_for_error) {
				return false;
			}
			throw $e;
		}

		return ($this->pdo->errorCode() == PDO::ERR_NONE);
	}


	/**
	 * @return bool
	 */
	public function supportsEngineMigration() {
		return true;
	}


	/**
	 * @param string $engine
	 * @return array
	 */
	public function migrateAllTablesToEngine($engine = ilDBConstants::ENGINE_INNODB) {
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
}

