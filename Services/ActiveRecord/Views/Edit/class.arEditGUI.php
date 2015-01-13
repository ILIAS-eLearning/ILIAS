<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Edit/class.arEditField.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Edit/class.arEditFields.php');

/**
 * GUI-Class arEditGUI
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           2.0.7
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
	 * @var string
	 */
	protected $form_prefix = "";
	/**
	 * @var arEditFields
	 */
	protected $fields;


	/**
	 * @param arGUI        $parent_gui
	 * @param ActiveRecord $ar
	 */
	public function __construct(arGUI $parent_gui, ActiveRecord $ar) {
		global $ilCtrl;

		$this->ar = $ar;
		$this->parent_gui = $parent_gui;
		$this->ctrl = $ilCtrl;
		$this->ctrl->saveParameter($parent_gui, 'ar_id');
		$this->setFormName(get_class($ar));
		$this->init();
	}


	/**
	 ******************************************************************
	 *********************** Form Initialization **********************
	 ******************************************************************
	 */
	protected function init() {
		$this->initFields();
		$this->initForm();
		if ($this->ar->getPrimaryFieldValue() != 0) {
			$this->fillForm();
		}
	}


	protected function initFields() {
		$this->fields = new arEditFields($this->ar);
		$this->customizeFields();
		$this->fields->sortFields();
	}


	protected function customizeFields() {
	}


	protected function initForm() {
		$this->BeforeInitForm();
		$this->initFormAction();
		$this->initFormTitle();
		$this->generateFormFields();
		$this->initCommandButtons();
		$this->afterInitForm();
	}


	protected function beforeInitForm() {
	}


	protected function initFormAction() {
		$this->setFormAction($this->ctrl->getFormAction($this->parent_gui, "index"));
	}


	protected function initFormTitle() {
		$this->setFormPrefix("");
		if ($this->ar->getPrimaryFieldValue() == 0) {
			$this->setTitle($this->txt($this->getFormPrefix() . 'create_' . $this->getFormName()));
		} else {
			$this->setTitle($this->txt($this->getFormPrefix() . 'edit_' . $this->getFormName()));
		}
	}


	protected function generateFormFields() {

		foreach ($this->fields->getFields() as $field) {
			/**
			 * @var arEditField $field
			 */
			if ($field->getVisible()) {
				$this->addFormField($field);
			}
		}
	}


	/**
	 * @param arEditField $field
	 */
	protected function addFormField(arEditField $field) {
		$field_element = NULL;
		if (!$field->getFormElement()) {
			switch ($field->getFieldType()) {
				case 'integer':
				case 'float':
					$field->setFormElement($this->addNumbericInputField($field));
					break;
					break;
				case 'date':
				case 'time':
				case 'timestamp':
					$field->setFormElement($this->addDateTimeInputField($field));
					break;
				case 'clob':
					$field->setFormElement($this->addClobInputField($field));
					break;
				default:
					$field->setFormElement($this->addTextInputField($field));
			}
			if ($field->getNotNull()) {
				$field->getFormElement()->setRequired(true);
			}
		}

		if ($field->getFormElement()) {
			if ($field->getSubelementOf()) {
				$field->getSubelementOf()->addSubItem($field->getFormElement());
			} else {
				$this->addItem($field->getFormElement());
			}
		}
	}


	/**
	 * @param arEditField $field
	 *
	 * @return ilTextInputGUI
	 */
	protected function addBooleanInputField(arEditField $field) {
		return new ilCheckboxInputGUI($this->txt($field->getTxt()), $field->getName());
	}


	/**
	 * @param arEditField $field
	 *
	 * @return ilTextInputGUI
	 */
	protected function addTextInputField(arEditField $field) {
		return new ilTextInputGUI($this->txt($field->getTxt()), $field->getName());
	}


	/**
	 * @param arEditField $field
	 *
	 * @return ilNumberInputGUI
	 */
	protected function addNumbericInputField(arEditField $field) {
		return new ilNumberInputGUI($this->txt($field->getTxt()), $field->getName());
	}


	/**
	 * @param arEditField $field
	 *
	 * @return ilDateTimeInputGUI
	 */
	protected function addDateTimeInputField(arEditField $field) {
		$date_input = new ilDateTimeInputGUI($this->txt($field->getTxt()), $field->getName());
		$date_input->setDate(new ilDate(date('Y-m-d H:i:s'), IL_CAL_DATE));
		$date_input->setShowTime(true);

		return $date_input;
	}


	/**
	 * @param arEditField $field
	 *
	 * @return ilTextAreaInputGUI
	 */
	protected function addClobInputField(arEditField $field) {
		return new ilTextAreaInputGUI($this->txt($field->getTxt()), $field->getName());
	}


	protected function initCommandButtons() {
		if ($this->ar->getPrimaryFieldValue() == 0) {
			$this->addCommandButton('create', $this->txt('create', false));
		} else {
			$this->addCommandButton('update', $this->txt('save', false));
		}
		$this->addCommandButton('index', $this->txt('cancel', false));
	}


	protected function afterInitForm() {
	}


	/**
	 ******************************************************************
	 *********************** Fill Form  *******************************
	 ******************************************************************
	 */
	public function fillForm() {
		$this->beforeFillForm();
		foreach ($this->fields->getFields() as $field) {
			/**
			 * @var arEditField $field
			 */
			if ($field->getVisible()) {
				if ($field->getFormElement()) {
					$this->fillFormField($field);
				}
			}
		}
		$this->afterFillForm();
	}


	protected function beforeFillForm() {
	}


	protected function afterFillForm() {
	}


	/**
	 * @param arEditField $field
	 */
	protected function fillFormField(arEditField $field) {
		$get_function = $field->getGetFunctionName();
		switch (get_class($field->getFormElement())) {
			case 'ilCheckboxInputGUI':
				$field->getFormElement()->setValue(1);
				$field->getFormElement()->setChecked($this->ar->$get_function() == 1 ? true : false);
				break;
			case 'ilNumberInputGUI':
			case 'ilSelectInputGUI':
			case 'ilTextInputGUI':
			case 'ilTextAreaInputGUI':
			case 'ilRadioGroupInputGUI':
				$field->getFormElement()->setValue($this->ar->$get_function());
				break;
			case 'ilDateTimeInputGUI':
			case 'ilDate':
				/**
				 * @var ilDateTimeInputGUI $form_item
				 */
				$datetime = new ilDateTime($this->ar->$get_function(), IL_CAL_DATETIME);
				$form_item->setDate($datetime);
				break;
			default:
				$this->fillCustomFormField($field);
				break;
		}
	}


	/**
	 * @param arEditField $field
	 */
	protected function fillCustomFormField(arEditField $field) {
	}



	/**
	 ******************************************************************
	 *********************** After Submit  ****************************
	 ******************************************************************
	 */
	/**
	 * @return bool
	 */
	public function saveObject() {
		if (!$this->beforeSave()) {
			return false;
		}
		global $ilUser;
		/**
		 * @var ilObjUser $ilUser
		 */
		if (!$this->setArFieldsAfterSubmit()) {
			return false;
		}
		$modified_by_field = $this->getFields()->getModifiedByField();
		if ($modified_by_field) {
			$set_modified_by_function = $modified_by_field->getSetFunctionName();
			$this->ar->$set_modified_by_function($ilUser->getId());
		}
		$modification_date_field = $this->getFields()->getModificationDateField();
		if ($modification_date_field) {
			$set_modification_date_function = $modification_date_field->getSetFunctionName();
			$datetime = new ilDateTime(time(), IL_CAL_UNIX);
			$this->ar->$set_modification_date_function($datetime);
		}
		if ($this->ar->getPrimaryFieldValue() != 0) {
			$this->ar->update();
		} else {
			$created_by_field = $this->getFields()->getCreatedByField();
			if ($created_by_field) {
				$set_created_by_function = $created_by_field->getSetFunctionName();
				$this->ar->$set_created_by_function($ilUser->getId());
			}
			$creation_date_field = $this->getFields()->getCreationDateField();
			if ($creation_date_field) {
				$set_creation_date_function = $creation_date_field->getSetFunctionName();
				$datetime = new ilDateTime(time(), IL_CAL_UNIX);
				$this->ar->$set_creation_date_function($datetime);
			}
			$this->ar->create();
		}

		return $this->afterSave();
	}


	protected function beforeSave() {
		return true;
	}


	/**
	 * @return bool
	 */
	protected function afterSave() {
		return true;
	}


	/**
	 * @return bool
	 */
	public function setArFieldsAfterSubmit() {
		if (!$this->checkInput()) {
			return false;
		}
		if (!$this->afterValidation()) {
			return false;
		}

		foreach ($this->fields->getFields() as $field) {
			if (!$this->setArFieldAfterSubmit($field)) {
				return false;
			}
		}

		return true;
	}


	protected function afterValidation() {
		return true;
	}


	/**
	 * @param arEditField $field
	 *
	 * @return bool
	 */
	protected function setArFieldAfterSubmit(arEditField $field) {
		/**
		 * @var arEditField $field
		 */
		$valid = false;

		if ($field->getPrimary()) {
			$valid = true;

			return true;
		}
		if (array_key_exists($field->getName(), $_POST)) {
			switch (get_class($field->getFormElement())) {
				case 'ilNumberInputGUI':
				case 'ilCheckboxInputGUI':
				case 'ilSelectInputGUI':
				case 'ilRadioGroupInputGUI':
					return $this->setNumericRecordField($field);
				case 'ilTextInputGUI':
				case 'ilTextAreaInputGUI':
					return $this->setTextRecordField($field);
				case 'ilDateTimeInputGUI':
				case 'ilDate':
					return $this->setDateTimeRecordField($field);
				default:
					return $this->setCustomRecordField($field);
			}
		}

		return $this->handleEmptyPostValue($field);;
	}


	/**
	 * @param arEditField $field
	 *
	 * @return bool
	 */
	protected function setNumericRecordField(arEditField $field) {
		$set_function = $field->getSetFunctionName();
		$this->ar->$set_function($this->getInput($field->getName()));

		return true;
	}


	/**
	 * @param arEditField $field
	 *
	 * @return bool
	 */
	protected function setTextRecordField(arEditField $field) {
		$set_function = $field->getSetFunctionName();
		$this->ar->$set_function($this->getInput($field->getName()));

		return true;
	}


	/**
	 * @param arEditField $field
	 *
	 * @return bool
	 */
	protected function setDateTimeRecordField(arEditField $field) {
		$set_function = $field->getSetFunctionName();
		$value = $this->getInput($field->getName());
		if ($value['time']) {
			$datetime = new ilDateTime($value['date'] . " " . $value['time'], IL_CAL_DATETIME);
		} else {
			$datetime = new ilDateTime($value['date'], IL_CAL_DATETIME);
		}
		$this->ar->$set_function($datetime);

		return true;
	}


	/**
	 * @param arEditField $field
	 *
	 * @return bool
	 */
	protected function setCustomRecordField(arEditField $field) {
		return true;
	}


	/**
	 * @param arEditField $field
	 *
	 * @return bool
	 */
	protected function handleEmptyPostValue(arEditField $field) {
		return true;
	}

	/**
	 ******************************************************************
	 *********************** Setters and Getters  *********************
	 ******************************************************************
	 */
	/**
	 * @param arEditFields $fields
	 */
	function setFields(arEditFields $fields) {
		$this->fields = $fields;
	}


	/**
	 * @return arEditFields
	 */
	public function getFields() {
		return $this->fields;
	}


	/**
	 * @return arEditField []
	 */
	public function getFieldsAsArray() {
		return $this->getFields()->getFields();
	}


	/**
	 * @param $field_name
	 *
	 * @return arEditField
	 */
	public function getField($field_name) {
		return $this->getFields()->getField($field_name);
	}


	/**
	 * @param arEditField
	 */
	public function addEditField(arEditField $field) {
		$this->getFields()->addField($field);
	}


	/**
	 * @param      $txt
	 * @param bool $plugin_txt
	 *
	 * @return string
	 */
	protected function txt($txt, $plugin_txt = true) {
		return $this->parent_gui->txt($txt, $plugin_txt);
	}


	/**
	 * @param string $form_name
	 */
	public function setFormName($form_name) {
		$this->form_name = $form_name;
	}


	/**
	 * @return string
	 */
	public function getFormName() {
		return $this->form_name;
	}


	/**
	 * @param string $form_prefix
	 */
	public function setFormPrefix($form_prefix) {
		$this->form_prefix = $form_prefix;
	}


	/**
	 * @return string
	 */
	public function getFormPrefix() {
		return $this->form_prefix;
	}
}