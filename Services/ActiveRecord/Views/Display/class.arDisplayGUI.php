<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Display/class.arDisplayField.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Display/class.arDisplayFields.php');

/**
 * GUI-Class arDisplayGUI
 *
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           2.0.7
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
	 * @var arDisplayFields|array
	 */
	protected $fields = array();
	/**
	 * @var array
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
	 * @var ilTemplate
	 */
	protected $template;


	/**
	 * @param arGUI        $parent_gui
	 * @param ActiveRecord $ar
	 */
	public function __construct(arGUI $parent_gui, ActiveRecord $ar) {
		global $ilCtrl, $tpl;
		/**
		 * @var ilCtrl     $ilCtrl
		 * @var ilTemplate $tpl
		 */
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		$this->ar = $ar;
		$this->parent_gui = $parent_gui;

		$this->ctrl->saveParameter($parent_gui, 'ar_id');

		$this->init();
	}


	protected function init() {
		$this->initTitle();
		$this->initFields();
		$this->initBackButton();
		$this->initTemplate();
	}


	protected function initTitle() {
		$this->setTitle(strtolower(str_replace("Record", "", get_class($this->ar))));
	}


	protected function initFields() {
		$this->fields = new arDisplayFields($this->ar);
		$this->customizeFields();
		$this->fields->sortFields();
	}


	protected function customizeFields() {
	}


	protected function initBackButton() {
		$this->setBackButtonName($this->txt("back", false));
		$this->setBackButtonTarget($this->ctrl->getLinkTarget($this->parent_gui, "index"));
	}


	protected function initTemplate() {
		$this->setTemplate(new ilTemplate("tpl.display.html", true, true, "Customizing/global/plugins/Libraries/ActiveRecord"));
	}


	/**
	 * @return string
	 */
	public function getHtml() {

		$this->getTemplate()->setVariable("TITLE", $this->title);
		$this->setArFieldsData();
		$this->getTemplate()->setVariable("BACK_BUTTON_NAME", $this->getBackButtonName());
		$this->getTemplate()->setVariable("BACK_BUTTON_TARGET", $this->getBackButtonTarget());

		return $this->getTemplate()->get();
	}


	protected function setArFieldsData() {
		foreach ($this->fields->getFields() as $field) {
			/**
			 * @var arDisplayField $field
			 */
			if ($field->getVisible()) {
				$get_function = $field->getGetFunctionName();
				$value = $this->ar->$get_function();
				$this->getTemplate()->setCurrentBlock("entry");
				$this->getTemplate()->setVariable("ITEM", $this->txt($field->getTxt()));
				$this->getTemplate()->setVariable("VALUE", $this->setArFieldData($field, $value));
				$this->getTemplate()->parseCurrentBlock();
			}
		}
	}


	/**
	 * @param arDisplayField $field
	 * @param                $value
	 *
	 * @return null|string
	 */
	protected function setArFieldData(arDisplayField $field, $value) {
		if ($field->getCustomField()) {
			return $this->setCustomFieldData($field);
		} else {
			if ($value == NULL) {
				return $this->setEmptyFieldData($field);
			} else {
				if ($field->getIsCreatedByField()) {
					return $this->setCreatedByData($field, $value);
				} else {
					if ($field->getIsModifiedByField()) {
						return $this->setModifiedByData($field, $value);
					} else {
						switch ($field->getFieldType()) {
							case 'integer':
							case 'float':
								return $this->setNumericData($field, $value);
							case 'text':
								return $this->setTextData($field, $value);
							case 'date':
							case 'time':
							case 'timestamp':
								return $this->setDateTimeData($field, $value);
							case 'clob':
								return $this->setClobData($field, $value);
						}
					}
				}
			}
		}
	}


	/**
	 * @param arDisplayField $field
	 *
	 * @return string
	 */
	protected function setEmptyFieldData(arDisplayField $field) {
		return $this->txt("", false);
	}


	/**
	 * @param arDisplayField $field
	 *
	 * @return string
	 */
	protected function setCustomFieldData(arDisplayField $field) {
		return "CUSTOM-OVERRIDE: setCustomFieldData";
	}


	/**
	 * @param arDisplayField $field
	 * @param                $value
	 *
	 * @return string
	 */
	protected function setModifiedByData(arDisplayField $field, $value) {
		$user = new ilObjUser($value);

		return $user->getPublicName();
	}


	/**
	 * @param arDisplayField $field
	 * @param                $value
	 *
	 * @return string
	 */
	protected function setCreatedByData(arDisplayField $field, $value) {
		$user = new ilObjUser($value);

		return $user->getPublicName();
	}


	/**
	 * @param arDisplayField $field
	 * @param                $value
	 *
	 * @return mixed
	 */
	protected function setNumericData(arDisplayField $field, $value) {
		return $value;
	}


	/**
	 * @param arDisplayField $field
	 * @param                $value
	 *
	 * @return mixed
	 */
	protected function setTextData(arDisplayField $field, $value) {
		return $value;
	}


	/**
	 * @param arDisplayField $field
	 * @param                $value
	 *
	 * @return string
	 */
	protected function setDateTimeData(arDisplayField $field, $value) {
		$datetime = new ilDateTime($value, IL_CAL_DATETIME);

		return ilDatePresentation::formatDate($datetime, IL_CAL_DATETIME);
	}


	/**
	 * @param arDisplayField $field
	 * @param                $value
	 *
	 * @return mixed
	 */
	protected function setClobData(arDisplayField $field, $value) {
		return $value;
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
	 * @param $back_button_target
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


	/**
	 * @param arDisplayFields $fields
	 */
	function setFields(arDisplayFields $fields) {
		$this->fields = $fields;
	}


	/**
	 * @return arDisplayFields
	 */
	public function getFields() {
		return $this->fields;
	}


	/**
	 * @return arDisplayField []
	 */
	public function getFieldsAsArray() {
		return $this->getFields()->getFields();
	}


	/**
	 * @param $field_name
	 *
	 * @return arDisplayField
	 */
	public function getField($field_name) {
		return $this->getFields()->getField($field_name);
	}


	/**
	 * @param arDisplayField
	 */
	public function addField(arDisplayField $field) {
		$this->getFields()->addField($field);
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
	 * @param \ilTemplate $template
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}


	/**
	 * @return \ilTemplate
	 */
	public function getTemplate() {
		return $this->template;
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
}