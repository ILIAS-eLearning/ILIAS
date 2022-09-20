<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * Class ilDclSelectionFieldModel
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ilDclSelectionFieldModel extends ilDclBaseFieldModel
{
    public const SELECTION_TYPE_SINGLE = 'selection_type_single';
    public const SELECTION_TYPE_MULTI = 'selection_type_multi';
    public const SELECTION_TYPE_COMBOBOX = 'selection_type_combobox';
    // those should be overwritten by subclasses
    public const PROP_SELECTION_TYPE = '';
    public const PROP_SELECTION_OPTIONS = '';

    public function getValidFieldProperties(): array
    {
        return array(static::PROP_SELECTION_OPTIONS, static::PROP_SELECTION_TYPE);
    }

    /**
     * Returns a query-object for building the record-loader-sql-query
     * @param string|int $filter_value
     */
    public function getRecordQueryFilterObject(
        $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ): ?ilDclRecordQueryObject {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $join_str
            = " LEFT JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";

        $join_str .= " LEFT JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id";

        $where_str = " AND ";
        if ($filter_value == 'none') {
            $where_str .= "("
                . "filter_stloc_{$this->getId()}.value IS NULL "
                . " OR filter_stloc_{$this->getId()}.value = " . $ilDB->quote("", 'text')
                . " OR filter_stloc_{$this->getId()}.value = " . $ilDB->quote("[]", 'text')
                . ") ";
        } else {
            if ($this->isMulti()) {
                $where_str .= " (" .
                    "filter_stloc_{$this->getId()}.value = " . $ilDB->quote("[$filter_value]", 'text') . " OR " .
                    "filter_stloc_{$this->getId()}.value LIKE " . $ilDB->quote("%\"$filter_value\"%", 'text') . " OR " .
                    "filter_stloc_{$this->getId()}.value LIKE " . $ilDB->quote("%,$filter_value,%", 'text') . " OR " .
                    "filter_stloc_{$this->getId()}.value LIKE " . $ilDB->quote("%[$filter_value,%", 'text') . " OR " .
                    "filter_stloc_{$this->getId()}.value LIKE " . $ilDB->quote("%,$filter_value]%", 'text') .
                    ") ";
                ;
            } else {
                $where_str .= "filter_stloc_{$this->getId()}.value = "
                    . $ilDB->quote($filter_value, 'integer');
            }
        }

        $join_str .= ") ";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);
        $sql_obj->setWhereStatement($where_str);

        return $sql_obj;
    }

    public function isMulti(): bool
    {
        return ($this->getProperty(static::PROP_SELECTION_TYPE) == self::SELECTION_TYPE_MULTI);
    }

    /**
     * called when saving the 'edit field' form
     * @throws ilDclException
     */
    public function storePropertiesFromForm(ilPropertyFormGUI $form): void
    {
        $representation = ilDclFieldFactory::getFieldRepresentationInstance($this);

        $field_props = $this->getValidFieldProperties();
        foreach ($field_props as $property) {
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
            if (!empty($value) || ($this->getPropertyInstance($property) != null && $property != self::PROP_PLUGIN_HOOK_NAME)) {
                $this->setProperty($property, $value);
            }
        }
    }

    /**
     * @param ilPropertyFormGUI $form
     * @return bool
     */
    public function fillPropertiesForm(ilPropertyFormGUI &$form): bool
    {
        $values = array(
            'table_id' => $this->getTableId(),
            'field_id' => $this->getId(),
            'title' => $this->getTitle(),
            'datatype' => $this->getDatatypeId(),
            'description' => $this->getDescription(),
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
     * @param array $value
     */
    public function setProperty(string $key, $value): ?ilDclFieldProperty
    {
        $is_update = $this->getProperty($key);
        switch ($key) {
            case static::PROP_SELECTION_OPTIONS:

                ilDclSelectionOption::flushOptions($this->getId());
                $sorting = 1;
                foreach ($value as $id => $val) {
                    ilDclSelectionOption::storeOption($this->getId(), $id, $sorting, $val);
                    $sorting++;
                }
                // if the field is not being created reorder the options in the existing record fields
                if ($is_update) {
                    $this->reorderExistingValues();
                }
                break;
            case static::PROP_SELECTION_TYPE:
                $will_be_multi = ($value == self::SELECTION_TYPE_MULTI);
                // if the "Multi" property has changed, adjust the record field values
                if ($is_update && ($this->isMulti() && !$will_be_multi || !$this->isMulti() && $will_be_multi)) {
                    $this->multiPropertyChanged($will_be_multi);
                }
                parent::setProperty($key, $value)->store();
                break;
            default:
                parent::setProperty($key, $value)->store();
        }

        return null;
    }

    /**
     * sorts record field values by the new order
     */
    public function reorderExistingValues(): void
    {
        $options = ilDclSelectionOption::getAllForField($this->getId());
        // loop each record(-field)
        foreach (ilDclCache::getTableCache($this->getTableId())->getRecords() as $record) {
            $record_field = $record->getRecordField($this->getId());
            $record_field_value = $record_field->getValue();

            if (is_array($record_field_value) && count($record_field_value) > 1) {
                $sorted_array = array();
                // $options has the right order, so loop those
                foreach ($options as $option) {
                    if (in_array($option->getOptId(), $record_field_value)) {
                        $sorted_array[] = $option->getOptId();
                    }
                }
                $record_field->setValue($sorted_array);
                $record_field->doUpdate();
            }
        }
    }

    /**
     * changes the values of all record fields, since the property "multi" has changed
     */
    protected function multiPropertyChanged(bool $is_multi_now): void
    {
        foreach (ilDclCache::getTableCache($this->getTableId())->getRecords() as $record) {
            $record_field = $record->getRecordField($this->getId());
            $record_field_value = $record_field->getValue();

            if ($record_field_value && !is_array($record_field_value) && $is_multi_now) {
                $record_field->setValue(array($record_field_value));
                $record_field->doUpdate();
            } else {
                if (is_array($record_field_value) && !$is_multi_now) {
                    $record_field->setValue(array_shift($record_field_value));
                    $record_field->doUpdate();
                }
            }
        }
    }

    /**
     * @param $key
     * @return ilDclSelectionOption[]|ilDclFieldProperty|null
     */
    public function getProperty(string $key)
    {
        switch ($key) {
            case static::PROP_SELECTION_OPTIONS:
                return ilDclSelectionOption::getAllForField($this->getId());
                break;
            default:
                return parent::getProperty($key);
        }
    }

    public function getRecordQuerySortObject(
        string $direction = "asc",
        bool $sort_by_status = false
    ): ?ilDclRecordQueryObject {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($this->isMulti()) {
            return null;
        }

        $sql_obj = new ilDclRecordQueryObject();

        $select_str = "sel_opts_{$this->getId()}.value AS field_{$this->getId()}";
        $join_str
            = "LEFT JOIN il_dcl_record_field AS sort_record_field_{$this->getId()} ON (sort_record_field_{$this->getId()}.record_id = record.id AND sort_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";
        $join_str .= "LEFT JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS sort_stloc_{$this->getId()} ON (sort_stloc_{$this->getId()}.record_field_id = sort_record_field_{$this->getId()}.id) ";
        $join_str .= "LEFT JOIN il_dcl_sel_opts as sel_opts_{$this->getId()} ON (sel_opts_{$this->getId()}.opt_id = sort_stloc_{$this->getId()}.value AND sel_opts_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";

        $sql_obj->setSelectStatement($select_str);
        $sql_obj->setJoinStatement($join_str);
        $sql_obj->setOrderStatement("field_{$this->getId()} {$direction}");

        return $sql_obj;
    }

    public function cloneProperties(ilDclBaseFieldModel $originalField): void
    {
        parent::cloneProperties($originalField);
        $options = ilDclSelectionOption::getAllForField($originalField->getId());
        foreach ($options as $opt) {
            $new_opt = new ilDclSelectionOption();
            $new_opt->cloneOption($opt);
            $new_opt->setFieldId($this->getId());
            $new_opt->store();
        }
    }

    public function doDelete(): void
    {
        foreach (ilDclSelectionOption::getAllForField($this->getId()) as $option) {
            $option->delete();
        }
        parent::doDelete();
    }

    public function isConfirmationRequired(ilPropertyFormGUI $form): bool
    {
        $will_be_multi = ($form->getInput('prop_' . static::PROP_SELECTION_TYPE) == self::SELECTION_TYPE_MULTI);

        return $this->isMulti() && !$will_be_multi;
    }

    public function getConfirmationGUI(ilPropertyFormGUI $form): ilConfirmationGUI
    {
        global $DIC;
        $representation = ilDclFieldFactory::getFieldRepresentationInstance($this);
        $prop_selection_options = $representation->getPropertyInputFieldId(static::PROP_SELECTION_OPTIONS);
        $prop_selection_type = $representation->getPropertyInputFieldId(static::PROP_SELECTION_TYPE);

        $ilConfirmationGUI = parent::getConfirmationGUI($form);
        $ilConfirmationGUI->setHeaderText($DIC->language()->txt('dcl_msg_mc_to_sc_confirmation'));
        $ilConfirmationGUI->addHiddenItem($prop_selection_type, $form->getInput($prop_selection_type));
        foreach ($form->getInput($prop_selection_options) as $key => $option) {
            $ilConfirmationGUI->addHiddenItem(
                $prop_selection_options . "[$key][selection_value]",
                $option['selection_value']
            );
        }

        return $ilConfirmationGUI;
    }
}
