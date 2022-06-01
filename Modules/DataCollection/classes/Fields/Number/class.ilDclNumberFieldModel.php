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
class ilDclNumberFieldModel extends ilDclBaseFieldModel
{

    /**
     * Returns a query-object for building the record-loader-sql-query
     * @param array|string $filter_value
     */
    public function getRecordQueryFilterObject(
        $filter_value = "",
        ?ilDclBaseFieldModel $sort_field = null
    ) : ?ilDclRecordQueryObject {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if (is_array($filter_value)) {
            $from = (isset($filter_value['from'])) ? (int) $filter_value['from'] : null;
            $to = (isset($filter_value['to'])) ? (int) $filter_value['to'] : null;
        }

        $join_str
            = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";
        $join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id";
        if (!is_null($from)) {
            $join_str .= " AND filter_stloc_{$this->getId()}.value >= " . $ilDB->quote($from, 'integer');
        }
        if (!is_null($to)) {
            $join_str .= " AND filter_stloc_{$this->getId()}.value <= " . $ilDB->quote($to, 'integer');
        }
        $join_str .= ") ";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);

        return $sql_obj;
    }

    public function hasNumericSorting() : bool
    {
        return true;
    }

    /**
     * @param float $value
     */
    public function checkValidity($value, ?int $record_id = null) : bool
    {
        $valid = parent::checkValidity($value, $record_id);

        if (!is_numeric($value) && $value != '') {
            throw new ilDclInputException(ilDclInputException::TYPE_EXCEPTION);
        }

        return $valid;
    }
}
