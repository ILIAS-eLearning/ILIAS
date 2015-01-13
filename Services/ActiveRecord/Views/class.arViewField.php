<?php
include_once('./Customizing/global/plugins/Libraries/ActiveRecord/Fields/class.arField.php');

/**
 * GUI-Class arViewField
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 *
 */
class arViewField extends arField {

	/**
	 * @var string
	 */
	protected $txt_prefix = "";
	/**
	 * @var string
	 */
	protected $txt = "";
	/**
	 * @var int
	 */
	protected $position = 1000;
	/**
	 * @var bool
	 */
	protected $visible = false;
	/**
	 * @var bool
	 */
	protected $custom_field = false;
	/**
	 * @var string
	 */
	protected $get_function_name = "";
	/**
	 * @var string
	 */
	protected $set_function_name = "";
	/**
	 * @var bool
	 */
	protected $is_created_by_field = false;
	/**
	 * @var bool
	 */
	protected $is_modified_by_field = false;
	/**
	 * @var bool
	 */
	protected $is_creation_date_field = false;
	/**
	 * @var bool
	 */
	protected $is_modification_date_field = false;


	/**
	 * @param      $name
	 * @param null $txt
	 * @param int  $position
	 * @param bool $visible
	 * @param bool $custom_field
	 */
	function __construct($name, $txt = NULL, $position = 0, $visible = true, $custom_field = false) {
		$this->name = $name;
		$this->position = $position;
		$this->txt = $txt;
		$this->visible = $visible;
		$this->custom_field = $custom_field;

		$camel_case = ActiveRecord::_toCamelCase($this->getName(), true);
		$this->get_function_name = "get" . $camel_case;
		$this->set_function_name = "set" . $camel_case;
	}


	/**
	 * @param string $position
	 */
	public function setPosition($position) {
		$this->position = $position;
	}


	/**
	 * @return string
	 */
	public function getPosition() {
		return $this->position;
	}


	/**
	 * @param string $txt
	 */
	public function setTxt($txt) {
		$this->txt = $txt;
	}


	/**
	 * @return string
	 */
	public function getTxt() {
		if ($this->txt) {
			return $this->getTxtPrefix() . $this->txt;
		}

		return $this->getTxtPrefix() . $this->getName();
	}


	/**
	 * @param string $visible
	 */
	public function setVisible($visible) {
		$this->visible = $visible;
	}


	/**
	 * @return string
	 */
	public function getVisible() {
		return $this->visible;
	}


	/**
	 * @param boolean $custom_field
	 */
	public function setCustomField($custom_field) {
		$this->custom_field = $custom_field;
	}


	/**
	 * @return boolean
	 */
	public function getCustomField() {
		return $this->custom_field;
	}


	/**
	 * @param array $allowed_attributes
	 */
	public static function setAllowedAttributes($allowed_attributes) {
		self::$allowed_attributes = $allowed_attributes;
	}


	/**
	 * @return array
	 */
	public static function getAllowedAttributes() {
		return self::$allowed_attributes;
	}


	/**
	 * @param string $txt_prefix
	 */
	public function setTxtPrefix($txt_prefix) {
		$this->txt_prefix = $txt_prefix;
	}


	/**
	 * @return string
	 */
	public function getTxtPrefix() {
		return $this->txt_prefix;
	}


	/**
	 * @return string
	 */
	public function getGetFunctionName() {
		return $this->get_function_name;
	}


	/**
	 * @return string
	 */
	public function getSetFunctionName() {
		return $this->set_function_name;
	}


	/**
	 * @param boolean $is_created_by_field
	 */
	public function setIsCreatedByField($is_created_by_field) {
		$this->is_created_by_field = $is_created_by_field;
	}


	/**
	 * @param $is_creation_date_field
	 */
	public function setIsCreationDateField($is_creation_date_field) {
		$this->is_creation_date_field = $is_creation_date_field;
	}


	/**
	 * @return boolean
	 */
	public function getIsCreationDateField() {
		return $this->is_creation_date_field;
	}


	/**
	 * @return boolean
	 */
	public function getIsCreatedByField() {
		return $this->is_created_by_field;
	}


	/**
	 * @param boolean $is_modified_by_field
	 */
	public function setIsModifiedByField($is_modified_by_field) {
		$this->is_modified_by_field = $is_modified_by_field;
	}


	/**
	 * @return boolean
	 */
	public function getIsModifiedByField() {
		return $this->is_modified_by_field;
	}


	/**
	 * @param $is_modification_date_field
	 */
	public function setIsModificationDateField($is_modification_date_field) {
		$this->is_modification_date_field = $is_modification_date_field;
	}


	/**
	 * @return boolean
	 */
	public function getIsModificationDateField() {
		return $this->is_modification_date_field;
	}


	/**
	 * @param arField $field
	 *
	 * @return arViewField
	 */
	static function castFromFieldToViewField(arField $field) {
		require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Index/class.arIndexTableField.php');
		require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Edit/class.arEditField.php');
		require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Display/class.arDisplayField.php');

		$field_class = get_called_class();
		$obj = new $field_class($field->getName());
		foreach (get_object_vars($field) as $key => $name) {
			$obj->$key = $name;
		}

		return $obj;
	}
}