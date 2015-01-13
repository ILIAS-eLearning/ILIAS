<?php
require_once('class.arStatement.php');

/**
 * Class arStatementCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
abstract class arStatementCollection {

	/**
	 * @var arStatementCollection[]
	 */
	protected static $cache = array();
	/**
	 * @var arStatement[]
	 */
	protected $statements = array();
	/**
	 * @var ActiveRecord
	 */
	protected $ar;


	/**
	 * @param arStatement $statement
	 */
	public function add(arStatement $statement) {
		$this->statements[] = $statement;
	}


	/**
	 * @return bool
	 */
	public function hasStatements() {
		return count($this->statements) > 0;
	}


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return arStatementCollection
	 */
	public static function getInstance(ActiveRecord $ar) {
		/**
		 * @var $classname arJoinCollection
		 */

		$classname = get_called_class();
		$arWhereCollection = new $classname();
		$arWhereCollection->setAr($ar);

		return $arWhereCollection;
	}


	/**
	 * @return string
	 */
	abstract public function asSQLStatement();


	/**
	 * @param \ActiveRecord $ar
	 */
	public function setAr($ar) {
		$this->ar = $ar;
	}


	/**
	 * @return \ActiveRecord
	 */
	public function getAr() {
		return $this->ar;
	}


	/**
	 * @param \arStatement[] $statements
	 */
	public function setStatements($statements) {
		$this->statements = $statements;
	}


	/**
	 * @return \arStatement[]
	 */
	public function getStatements() {
		return $this->statements;
	}
}

?>
