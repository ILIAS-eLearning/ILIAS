<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * GUI-Class arDisplayGUI
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           2.0.5
 *
 */
class arDisplayGUI {

	/**
	 * @var  ActiveRecord
	 */
	protected $record;
	/**
	 * @var arGUI
	 */
	protected $parent_gui;
	/**
	 * @var  ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var  ilTemplate
	 */
	protected $tpl;
	/**
	 * @var string
	 */
	protected $title = "";
	/**
	 * @var array
	 */
	protected $fields_to_hide = array();
	/**
	 * @var data
	 */
	protected $data = array();
	/**
	 * @var string
	 */
	protected $back_button_name = "";
	/**
	 * @var string
	 */
	protected $back_button_target = "";


	/**
	 * @param              $parent_gui
	 * @param ActiveRecord $record
	 * @param ilPlugin     $plugin_object
	 */
	public function __construct(arGUI $parent_gui, ActiveRecord $ar) {
		global $ilCtrl, $tpl;
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->ar = $ar;
		$this->parent_gui = $parent_gui;

		$this->setTitle($this->txt("record"));
		$this->initFieldsToHide();
		$this->setData();
		$this->setBackButtonName($this->txt("back", false));
		$this->setBackButtonTarget($this->ctrl->getLinkTarget($this->parent_gui, "index"));

		$this->ctrl->saveParameter($parent_gui, 'ar_id');
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


	/**
	 * @param array $fields_to_hide
	 */
	public function setFieldsToHide($fields_to_hide) {
		$this->fields_to_hide = $fields_to_hide;
	}


	/**
	 * @return array
	 */
	public function getFieldsToHide() {
		return $this->fields_to_hide;
	}


	protected function initFieldsToHide() {
	}


	protected function setData() {
		foreach ($this->ar->getArFieldList()->getFields() as $field) {
			if (! in_array($field->getName(), $this->getFieldsToHide())) {
				$this->setField($field);
			}
		}
		$this->afterSetData();
	}


	protected function afterSetData() {
	}


	protected function setField(arField $field) {
		$field_element = NULL;
		$get_function = "get" . $this->ar->_toCamelCase($field->getName(), true);
		$value = $this->ar->$get_function();
		switch ($field->getFieldType()) {
			case 'integer':
			case 'float':
				$this->setNumericData($field, $value);
				break;
			case 'text':
				$this->setTextData($field, $value);
				break;
			case 'date':
			case 'time':
			case 'timestamp':
				$this->setDateTimeData($field, $value);
				break;
			case 'clob':
				$this->setClobData($field, $value);
				break;
		}
	}


	protected function setNumericData(arField $field, $value) {
		$this->data[$field->getName()] = $value;
	}


	protected function setTextData(arField $field, $value) {
		$this->data[$field->getName()] = $value;
	}


	protected function setDateTimeData(arField $field, $value) {
		$datetime = new ilDateTime($value, IL_CAL_DATETIME);
		$this->data[$field->getName()] = ilDatePresentation::formatDate($datetime, IL_CAL_DATETIME);
	}


	protected function setClobData(arField $field, $value) {
		$this->data[$field->getName()] = $value;
	}


	public function getHtml() {
		$tpl_display = new ilTemplate("tpl.display.html", true, true, "Customizing/global/plugins/Libraries/ActiveRecord");

		$tpl_display->setVariable("TITLE", $this->title);
		foreach ($this->data as $key => $entry) {
			$tpl_display->setCurrentBlock("entry");
			$tpl_display->setVariable("ITEM", $this->txt($key));
			$tpl_display->setVariable("VALUE", $entry);
			$tpl_display->parseCurrentBlock();
		}

		$tpl_display->setVariable("BACK_BUTTON_NAME", $this->getBackButtonName());
		$tpl_display->setVariable("BACK_BUTTON_TARGET", $this->getBackButtonTarget());

		return $tpl_display->get();
	}


	/**
	 * @param string $back_button_name
	 */
	public function setBackButtonName($back_button_name) {
		$this->back_button_name = $back_button_name;
	}


	/**
	 * @return string
	 */
	public function getBackButtonName() {
		return $this->back_button_name;
	}


	/**
	 * @param string $back_button_value
	 */
	public function setBackButtonTarget($back_button_target) {
		$this->back_button_target = $back_button_target;
	}


	/**
	 * @return string
	 */
	public function getBackButtonTarget() {
		return $this->back_button_target;
	}


	protected function txt($txt, $plugin_txt = true) {
		return $this->parent_gui->txt($txt, $plugin_txt);
	}
}

?>