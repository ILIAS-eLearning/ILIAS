<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('class.arConfig.php');

/**
 * GUI-Class arConfig
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @version           $Id:
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


	private function initForm() {
		$this->setTitle($this->lng->txt('configuration'));
		$this->setDescription($this->lng->txt('settings_description'));

		$te = new ilTextInputGUI($this->lng->txt('admin_system_user'), 'system_user');
		$this->addItem($te);

		$this->addCommandButtons();
	}


	/**
	 * @param      $a_item
	 *
	 * @param bool $add_info
	 *
	 * @return mixed
	 */
	public function addItem($a_item, $add_info = true) {
		if (get_class($a_item) != 'ilFormSectionHeaderGUI' AND $add_info) {
			$a_item->setInfo($this->lng->txt('admin_config_' . $a_item->getPostVar() . '_info'));
		}

		return parent::addItem($a_item);
	}


	public function fillForm() {
		$array = array();
		foreach ($this->getItems() as $item) {
			$array = $this->fillValue($item, $array);
		}

		$this->setValuesByArray($array);
	}


	/**
	 * returns whether checkinput was successful or not.
	 *
	 * @return bool
	 */
	public function fillObject() {
		if (! $this->checkInput()) {
			return false;
		}

		return true;
	}


	/**
	 * @return bool
	 */
	public function saveObject() {
		if (! $this->fillObject()) {
			return false;
		}
		foreach ($this->getItems() as $item) {
			$this->writeValue($item);
		}

		return true;
	}


	protected function addCommandButtons() {
		$this->addCommandButton('save', $this->lng->txt('admin_form_button_save'));
		$this->addCommandButton('cancel', $this->lng->txt('admin_form_button_cancel'));
	}


	/**
	 * @param $item
	 * @param $array
	 *
	 * @return mixed
	 */
	protected function fillValue($item, $array) {
		if (get_class($item) != 'ilFormSectionHeaderGUI') {
			$key = $item->getPostVar();
			$array[$key] = msConfig::get($key);
			foreach ($item->getSubItems() as $sub_item) {
				$array = $this->fillValue($sub_item, $array);
			}
		}

		return $array;
	}


	/**
	 * @param $item
	 */
	protected function writeValue($item) {
		if (get_class($item) != 'ilFormSectionHeaderGUI') {
			/**
			 * @var $item ilCheckboxInputGUI
			 */
			$key = $item->getPostVar();
			msConfig::set($key, $this->getInput($key));
			foreach ($item->getSubItems() as $subitem) {
				$this->writeValue($subitem);
			}
		}
	}
}