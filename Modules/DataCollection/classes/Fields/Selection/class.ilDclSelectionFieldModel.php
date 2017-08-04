<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilDclSelectionFieldModel
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ilDclSelectionFieldModel extends ilDclBaseFieldModel {

	const SELECTION_TYPE_SINGLE = 'selection_type_single';
	const SELECTION_TYPE_MULTI = 'selection_type_multi';
	const SELECTION_TYPE_COMBOBOX = 'selection_type_combobox';

	/**
	 * @inheritDoc
	 */
	public function getValidFieldProperties() {
		return array(static::PROP_SELECTION_OPTIONS, static::PROP_SELECTION_TYPE);
	}

	/**
	 * Returns a query-object for building the record-loader-sql-query
	 *
	 * @param string $filter_value
	 *
	 * @return null|ilDclRecordQueryObject
	 */
	public function getRecordQueryFilterObject($filter_value = "", ilDclBaseFieldModel $sort_field = null) {
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$join_str =
			" INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
			. $ilDB->quote($this->getId(), 'integer') . ") ";

		if ($this->isMulti()) {
			$join_str .=
				" INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id AND filter_stloc_{$this->getId()}.value LIKE "
				. $ilDB->quote("%$filter_value%", 'text') . ") ";
		} else {
			$join_str .=
				" INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id AND filter_stloc_{$this->getId()}.value = "
				. $ilDB->quote($filter_value, 'integer') . ") ";
		}

		$sql_obj = new ilDclRecordQueryObject();
		$sql_obj->setJoinStatement($join_str);

		return $sql_obj;
	}

	public function isMulti() {
		return ($this->getProperty(static::PROP_SELECTION_TYPE) == self::SELECTION_TYPE_MULTI);
	}

	/**
	 * called when saving the 'edit field' form
	 *
	 * @param ilPropertyFormGUI $form
	 */
	public function storePropertiesFromForm(ilPropertyFormGUI $form) {
		$field_props = $this->getValidFieldProperties();
		foreach ($field_props as $property) {
			$representation = ilDclFieldFactory::getFieldRepresentationInstance($this);
			$value = $form->getInput($representation->getPropertyInputFieldId($property));

			// break down the multidimensional array from the multi input
			// e.g.: { [0] => { [0] => 'x' }, [1] => { [1] => 'y' } }    TO    { [0] => 'x', [1] => 'y' }
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					if (is_array($v)) {
						$value[$k] = array_shift($v);
					}
				}
			}

			// save non empty values and set them to null, when they already exist. Do not override plugin-hook when already set.
			if(!empty($value) || ($this->getPropertyInstance($property) != NULL && $property != self::PROP_PLUGIN_HOOK_NAME)) {
				$this->setProperty($property, $value)->store();
			}
		}
	}


	public function fillPropertiesForm(ilPropertyFormGUI &$form) {
		$values = array(
			'table_id' => $this->getTableId(),
			'field_id' => $this->getId(),
			'title' => $this->getTitle(),
			'datatype' => $this->getDatatypeId(),
			'description' => $this->getDescription(),
			'required' => $this->getRequired(),
			'unique' => $this->isUnique(),
		);

		$properties = $this->getValidFieldProperties();
		foreach ($properties as $prop) {
			if ($prop == static::PROP_SELECTION_OPTIONS) {
				$prop_values = $this->getProperty($prop);
				foreach ($prop_values as $k => $v) {
					$prop_values[$k] = array('selection_value' => $v);
				}
				$values['prop_' . $prop] = $prop_values;
			} else {
				$values['prop_' . $prop] = $this->getProperty($prop);
			}
		}

		$form->setValuesByArray($values);

		return true;
	}
}