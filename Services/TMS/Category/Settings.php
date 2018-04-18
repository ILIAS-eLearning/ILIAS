<?php

class Settings {
	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var bool
	 */
	protected $show_in_cockpit;

	public function __construct($obj_id, $show_in_cockpit) {
		assert('is_int($obj_id)');
		assert('is_bool($show_in_cockpit)');

		$this->obj_id = $obj_id;
		$this->show_in_cockpit = $show_in_cockpit;
	}

	public function getObjId() {
		return $this->obj_id;
	}

	public function getShowInCockpit() {
		return $this->show_in_cockpit;
	}

	public function withShowInCockpit($show_in_cockpit) {
		assert('is_bool($show_in_cockpit)');
		$clone = clone $this;
		$clone->show_in_cockpit = $show_in_cockpit;
		return $clone;
	}
}