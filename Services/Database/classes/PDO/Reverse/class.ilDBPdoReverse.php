<?php

/**
 * Class ilDBPdoReverse
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDBPdoReverse implements ilDBReverse {

	/**
	 * @var PDO
	 */
	protected $pdo;
	/**
	 * @var ilDBPdo
	 */
	protected $db_instance;


	/**
	 * ilDBPdoReverse constructor.
	 *
	 * @param \PDO $pdo
	 * @param \ilDBPdo $db_instance
	 */
	public function __construct(\PDO $pdo, ilDBPdo $db_instance) {
		$this->pdo = $pdo;
		$this->db_instance = $db_instance;
	}


	/**
	 * @param $table_name
	 * @param $field_name
	 * @return array
	 */
	public function getTableFieldDefinition($table_name, $field_name) {
		$return = array();
		throw new ilDatabaseException('not yet implemented ' . __METHOD__);




		//		$result = $db->loadModule('Datatype', null, true); // Hope we dont have to implement this module, too??
		//		if (PEAR::isError($result)) {
		//			return $result;
		//		}
		$table = $this->db_instance->quoteIdentifier($table_name);
		$query = "SHOW COLUMNS FROM $table LIKE " . $this->db_instance->quote($field_name);
		$res = $this->pdo->query($query);
		$columns = array();
		while($data = $res->fetch(PDO::FETCH_ASSOC)) {
			$columns[] = $data;
		}
		echo '<pre>' . print_r($columns, 1) . '</pre>';
		echo '<pre>' . print_r($query, 1) . '</pre>';
		echo '<pre>' . print_r($table_name, 1) . '</pre>';

		throw new Exception();
		exit;

		$columns = $db->queryAll($query, null, MDB2_FETCHMODE_ASSOC);
		if (PEAR::isError($columns)) {
			return $columns;
		}
		foreach ($columns as $column) {
			$column = array_change_key_case($column, CASE_LOWER);
			$column['name'] = $column['field'];
			unset($column['field']);
			if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
				if ($db->options['field_case'] == CASE_LOWER) {
					$column['name'] = strtolower($column['name']);
				} else {
					$column['name'] = strtoupper($column['name']);
				}
			} else {
				$column = array_change_key_case($column, $db->options['field_case']);
			}
			if ($field_name == $column['name']) {
				$mapped_datatype = $db->datatype->mapNativeDatatype($column);
				if (PEAR::IsError($mapped_datatype)) {
					return $mapped_datatype;
				}
				list($types, $length, $unsigned, $fixed) = $mapped_datatype;
				$notnull = false;
				if (empty($column['null']) || $column['null'] !== 'YES') {
					$notnull = true;
				}
				$default = false;
				if (array_key_exists('default', $column)) {
					$default = $column['default'];
					if (is_null($default) && $notnull) {
						$default = '';
					}
				}
				$autoincrement = false;
				if (!empty($column['extra']) && $column['extra'] == 'auto_increment') {
					$autoincrement = true;
				}

				$definition[0] = array(
					'notnull'    => $notnull,
					'nativetype' => preg_replace('/^([a-z]+)[^a-z].*/i', '\\1', $column['type']),
				);
				if (!is_null($length)) {
					$definition[0]['length'] = $length;
				}
				if (!is_null($unsigned)) {
					$definition[0]['unsigned'] = $unsigned;
				}
				if (!is_null($fixed)) {
					$definition[0]['fixed'] = $fixed;
				}
				if ($default !== false) {
					$definition[0]['default'] = $default;
				}
				if ($autoincrement !== false) {
					$definition[0]['autoincrement'] = $autoincrement;
				}
				foreach ($types as $key => $type) {
					$definition[$key] = $definition[0];
					if ($type == 'clob' || $type == 'blob') {
						unset($definition[$key]['default']);
					}
					$definition[$key]['type'] = $type;
					$definition[$key]['mdb2type'] = $type;
				}

				return $definition;
			}
		}

		return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null, 'it was not specified an existing table column', __FUNCTION__);

		return $return;
	}


	public function getTableIndexDefinition($table, $constraint_name) {
		throw new ilDatabaseException('not yet implemented ' . __METHOD__);
	}


	public function getTableConstraintDefinition($table, $index_name) {
		throw new ilDatabaseException('not yet implemented ' . __METHOD__);
	}


	public function getTriggerDefinition($trigger) {
		throw new ilDatabaseException('not yet implemented ' . __METHOD__);
	}


	public function tableInfo($result, $mode = null) {
		throw new ilDatabaseException('not yet implemented ' . __METHOD__);
	}
}
