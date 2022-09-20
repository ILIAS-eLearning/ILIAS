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
 * Class ilDclBooleanFieldModel
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclBooleanFieldModel extends ilDclBaseFieldModel
{
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
            = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ")";
        if ($filter_value == "checked") {
            $join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id";
            $join_str .= " AND filter_stloc_{$this->getId()}.value = " . $ilDB->quote(1, 'integer');
        } else {
            $join_str .= "LEFT JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id";
            $where_additions = " AND (filter_stloc_{$this->getId()}.value <> " . $ilDB->quote(1, 'integer')
                . " OR filter_stloc_{$this->getId()}.value is NULL)";
        }
        $join_str .= " ) ";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);
        $sql_obj->setWhereStatement($where_additions);

        return $sql_obj;
    }
}
