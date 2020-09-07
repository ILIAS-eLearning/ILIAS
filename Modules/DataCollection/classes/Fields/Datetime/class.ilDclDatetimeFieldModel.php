<?php

/**
 * Class ilDclBooleanFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclDatetimeFieldModel extends ilDclBaseFieldModel
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

        $date_from = (isset($filter_value['from']) && is_object($filter_value['from'])) ? $filter_value['from'] : null;
        $date_to = (isset($filter_value['to']) && is_object($filter_value['to'])) ? $filter_value['to'] : null;

        $join_str
            = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";
        $join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id ";
        if ($date_from) {
            $join_str .= "AND filter_stloc_{$this->getId()}.value >= " . $ilDB->quote($date_from, 'date') . " ";
        }
        if ($date_to) {
            $join_str .= "AND filter_stloc_{$this->getId()}.value <= " . $ilDB->quote($date_to, 'date') . " ";
        }
        $join_str .= ") ";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);

        return $sql_obj;
    }


    public function checkValidity($value, $record_id = null)
    {
        if ($value == null) {
            return true;
        }

        if ($this->isUnique()) {
            $table = ilDclCache::getTableCache($this->getTableId());
            $datestring = $value . ' 00:00:00';
            foreach ($table->getRecords() as $record) {
                if ($record->getRecordFieldValue($this->getId()) == $datestring && ($record->getId() != $record_id || $record_id == 0)) {
                    throw new ilDclInputException(ilDclInputException::UNIQUE_EXCEPTION);
                }
            }
        }
    }
}
