<?php
include_once('./Customizing/global/plugins/Libraries/ActiveRecord/Fields/class.arField.php');
include_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/class.arViewField.php');

/**
 * GUI-Class arViewFields
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 *
 */
class arViewFields {

	const FIELD_CLASS = 'arViewField';
	/**
	 * @var arViewField[]
	 */
	protected $fields = array();
	/**
	 * @var arViewField[]
	 */
	protected $fields_for_display = NULL;
	/**
	 * @var ActiveRecord
	 */
	protected $active_record = NULL;
	/**
	 * @var string
	 */
	protected $txt_prefix = "";
	/**
	 * @var arViewField
	 */
	protected $created_by_field = NULL;
	/**
	 * @var arViewField
	 */
	protected $modified_by_field = NULL;
	/**
	 * @var arViewField
	 */
	protected $creation_date_field = NULL;
	/**
	 * @var arViewField
	 */
	protected $modification_date_field = NULL;


	/**
	 * @param ActiveRecord $ar
	 */
	public function __construct(ActiveRecord $ar) {
		$this->active_record = $ar;
		$this->generateFields();
	}


	/**
	 * @return bool
	 */
	protected function generateFields() {
		$fields = $this->active_record->getArFieldList()->getFields();
		foreach ($fields as $standard_field) {
			$current_class = get_called_class();
			$field_class = $current_class::FIELD_CLASS;
			/**
			 * @var arViewField $field_class
			 */
			$field = $field_class::castFromFieldToViewField($standard_field);
			$this->addField($field);
		}

		return true;
	}


	/**
	 * @param arViewField
	 */
	public function addField(arViewField $field) {
		$this->fields[$field->getName()] = $field;
	}


	/**
	 * @return arViewField[]
	 */
	public function getFields() {
		return $this->fields;
	}


	/**
	 * @return arViewField
	 */
	public function getPrimaryField() {
		return $this->getField(arFieldCache::getPrimaryFieldName($this->active_record));
	}


	/**
	 * @return bool
	 */
	public function sortFields() {
		uasort($this->fields, function (arViewField $field_a, arViewField $field_b) {
			return $field_a->getPosition() > $field_b->getPosition();
		});
	}


	/**
	 * @return arViewField[]
	 */
	public function getFieldsForDisplay() {
		if (!$this->fields_for_display && $this->getFields()) {
			foreach ($this->getFields() as $field) {
				/**
				 * @var $field arViewField
				 */
				if (($field->getVisible() || $field->getPrimary())) {
					$this->fields_for_display[] = $field;
				}
			}
		}

		return $this->fields_for_display;
	}


	/**
	 * @param $field_name
	 *
	 * @return arViewField
	 */
	public function getField($field_name) {
		return $this->fields[$field_name];
	}


	/**
	 * @param string $txt_prefix
	 */
	public function setTxtPrefix($txt_prefix) {
		$this->txt_prefix = $txt_prefix;
		foreach ($this->getFields() as $field) {
			$field->setTxtPrefix($txt_prefix);
		}
	}


	/**
	 * @return string
	 */
	public function getTxtPrefix() {
		return $this->txt_prefix;
	}


	/**
	 * @param \arViewField $created_by_field
	 */
	public function setCreatedByField($created_by_field) {
		$created_by_field->setIsCreatedByField(true);
		$this->created_by_field = $created_by_field;
	}


	/**
	 * @return \arViewField
	 */
	public function getCreatedByField() {
		if (!$this->created_by_field) {
			foreach ($this->getFields() as $field) {
				/**
				 * @var $field arViewField
				 */
				if ($field->getIsCreatedByField()) {
					return $this->created_by_field = $field;
				}
			}
		}

		return $this->created_by_field;
	}


	/**
	 * @param \arViewField $creation_date_field
	 */
	public function setCreationDateField($creation_date_field) {
		$creation_date_field->setIsCreationDateField(true);
		$this->creation_date_field = $creation_date_field;
	}


	/**
	 * @return \arViewField
	 */
	public function getCreationDateField() {
		if (!$this->creation_date_field) {
			foreach ($this->getFields() as $field) {
				/**
				 * @var $field arViewField
				 */
				if ($field->getIsCreationDateField()) {
					return $this->creation_date_field = $field;
				}
			}
		}

		return $this->creation_date_field;
	}


	/**
	 * @param \arViewField $modification_date_field
	 */
	public function setModificationDateField($modification_date_field) {
		$modification_date_field->setIsModificationDateField(true);
		$this->modification_date_field = $modification_date_field;
	}


	/**
	 * @return \arViewField
	 */
	public function getModificationDateField() {
		if (!$this->modification_date_field) {
			foreach ($this->getFields() as $field) {
				/**
				 * @var $field arViewField
				 */
				if ($field->getIsModificationDateField()) {
					return $this->modification_date_field = $field;
				}
			}
		}

		return $this->modification_date_field;
	}


	/**
	 * @param \arViewField $modified_by_field
	 */
	public function setModifiedByField($modified_by_field) {
		$modified_by_field->setIsModifiedByField(true);
		$this->modified_by_field = $modified_by_field;
	}


	/**
	 * @return \arViewField
	 */
	public function getModifiedByField() {
		if (!$this->modified_by_field) {
			foreach ($this->getFields() as $field) {
				/**
				 * @var $field arViewField
				 */
				if ($field->getIsModifiedByField()) {
					return $this->modified_by_field = $field;
				}
			}
		}

		return $this->modified_by_field;
	}
}