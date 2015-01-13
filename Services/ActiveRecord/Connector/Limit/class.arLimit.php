<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arLimit
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 *
 * @version 2.0.7
 */
class arLimit extends arStatement {

	/**
	 * @var int
	 */
	protected $start;
	/**
	 * @var int
	 */
	protected $end;


	/**
	 * @param ActiveRecord $ar
	 *
	 * @return string
	 */
	public function asSQLStatement(ActiveRecord $ar) {
		return ' LIMIT ' . $this->getStart() . ', ' . $this->getEnd();
	}


	/**
	 * @param int $end
	 */
	public function setEnd($end) {
		$this->end = $end;
	}


	/**
	 * @return int
	 */
	public function getEnd() {
		return $this->end;
	}


	/**
	 * @param int $start
	 */
	public function setStart($start) {
		$this->start = $start;
	}


	/**
	 * @return int
	 */
	public function getStart() {
		return $this->start;
	}
}

?>
