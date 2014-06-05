<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatementCollection.php');
require_once('class.arJoin.php');

/**
 * Class arJoinCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.4
 */
class arJoinCollection extends arStatementCollection {

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
					$selected_fields[] = $join->getTableName() . '.' . $field;
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
