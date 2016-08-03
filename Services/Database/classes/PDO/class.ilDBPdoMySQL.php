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

