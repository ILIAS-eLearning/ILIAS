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

	// those should be overwritten by subclasses
	const PROP_SELECTION_TYPE = '';
	const PROP_SELECTION_OPTIONS = '';

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
				$this->setProperty($property, $value);
			}
		}
	}


	/**
	 * @param ilPropertyFormGUI $form
	 *
	 * @return bool
	 */
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
				$options = ilDclSelectionOption::getAllForField($this->getId());
				$prop_values = array();
				foreach ($options as $option) {
					// the 'selection_value' is for a correct input
					$prop_values[$option->getOptId()] = array('selection_value' => $option->getValue());
				}

				$values['prop_' . $prop] = $prop_values;
			} else {
				$values['prop_' . $prop] = $this->getProperty($prop);
			}
		}

		$form->setValuesByArray($values);

		return true;
	}


	/**
	 * @param $key
	 * @param $value
	 */
	public function setProperty($key, $value) {
		switch ($key) {
			case static::PROP_SELECTION_OPTIONS:
				$sorting = 1;
				foreach ($value as $id => $val) {
					ilDclSelectionOption::storeOption($this->getId(), $id, $sorting, $val);
					$sorting++;
				}
				break;
			case static::PROP_SELECTION_TYPE:
				$will_be_multi = ($value == self::SELECTION_TYPE_MULTI);
				if ($this->isMulti() && !$will_be_multi || !$this->isMulti() && $will_be_multi) {
					$this->multiPropertyChanged($will_be_multi);
				}
				parent::setProperty($key, $value)->store();
				break;
			default:
				parent::setProperty($key, $value)->store();
		}
	}


	/**
	 * @param $new_value
	 */
	protected function multiPropertyChanged($is_multi_now) {
		foreach (ilDclCache::getTableCache($this->getTableId())->getRecords() as $record) {
			$record_field = $record->getRecordField($this->getId());
			$record_field_value = $record_field->getValue();

			if ($record_field_value && !is_array($record_field_value) && $is_multi_now) {
				$record_field->setValue(array($record_field_value));
				$record_field->doUpdate();
			}
			else if (is_array($record_field_value) && !$is_multi_now && (count($record_field_value) == 1)) {
				$record_field->setValue(array_shift($record_field_value));
				$record_field->doUpdate();
			}
		}
	}

	/**
	 * @param $key
	 *
	 * @return ilDclSelectionOption[]|null
	 */
	public function getProperty($key) {
		switch ($key) {
			case static::PROP_SELECTION_OPTIONS:
				return ilDclSelectionOption::getAllForField($this->getId());
				break;
			default:
				return parent::getProperty($key);
		}
	}


	/**
	 * @param string $direction
	 * @param bool   $sort_by_status
	 *
	 * @return ilDclRecordQueryObject
	 */
	public function getRecordQuerySortObject($direction = "asc", $sort_by_status = false) {
		global $DIC;
		$ilDB = $DIC['ilDB'];

		$sql_obj = new ilDclRecordQueryObject();

		$select_str = "sel_opts_{$this->getId()}.value AS field_{$this->getId()}";
		$join_str = "LEFT JOIN il_dcl_record_field AS sort_record_field_{$this->getId()} ON (sort_record_field_{$this->getId()}.record_id = record.id AND sort_record_field_{$this->getId()}.field_id = "
			. $ilDB->quote($this->getId(), 'integer') . ") ";
		$join_str .= "LEFT JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS sort_stloc_{$this->getId()} ON (sort_stloc_{$this->getId()}.record_field_id = sort_record_field_{$this->getId()}.id) ";
		$join_str .= "LEFT JOIN il_dcl_sel_opts as sel_opts_{$this->getId()} ON (sel_opts_{$this->getId()}.opt_id = sort_stloc_{$this->getId()}.value AND sel_opts_{$this->getId()}.field_id = " . $ilDB->quote($this->getId(), 'integer') . ") ";

		$sql_obj->setSelectStatement($select_str);
		$sql_obj->setJoinStatement($join_str);
		$sql_obj->setOrderStatement("field_{$this->getId()} {$direction}");

		return $sql_obj;
	}


	public function cloneProperties(ilDclBaseFieldModel $originalField) {
		parent::cloneProperties($originalField);
		$options = ilDclSelectionOption::getAllForField($originalField->getId());
		foreach ($options as $opt) {
			$new_opt = new ilDclSelectionOption();
			$new_opt->cloneOption($opt);
			$new_opt->setFieldId($this->getId());
			$new_opt->store();
		}
	}


	/**
	 *
	 */
	public function doDelete() {
		foreach (ilDclSelectionOption::getAllForField($this->getId()) as $option) {
			$option->delete();
		}
		parent::doDelete();
	}
}