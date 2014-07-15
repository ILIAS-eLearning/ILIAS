<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

/**
 * GUI-Class arEditGUI
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           2.0.5
 *
 */
class arEditGUI extends ilPropertyFormGUI {

	/**
	 * @var  ActiveRecord
	 */
	protected $ar;
	/**
	 * @var arGUI
	 */
	protected $parent_gui;
	/**
	 * @var  ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var string
	 */
	protected $form_name = "";
	/**
	 * @var array
	 */
	protected $fields_to_hide = array();


	/**
	 * @param              $parent_gui
	 * @param ActiveRecord $ar
	 * @param ilPlugin     $plugin_object
	 */
	public function __construct(arGUI $parent_gui, ActiveRecord $ar) {
		global $ilCtrl;

		$this->ar = $ar;
		$this->parent_gui = $parent_gui;
		$this->ctrl = $ilCtrl;
		$this->ctrl->saveParameter($parent_gui, 'ar_id');
		$this->initFieldsToHide();
		$this->initForm();
		if ($this->ar->getId() != 0) {
			$this->fillForm();
		}
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


	protected function initForm() {
		$this->setInitFormAction();
		$this->setFormName();
		$this->generateFields();
		$this->addCommandButtons();
	}


	protected function setInitFormAction() {
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui, "index"));
	}


	protected function generateFields() {
		foreach ($this->ar->getArFieldList()->getFields() as $field) {
			if (! in_array($field->getName(), $this->getFieldsToHide())) {
				$this->addField($field);
			}
		}
	}


	protected function addField(arField $field) {
		$field_element = NULL;
		switch ($field->getFieldType()) {
			case 'integer':
			case 'float':
				$field_element = $this->addNumbericInputField($field);
				break;
			case 'text':
				$field_element = $this->addTextInputField($field);
				break;
			case 'date':
			case 'time':
			case 'timestamp':
				$field_element = $this->addDateTimeInputField($field);
				break;
			case 'clob':
				$field_element = $this->addClobInputField($field);
				break;
		}
		if ($field->notnull) {
			$field_element->setRequired(true);
		}
		$this->adaptAnyInput($field_element, $field);
		if ($field_element) {
			$this->addItem($field_element);
		}
	}


	protected function addTextInputField(arField $field) {
		return new ilTextInputGUI($this->txt($field->getName()), $field->getName());;
	}


	protected function addNumbericInputField(arField $field) {
		return new ilNumberInputGUI($this->txt($field->getName()), $field->getName());
	}


	protected function addDateTimeInputField(arField $field) {
		$date_input = new ilDateTimeInputGUI($this->txt($field->getName()), $field->getName());
		$date_input->setDate(new ilDate(date('Y-m-d H:i:s'), IL_CAL_DATE));
		$date_input->setShowTime(true);

		return $date_input;
	}


	protected function addClobInputField(arField $field) {
		return new ilTextAreaInputGUI($this->txt($field->getName()), $field->getName());
	}


	protected function adaptAnyInput(&$any_input, arField $field) {
	}


	protected function setFormName() {
		if ($this->ar->getId() == 0) {
			$this->setTitle($this->txt('create_' . $this->form_name));
		} else {
			$this->setTitle($this->txt('edit_' . $this->form_name));
		}
	}


	public function fillForm() {
		foreach ($this->ar->getArFieldList()->getFields() as $field) {
			$form_item = $this->getItemByPostVar($field->getName());
			if (! in_array($field->getName(), $this->getFieldsToHide())) {
				$get_function = "get" . $this->ar->_toCamelCase($field->getName(), true);
				switch ($field->getFieldType()) {
					case 'integer':
					case 'float':
					case 'text':
					case 'clob':
						$form_item->setValue($this->ar->$get_function());
						break;
					case 'date':
					case 'time':
					case 'timestamp':
						$datetime = new ilDateTime($this->ar->$get_function(), IL_CAL_DATETIME);
						$form_item->setDate($datetime);
						//$values[$field->getName()] =
						break;
				}
			}
		}
	}


	/**
	 * returns whether checkinput was successful or not.
	 *
	 * @return bool
	 */
	public function setRecordFields() {
		if (! $this->checkInput()) {
			return false;
		}

		foreach ($this->ar->getArFieldList()->getFields() as $field) {
			$valid = false;

			if ($field->getName() == 'id') {
				$valid = true;
			} elseif ($field->getName() == 'created' && $this->ar->getId() == 0) {
				$datetime = new ilDateTime(time(), IL_CAL_UNIX);
				$this->ar->setCreated($datetime->get(IL_CAL_DATETIME));
				$valid = true;
			} elseif ($field->getName() == 'modified') {
				$datetime = new ilDateTime(time(), IL_CAL_UNIX);
				$this->ar->setModified($datetime->get(IL_CAL_DATETIME));
				$valid = true;
			} elseif (array_key_exists($field->getName(), $_POST)) {
				$value = $_POST[$field->getName()];

				$set_function = "set" . $this->ar->_toCamelCase($field->getName(), true);

				switch ($field->getFieldType()) {
					case 'integer':
					case 'float':
						$valid = $this->setNumbericRecordField($field, $set_function, $value);
						break;
					case 'text':
						$valid = $this->setTextRecordField($field, $set_function, $value);
						break;
					case 'date':
					case 'time':
					case 'timestamp':
						$valid = $this->setDateTimeRecordField($field, $set_function, $value);
						break;
					case 'clob':
						$valid = $this->setClobRecordField($field, $set_function, $value);
						break;
				}
			} else {
				$valid = $this->handleEmptyPostValue($field);;
			}

			if (! $valid) {
				return false;
			}
		}

		return true;
	}


	protected function setNumbericRecordField(arField $field, $set_function, $value) {
		$this->ar->$set_function($value);

		return true;
	}


	protected function setTextRecordField(arField $field, $set_function, $value) {
		$this->ar->$set_function($value);

		return true;
	}


	protected function setDateTimeRecordField(arField $field, $set_function, $value) {
		if ($value['time']) {
			$datetime = new ilDateTime($value['date'] . " " . $value['time'], IL_CAL_DATETIME);
			$timestamp = $datetime->get(IL_CAL_DATETIME);
		} else {
			$datetime = new ilDateTime($value['date'], IL_CAL_DATETIME);
			$timestamp = $datetime->get(IL_CAL_DATETIME);
		}
		$this->ar->$set_function($timestamp);

		return true;
	}


	protected function setClobRecordField(arField $field, $set_function, $value) {
		$this->ar->$set_function($value);

		return true;
	}


	protected function handleEmptyPostValue(arField $field) {
		return true;
	}


	/**
	 * @return bool
	 */
	public function saveObject() {
		if (! $this->setRecordFields()) {
			return false;
		}
		if ($this->ar->getId()) {
			$this->ar->update();
		} else {
			$this->ar->create();
		}

		return true;
	}


	protected function addCommandButtons() {
		if ($this->ar->getId() == 0) {
			$this->addCommandButton('create', $this->txt('create', false));
		} else {
			$this->addCommandButton('update', $this->txt('save', false));
		}
		$this->addCommandButton('index', $this->txt('cancel', false));
	}


	protected function txt($txt, $plugin_txt = true) {
		return $this->parent_gui->txt($txt, $plugin_txt);
	}
}