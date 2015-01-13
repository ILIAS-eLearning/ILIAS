<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/class.arViewField.php');

/**
 * GUI-Class arIndexTableField
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 *
 */
class arIndexTableField extends arViewField {

	/**
	 * @var bool
	 */
	protected $has_filter = false;
	/**
	 * @var bool
	 */
	protected $sortable = false;
	/**
	 * @var bool
	 */
	protected $visible_default = false;


	/**
	 * @param string $name
	 * @param null   $txt
	 * @param null   $position
	 * @param bool   $visible
	 * @param bool   $custom_field
	 * @param bool   $sortable
	 * @param bool   $has_filter
	 */
	function __construct($name = "", $txt = NULL, $position = NULL, $visible = false, $custom_field = false, $sortable = false, $has_filter = false) {
		$this->sortable = $sortable;
		$this->has_filter = $has_filter;
		parent::__construct($name, $txt, $position, $visible, $custom_field);
	}


	/**
	 * @param $has_filter
	 */
	public function setHasFilter($has_filter) {
		$this->has_filter = $has_filter;
	}


	/**
	 * @return bool
	 */
	public function getHasFilter() {
		return $this->has_filter;
	}


	/**
	 * @param bool $sortable
	 */
	public function setSortable($sortable) {
		$this->sortable = $sortable;
	}


	/**
	 * @return bool
	 */
	public function getSortable() {
		return $this->sortable;
	}


	/**
	 * @param boolean $visible_default
	 */
	public function setVisibleDefault($visible_default) {
		$this->setVisible(true);
		$this->visible_default = $visible_default;
	}


	/**
	 * @return boolean
	 */
	public function getVisibleDefault() {
		return $this->visible_default;
	}
}