<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatementCollection.php');
require_once('class.arSelect.php');

/**
 * Class arSelectCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arSelectCollection extends arStatementCollection {

	/**
	 * @return string
	 */
	public function asSQLStatement() {
		$return = 'SELECT ';
		if ($this->hasStatements()) {
			foreach ($this->getSelects() as $select) {
				$return .= $select->asSQLStatement($this->getAr());
				if ($select != end($this->getSelects())) {
					$return .= ', ';
				}
			}
		}

//		$return .= ' FROM ' . $this->getAr()->getConnectorContainerName();

		return $return;
	}


	/**
	 * @return arSelect[]
	 */
	public function getSelects() {
		return $this->statements;
	}
}

?>
