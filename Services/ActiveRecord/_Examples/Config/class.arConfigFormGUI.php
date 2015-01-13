<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('class.arConfig.php');

/**
 * GUI-Class arConfigFormGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           2.0.7
 *
 */
class arConfigFormGUI extends ilPropertyFormGUI {

	/**
	 * @var
	 */
	protected $parent_gui;
	/**
	 * @var  ilCtrl
	 */
	protected $ctrl;


	/**
	 * @param $parent_gui
	 */
	public function __construct($parent_gui) {
		global $ilCtrl, $lng;
		$this->parent_gui = $parent_gui;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->ctrl->saveParameter($parent_gui, 'clip_ext_id');
		$this->setFormAction($this->ctrl->getFormAction($parent_gui));
		$this->initForm();
	}


	protected function initForm() {
		$this->setTitle($this->lng->txt('admin_form_title'));

		$te = new ilTextInputGUI($this->lng->txt('admin_origins_path'), 'path');
		$te->setInfo($this->lng->txt('admin_origins_path_info'));
		$this->addItem($te);

		$this->addCommandButtons();
	}


	public function fillForm() {
		$array = array();
		foreach ($this->getItems() as $item) {
			$this->getValuesForItem($item, $array);
		}
		$this->setValuesByArray($array);
	}


	/**
	 * @param $item
	 * @param $array
	 *
	 * @internal param $key
	 */
	private function getValuesForItem($item, &$array) {
		if (self::checkItem($item)) {
			$key = $item->getPostVar();
			$array[$key] = json_decode(arConfig::get($key));
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->getValuesForItem($subitem, $array);
				}
			}
		}
	}


	/**
	 * returns whether checkinput was successful or not.
	 *
	 * @return bool
	 */
	public function fillObject() {
		if (!$this->checkInput()) {
			return false;
		}

		return true;
	}


	/**
	 * @return bool
	 */
	public function saveObject() {
		if (!$this->fillObject()) {
			return false;
		}
		foreach ($this->getItems() as $item) {
			$this->saveValueForItem($item);
		}

		return true;
	}


	/**
	 * @param $item
	 */
	private function saveValueForItem($item) {
		if (self::checkItem($item)) {
			$key = $item->getPostVar();
			arConfig::set($key, json_encode($this->getInput($key)));
			if (self::checkForSubItem($item)) {
				foreach ($item->getSubItems() as $subitem) {
					$this->saveValueForItem($subitem);
				}
			}
		}
	}


	/**
	 * @param ilPropertyFormGUI $item
	 *
	 * @return bool
	 */
	public static function checkItem(ilPropertyFormGUI $item) {
		return !$item instanceof ilFormSectionHeaderGUI AND !$item instanceof ilMultiSelectInputGUI;
	}


	/**
	 * @param ilPropertyFormGUI $item
	 *
	 * @return bool
	 */
	public static function checkForSubItem(ilPropertyFormGUI $item) {
		return !$item instanceof ilMultiSelectInputGUI;
	}


	protected function addCommandButtons() {
		$this->addCommandButton('save', $this->lng->txt('admin_form_button_save'));
		$this->addCommandButton('cancel', $this->lng->txt('admin_form_button_cancel'));
	}
}