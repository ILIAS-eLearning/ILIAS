<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arConcat
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
class arConcat extends arStatement {

	/**
	 * @var string
	 */
	protected $as = '';
	/**
	 * @var array
	 */
	protected $fields = array();


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return string
	 */
	public function asSQLStatement(ActiveRecord $ar) {
		return ' CONCAT(' . implode(', ', $this->getFields()) . ') AS ' . $this->getAs();
	}


	/**
	 * @return string
	 */
	public function getAs() {
		return $this->as;
	}


	/**
	 * @param string $as
	 */
	public function setAs($as) {
		$this->as = $as;
	}


	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}


	/**
	 * @param array $fields
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}
}

?>
