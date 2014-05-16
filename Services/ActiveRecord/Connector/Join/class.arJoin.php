<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arJoin
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.02
 */
class arJoin extends arStatement {

	const TYPE_NORMAL = '';
	const TYPE_LEFT = 'LEFT';
	const TYPE_RIGHT = 'RIGHT';
	const TYPE_INNER = 'INNER';
	/**
	 * @var string
	 */
	protected $type = self::TYPE_NORMAL;
	/**
	 * @var string
	 */
	protected $table_name = '';
	/**
	 * @var array
	 */
	protected $fields = array( '*' );
	/**
	 * @var string
	 */
	protected $operator = '=';
	/**
	 * @var string
	 */
	protected $on_first_field = '';
	/**
	 * @var string
	 */
	protected $on_second_field = '';


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return string
	 */
	public function asSQLStatement(ActiveRecord $ar) {
		$join_table_name = $this->getTableName();
		$return = ' JOIN ' . $join_table_name;
		$return .= ' ON ' . $ar::returnDbTableName() . '.' . $this->getOnFirstField() . ' ' . $this->getOperator() . ' ';
		$return .= $join_table_name . '.' . $this->getOnSecondField();

		return $return;
	}


	public function setLeft() {
		$this->setType(self::TYPE_LEFT);
	}


	public function setRght() {
		$this->setType(self::TYPE_RIGHT);
	}


	public function setInner() {
		$this->setType(self::TYPE_INNER);
	}


	/**
	 * @param array $fields
	 */
	public function setFields($fields) {
		$this->fields = $fields;
	}


	/**
	 * @return array
	 */
	public function getFields() {
		return $this->fields;
	}


	/**
	 * @param string $on_first_field
	 */
	public function setOnFirstField($on_first_field) {
		$this->on_first_field = $on_first_field;
	}


	/**
	 * @return string
	 */
	public function getOnFirstField() {
		return $this->on_first_field;
	}


	/**
	 * @param string $on_second_field
	 */
	public function setOnSecondField($on_second_field) {
		$this->on_second_field = $on_second_field;
	}


	/**
	 * @return string
	 */
	public function getOnSecondField() {
		return $this->on_second_field;
	}


	/**
	 * @param string $operator
	 */
	public function setOperator($operator) {
		$this->operator = $operator;
	}


	/**
	 * @return string
	 */
	public function getOperator() {
		return $this->operator;
	}


	/**
	 * @param string $table_name
	 */
	public function setTableName($table_name) {
		$this->table_name = $table_name;
	}


	/**
	 * @return string
	 */
	public function getTableName() {
		return $this->table_name;
	}


	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}


	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
}

?>
