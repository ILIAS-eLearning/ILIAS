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
 *********************************************************************/

declare(strict_types=1);

abstract class ilDclSelectionFieldModel extends ilDclBaseFieldModel
{
    public const string SELECTION_TYPE_SINGLE = 'selection_type_single';
    public const string SELECTION_TYPE_MULTI = 'selection_type_multi';
    public const string SELECTION_TYPE_COMBOBOX = 'selection_type_combobox';
    public const PROP_SELECTION_TYPE = '';
    public const PROP_SELECTION_OPTIONS = '';

    public function getValidFieldProperties(): array
    {
        return [$this::PROP_SELECTION_OPTIONS, $this::PROP_SELECTION_TYPE, $this::PROP_UNIQUE];
    }

    public function getRecordQueryFilterObject(
        $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ): ?ilDclRecordQueryObject {

        $join_str
            = " LEFT JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $this->db->quote($this->getId(), 'integer') . ") ";

        $join_str .= " LEFT JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id";

        $where_str = " AND ";
        if ($filter_value == 'none') {
            $where_str .= "("
                . "filter_stloc_{$this->getId()}.value IS NULL "
                . " OR filter_stloc_{$this->getId()}.value = " . $this->db->quote("", 'text')
                . " OR filter_stloc_{$this->getId()}.value = " . $this->db->quote("[]", 'text')
                . ") ";
        } else {
            if ($this->isMulti()) {
                $where_str .= " (" .
                    "filter_stloc_{$this->getId()}.value LIKE " . $this->db->quote("%\"$filter_value\"%", 'text') .
                    ") ";
            } else {
                $where_str .= "filter_stloc_{$this->getId()}.value = "
                    . $this->db->quote($filter_value, 'integer');
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
        return ($this->getProperty($this::PROP_SELECTION_TYPE) === $this::SELECTION_TYPE_MULTI);
    }

    /**
     * @param array $value
     */
    public function setProperty(string $key, $value): ?ilDclFieldProperty
    {
        if ($key === $this::PROP_SELECTION_OPTIONS) {
            ilDclSelectionOption::flushOptions((int) $this->getId());
            $sorting = 1;
            foreach ($value as $id => $val) {
                ilDclSelectionOption::storeOption((int) $this->getId(), $id, $sorting, $val);
                $sorting++;
            }
            return null;
        }
        return parent::setProperty($key, $value);
    }

    /**
     * @return ilDclSelectionOption[]|ilDclFieldProperty|null
     */
    public function getProperty(string $key): mixed
    {
        if ($key == $this::PROP_SELECTION_OPTIONS) {
            $prop_values = [];
            foreach (ilDclSelectionOption::getAllForField((int) $this->getId()) as $option) {
                $prop_values[$option->getOptId()] = $option->getValue();
            }

            return $prop_values;
        }
        return parent::getProperty($key);
    }

    public function cloneProperties(ilDclBaseFieldModel $originalField): void
    {
        parent::cloneProperties($originalField);
        $options = ilDclSelectionOption::getAllForField((int) $originalField->getId());
        foreach ($options as $opt) {
            $new_opt = new ilDclSelectionOption();
            $new_opt->cloneOption($opt);
            $new_opt->setFieldId((int) $this->getId());
            $new_opt->store();
        }
    }

    public function doDelete(): void
    {
        foreach (ilDclSelectionOption::getAllForField((int) $this->getId()) as $option) {
            $option->delete();
        }
        parent::doDelete();
    }
}
