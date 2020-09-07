<?php

/**
 * Class ilDclBooleanFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclNumberFieldModel extends ilDclBaseFieldModel
{

    /**
     * Returns a query-object for building the record-loader-sql-query
     *
     * @param string $filter_value
     *
     * @return null|ilDclRecordQueryObject
     */
    public function getRecordQueryFilterObject($filter_value = "", ilDclBaseFieldModel $sort_field = null)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $from = (isset($filter_value['from'])) ? (int) $filter_value['from'] : null;
        $to = (isset($filter_value['to'])) ? (int) $filter_value['to'] : null;

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


    public function hasNumericSorting()
    {
        return true;
    }


    public function checkValidity($value, $record_id = null)
    {
        $valid = parent::checkValidity($value, $record_id);

        if (!is_numeric($value) && $value != '') {
            throw new ilDclInputException(ilDclInputException::TYPE_EXCEPTION);
        }

        return $valid;
    }
}
