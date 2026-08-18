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

class ilDclNumberFieldModel extends ilDclBaseFieldModel
{
    public function getRecordQueryFilterObject(
        mixed $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ): ?ilDclRecordQueryObject {
        if (is_array($filter_value)) {
            $from = (isset($filter_value['from'])) ? (int) $filter_value['from'] : null;
            $to = (isset($filter_value['to'])) ? (int) $filter_value['to'] : null;
        }

        $join_str
            = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $this->db->quote($this->getId(), 'integer') . ") ";
        $join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id";
        if (isset($from)) {
            $join_str .= " AND filter_stloc_{$this->getId()}.value >= " . $this->db->quote($from, 'integer');
        }
        if (isset($to)) {
            $join_str .= " AND filter_stloc_{$this->getId()}.value <= " . $this->db->quote($to, 'integer');
        }
        $join_str .= ") ";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);

        return $sql_obj;
    }

    public function hasNumericSorting(): bool
    {
        return true;
    }

    public function checkValidity(mixed $value, ?int $record_id): bool
    {
        if (!is_numeric($value) && $value !== null) {
            throw new ilDclInputException(ilDclInputException::TYPE_EXCEPTION);
        }
        return parent::checkValidity((int) $value, $record_id);
    }
}
