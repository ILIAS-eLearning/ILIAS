<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatementCollection.php');
require_once('class.arJoin.php');

/**
 * Class arJoinCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arJoinCollection extends arStatementCollection {

	/**
	 * @var array
	 */
	protected $table_names = [ ];


	/**
	 * @param arJoin $statement
	 */
	public function add(arJoin $statement) {
		$table_name = $statement->getTableName();
		if (in_array($table_name, $this->table_names)) {

			$vals = array_count_values($this->table_names);
			$next = $vals[$table_name] + 1;
			$statement->setFullNames(true);
			$statement->setTableNameAs($table_name . '_' . $next);
		} else {
			$statement->setTableNameAs($table_name);
		}
		$this->table_names[] = $table_name;
		parent::add($statement);
	}


	/**
	 * @return string
	 */
	public function asSQLStatement() {
		$ar = $this->getAr();
		$table_name = $ar->getConnectorContainerName();
		$selected_fields = array();
		if ($this->hasStatements()) {
			$selected_fields[] = $table_name . '.*';
			$return = 'SELECT ';
			foreach ($this->getJoins() as $join) {
				foreach ($join->getFields() as $field) {
					//if (! in_array($join->getTableName() . '.' . $field, $selected_fields)) {
					if ($join->getFullNames()) {
						$selected_fields[] = $join->getTableNameAs() . '.' . $field . ' AS ' . $join->getTableNameAs() . '_' . $field;
					} else {
						$selected_fields[] = $join->getTableNameAs() . '.' . $field;
					}
					//}
				}
			}

			$return .= implode(', ', $selected_fields);
			$return .= ' FROM ' . $table_name;

			foreach ($this->getJoins() as $join) {
				$return .= $join->asSQLStatement($ar);
			}
		} else {
			$return = 'SELECT * FROM ' . $table_name;
		}

		return $return;
	}


	/**
	 * @return arJoin[]
	 */
	public function getJoins() {
		return $this->statements;
	}
}

?>
