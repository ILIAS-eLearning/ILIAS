<?php

/**
 * GUI-Class arIndexTableAction
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 *
 */
class arIndexTableAction {

	/**
	 * @var int
	 */
	protected $id = 0;
	/**
	 * @var string
	 */
	protected $title = "";
	/**
	 * @var string
	 */
	protected $target_class = "";
	/**
	 * @var string
	 */
	protected $target_cmd = "";
	/**
	 * @var string
	 */
	protected $access = "";


	/**
	 * @param      $id
	 * @param      $title
	 * @param      $target_class
	 * @param      $target_cmd
	 * @param null $access
	 */
	function __construct($id, $title, $target_class, $target_cmd, $access = NULL) {
		$this->id = $id;
		$this->title = $title;
		$this->target_class = $target_class;
		$this->target_cmd = $target_cmd;
		$this->access = $access;
	}


	/**
	 * @param string $access
	 */
	public function setAccess($access) {
		$this->access = $access;
	}


	/**
	 * @return string
	 */
	public function getAccess() {
		return $this->access;
	}


	/**
	 * @param \stdClass $actions
	 */
	public function setActions($actions) {
		$this->actions = $actions;
	}


	/**
	 * @return \stdClass
	 */
	public function getActions() {
		return $this->actions;
	}


	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param string $target_class
	 */
	public function setTargetClass($target_class) {
		$this->target_class = $target_class;
	}


	/**
	 * @return string
	 */
	public function getTargetClass() {
		return $this->target_class;
	}


	/**
	 * @param string $target_cmd
	 */
	public function setTargetCmd($target_cmd) {
		$this->target_cmd = $target_cmd;
	}


	/**
	 * @return string
	 */
	public function getTargetCmd() {
		return $this->target_cmd;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
}