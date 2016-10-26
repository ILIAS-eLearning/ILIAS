<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * This class gives all kind of DB information using the MDB2 manager
 * and reverse module.
 *
 * @author     Alex Killing <alex.killing@gmx.de>
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ingroup    ServicesDatabase
 *
 * @deprecated Use global ilDB only. If something is missing there, please contact fs@studer-raimann.ch
 */
class ilDBAnalyzer {

	/**
	 * @var \ilDBManager
	 */
	protected $manager;
	/**
	 * @var \ilDBReverse
	 */
	protected $reverse;
	/**
	 * @var \ilDB|\ilDBInterface
	 */
	protected $il_db;
	/**
	 * @var array
	 */
	protected $allowed_attributes;


	/**
	 * ilDBAnalyzer constructor.
	 *
	 * @param \ilDBInterface $ilDBInterface
	 *
	 * @deprecated Use global ilDB only. If something is missing there, please contact fs@studer-raimann.ch
	 */
	public function __construct(ilDBInterface $ilDBInterface = null) {
		if (!$ilDBInterface instanceof ilDBInterface) {
			global $ilDB;
			$ilDBInterface = $ilDB;
		}

		$this->manager = $ilDBInterface->loadModule(ilDBConstants::MODULE_MANAGER);
		$this->reverse = $ilDBInterface->loadModule(ilDBConstants::MODULE_REVERSE);
		$this->il_db = $ilDBInterface;
		$this->allowed_attributes = $ilDBInterface->getAllowedAttributes();
	}


	/**
	 * Get field information of a table.
	 *
	 * @param    string        table name
	 * @return    array        field information array
	 */
	public function getFieldInformation($a_table, $a_remove_not_allowed_attributes = false) {
		$fields = $this->manager->listTableFields($a_table);
		$inf = array();
		foreach ($fields as $field) {
			$rdef = $this->reverse->getTableFieldDefinition($a_table, $field);
			// is this possible?
			if ($rdef["type"] != $rdef["mdb2type"]) {
				throw new ilDatabaseException("ilDBAnalyzer::getFielInformation: Found type != mdb2type: $a_table, $field");
			}

			$best_alt = $this->getBestDefinitionAlternative($rdef);

			// collect other alternatives
			reset($rdef);
			$alt_types = "";
			foreach ($rdef as $k => $rd) {
				if ($k != $best_alt) {
					$alt_types .= $rdef[$k]["type"] . $rdef[$k]["length"] . " ";
				}
			}

			$inf[$field] = array(
				"notnull"       => $rdef[$best_alt]["notnull"],
				"nativetype"    => $rdef[$best_alt]["nativetype"],
				"length"        => $rdef[$best_alt]["length"],
				"unsigned"      => $rdef[$best_alt]["unsigned"],
				"default"       => $rdef[$best_alt]["default"],
				"fixed"         => $rdef[$best_alt]["fixed"],
				"autoincrement" => $rdef[$best_alt]["autoincrement"],
				"type"          => $rdef[$best_alt]["type"],
				"alt_types"     => $alt_types,
			);

			if ($a_remove_not_allowed_attributes) {
				foreach ($inf[$field] as $k => $v) {
					if ($k != "type" && !in_array($k, $this->allowed_attributes[$inf[$field]["type"]])) {
						unset($inf[$field][$k]);
					}
				}
			}
		}

		return $inf;
	}


	/**
	 * @param $a_def
	 * @return int|string
	 */
	public function getBestDefinitionAlternative($a_def) {
		// determine which type to choose
		$car = array(
			"boolean"   => 10,
			"integer"   => 20,
			"decimal"   => 30,
			"float"     => 40,
			"date"      => 50,
			"time"      => 60,
			"timestamp" => 70,
			"text"      => 80,
			"clob"      => 90,
			"blob"      => 100,
		);

		$cur_car = 0;
		$best_alt = 0;    // best alternatice
		foreach ($a_def as $k => $rd) {
			if ($car[$rd["type"]] > $cur_car) {
				$cur_car = $car[$rd["type"]];
				$best_alt = $k;
			}
		}

		return $best_alt;
	}


	/**
	 * Gets the auto increment field of a table.
	 * This should be used on ILIAS 3.10.x "MySQL" tables only.
	 *
	 * @param    string        table name
	 * @return    string        name of autoincrement field
	 */
	public function getAutoIncrementField($a_table) {
		$fields = $this->manager->listTableFields($a_table);
		$inf = array();

		foreach ($fields as $field) {
			$rdef = $this->reverse->getTableFieldDefinition($a_table, $field);
			if ($rdef[0]["autoincrement"]) {
				return $field;
			}
		}

		return false;
	}


	/**
	 * Get primary key of a table
	 *
	 * @param    string        table name
	 * @return    array        primary key information array
	 */
	public function getPrimaryKeyInformation($a_table) {
		$constraints = $this->manager->listTableConstraints($a_table);

		$pk = false;
		foreach ($constraints as $c) {

			$info = $this->reverse->getTableConstraintDefinition($a_table, $c);

			if ($info["primary"]) {
				$pk["name"] = $c;
				foreach ($info["fields"] as $k => $f) {
					$pk["fields"][$k] = array(
						"position" => $f["position"],
						"sorting"  => $f["sorting"],
					);
				}
			}
		}

		return $pk;
	}


	/**
	 * Get information on indices of a table.
	 * Primary key is NOT included!
	 * Fulltext indices are included and marked.
	 *
	 * @param    string        table name
	 * @return    array        indices information array
	 */
	public function getIndicesInformation($a_table, $a_abstract_table = false) {
		//$constraints = $this->manager->listTableConstraints($a_table);
		$indexes = $this->manager->listTableIndexes($a_table);

		// get additional information if database is MySQL
		$mysql_info = array();
		if (in_array($this->il_db->getDBType(), array(
			ilDBConstants::TYPE_MYSQL,
			ilDBConstants::TYPE_INNODB,
			ilDBConstants::TYPE_PDO_MYSQL_INNODB,
			ilDBConstants::TYPE_PDO_MYSQL_MYISAM,
		))) {
			$set = $this->il_db->query("SHOW INDEX FROM " . $a_table);
			while ($rec = $this->il_db->fetchAssoc($set)) {
				if (!empty ($rec["Key_name"])) {
					$mysql_info[$rec["Key_name"]] = $rec;
				} else {
					$mysql_info[$rec["key_name"]] = $rec;
				}
			}
		}

		$ind = array();
		foreach ($indexes as $c) {
			$info = $this->reverse->getTableIndexDefinition($a_table, $c);

			$i = array();
			if (!$info["primary"]) {
				$i["name"] = $c;
				$i["fulltext"] = false;
				$suffix = ($a_abstract_table) ? "_idx" : "";

				if ($mysql_info[$i["name"]]["Index_type"] == "FULLTEXT"
				    || $mysql_info[$i["name"] . "_idx"]["Index_type"] == "FULLTEXT"
				    || $mysql_info[$i["name"]]["index_type"] == "FULLTEXT"
				    || $mysql_info[$i["name"] . "_idx"]["index_type"] == "FULLTEXT"
				) {
					$i["fulltext"] = true;
				}
				foreach ($info["fields"] as $k => $f) {
					$i["fields"][$k] = array(
						"position" => $f["position"],
						"sorting"  => $f["sorting"],
					);
				}
				$ind[] = $i;
			}
		}

		return $ind;
	}


	/**
	 * Get information on constraints of a table.
	 * Primary key is NOT included!
	 * Fulltext indices are included and marked.
	 *
	 * @param    string        table name
	 * @return    array        indices information array
	 */
	public function getConstraintsInformation($a_table, $a_abstract_table = false) {
		$constraints = $this->manager->listTableConstraints($a_table);

		$cons = array();
		foreach ($constraints as $c) {
			$info = $this->reverse->getTableConstraintDefinition($a_table, $c);
			//var_dump($info);
			$i = array();
			if ($info["unique"]) {
				$i["name"] = $c;
				$i["type"] = "unique";
				foreach ($info["fields"] as $k => $f) {
					$i["fields"][$k] = array(
						"position" => $f["position"],
						"sorting"  => $f["sorting"],
					);
				}
				$cons[] = $i;
			}
		}

		return $cons;
	}


	/**
	 * Check whether sequence is defined for current table (only works on "abstraced" tables)
	 *
	 * @param    string        table name
	 * @return mixed false, if no sequence is defined, start number otherwise
	 *
	 * @throws \ilDatabaseException
	 * @deprecated Please do not use since this method will lead to a ilDatabaseException. Will be removed later
	 */
	public function hasSequence($a_table) {
		$seq = $this->manager->listSequences();
		if (is_array($seq) && in_array($a_table, $seq)) {
			// sequence field is (only) primary key field of table
			$pk = $this->getPrimaryKeyInformation($a_table);
			if (is_array($pk["fields"]) && count($pk["fields"] == 1)) {
				$seq_field = key($pk["fields"]);
			} else {
				throw new ilDatabaseException("ilDBAnalyzer::hasSequence: Error, sequence defined, but no one-field primary key given. Table: "
				                              . $a_table . ".");
			}

			$set = $this->il_db->query("SELECT MAX(" .$this->il_db->quoteIdentifier($seq_field) . ") ma FROM " . $this->il_db->quoteIdentifier($a_table) . "");
			$rec = $this->il_db->fetchAssoc($set);
			$next = $rec["ma"] + 1;

			return $next;
		}

		return false;
	}
}

?>
