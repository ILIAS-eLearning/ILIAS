<?php

/**
 * Class arStatement
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
abstract class arStatement {

	/**
	 * @var string
	 */
	protected $table_name_as = '';


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return string
	 */
	abstract public function asSQLStatement(ActiveRecord $ar);


	/**
	 * @return string
	 */
	public function getTableNameAs() {
		return $this->table_name_as;
	}


	/**
	 * @param string $table_name_as
	 */
	public function setTableNameAs($table_name_as) {
		$this->table_name_as = $table_name_as;
	}
}

?>
