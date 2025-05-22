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

class ilDclCopyFieldModel extends ilDclBaseFieldModel
{
    public function getRecordQueryFilterObject(
        $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ): ?ilDclRecordQueryObject {
        $join_str
            = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $this->db->quote($this->getId(), 'integer') . ") ";
        $join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id AND filter_stloc_{$this->getId()}.value LIKE "
            . $this->db->quote("%$filter_value%", 'text') . ") ";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);

        return $sql_obj;
    }

    public function getValidFieldProperties(): array
    {
        return [
            ilDclBaseFieldModel::PROP_REFERENCE,
            ilDclBaseFieldModel::PROP_N_REFERENCE
        ];
    }

    public function getPresentationTitle(): string
    {
        return $this->lng->txt('dcl_copy_field');
    }

    public function getPresentationDescription(): string
    {
        return $this->lng->txt('dcl_copy_field_desc');
    }
}
