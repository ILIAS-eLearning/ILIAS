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

class ilDclDateFieldModel extends ilDclBaseFieldModel
{
    /**
     * @param string|int $filter_value
     */
    public function getRecordQueryFilterObject(
        $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ): ?ilDclRecordQueryObject {

        $date_from = (isset($filter_value['from']) && is_object($filter_value['from'])) ? $filter_value['from'] : null;
        $date_to = (isset($filter_value['to']) && is_object($filter_value['to'])) ? $filter_value['to'] : null;

        $join_str
            = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $this->db->quote($this->getId(), 'integer') . ") ";
        $join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id ";
        if ($date_from) {
            $join_str .= "AND filter_stloc_{$this->getId()}.value >= " . $this->db->quote($date_from, 'date') . " ";
        }
        if ($date_to) {
            $join_str .= "AND filter_stloc_{$this->getId()}.value <= " . $this->db->quote($date_to, 'date') . " ";
        }
        $join_str .= ") ";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);

        return $sql_obj;
    }

    protected function areEqual($value_1, $value_2): bool
    {
        if ($value_1 === null || $value_2 === null) {
            return false;
        }
        return date('Y-m-d', strtotime($value_1)) === date('Y-m-d', strtotime($value_2));
    }
}
