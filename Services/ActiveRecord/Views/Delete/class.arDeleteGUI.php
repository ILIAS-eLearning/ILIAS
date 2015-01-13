<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * GUI-Class arDeleteGUI
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           2.0.7
 *
 */
class arDeleteGUI extends arIndexTableGUI {

	/**
	 * @var array
	 */
	protected $ids = NULL;


	/**
	 * @param arGUI            $a_parent_obj
	 * @param string           $a_parent_cmd
	 * @param ActiveRecordList $active_record_list
	 * @param null             $custom_title
	 * @param null             $ids
	 */
	public function __construct(arGUI $a_parent_obj, $a_parent_cmd, ActiveRecordList $active_record_list, $custom_title = NULL, $ids = NULL) {
		$this->setIds($ids);
		parent::__construct($a_parent_obj, $a_parent_cmd, $active_record_list, $custom_title);
	}


	protected function initActions() {
	}


	protected function initFormAction() {
		$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
	}


	protected function initRowSelector() {
		$this->setShowRowsSelector(false);
		$this->setLimit(9999);
	}


	/**
	 * @return array
	 */
	function getSelectableColumns() {
		return array();
	}


	protected function initCommandButtons() {
		$this->addCommandButton("deleteItems", $this->txt("delete", false));
		$this->addCommandButton("index", $this->txt("cancel", false));

		$id_nr = 0;
		foreach ($this->getIds() as $id) {
			$this->addHiddenInput("delete_id_" . $id_nr, $id);
			$id_nr ++;
		}
		$this->addHiddenInput("nr_ids", $id_nr);
	}


	public function customizeFields() {
		$field = $this->getFields()->getPrimaryField();
		/**
		 * @var arIndexTableField $field
		 */
		$field->setTxt($field->getName());
		$field->setVisibleDefault(true);
		$field->setHasFilter(false);
		$field->setSortable(false);
		$field->setPosition(0);
	}


	/**
	 * @return bool
	 * @description returns false, if no filter is needed, otherwise implement filters
	 *
	 */
	protected function beforeGetData() {

		$this->active_record_list->where($this->buildWhereQueryForIds($this->getIds()));
	}


	/**
	 * @param $ids
	 *
	 * @return string
	 */
	public function buildWhereQueryForIds($ids) {
		$query = "";
		foreach ($ids as $id) {
			if ($query != "") {
				$query .= " OR ";
			}
			$query .= $this->getFields()->getPrimaryField()->getName() . " = '" . $id . "'";
		}

		return $query;
	}


	/**
	 * @param array $ids
	 */
	public function setIds($ids) {
		$this->ids = $ids;
	}


	/**
	 * @return array
	 */
	public function getIds() {
		return $this->ids;
	}
}