<?php

/**
 * Class ilDclBooleanFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclIliasReferenceFieldModel extends ilDclBaseFieldModel
{

    /**
     * Returns a query-object for building the record-loader-sql-query
     *
     * @return null|ilDclRecordQueryObject
     */
    public function getRecordQuerySortObject($direction = "asc", $sort_by_status = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $join_str
            = "LEFT JOIN il_dcl_record_field AS sort_record_field_{$this->getId()} ON (sort_record_field_{$this->getId()}.record_id = record.id AND sort_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";
        $join_str .= "LEFT JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS sort_stloc_{$this->getId()} ON (sort_stloc_{$this->getId()}.record_field_id = sort_record_field_{$this->getId()}.id) ";
        $join_str .= "LEFT JOIN object_reference AS sort_object_reference_{$this->getId()} ON (sort_object_reference_{$this->getId()}.ref_id = sort_stloc_{$this->getId()}.value AND sort_object_reference_{$this->getId()}.deleted IS NULL)";
        $join_str .= "LEFT JOIN object_data AS sort_object_data_{$this->getId()} ON (sort_object_data_{$this->getId()}.obj_id = sort_object_reference_{$this->getId()}.obj_id)";

        if ($sort_by_status) {
            global $DIC;
            $ilUser = $DIC['ilUser'];
            $join_str .= "LEFT JOIN ut_lp_marks AS ut ON (ut.obj_id = sort_object_data_{$this->getId()}.obj_id AND ut.usr_id = "
                . $ilDB->quote($ilUser->getId(), 'integer') . ") ";
        }

        $select_str = (!$sort_by_status) ? " sort_object_data_{$this->getId()}.title AS field_{$this->getId()}," : " ut.status AS field_{$this->getId()}";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setSelectStatement($select_str);
        $sql_obj->setJoinStatement($join_str);
        $sql_obj->setOrderStatement("field_{$this->getId()} " . $direction);

        return $sql_obj;
    }


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

        $join_str
            = "INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";
        $join_str .= "INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id) ";
        $join_str .= "INNER JOIN object_reference AS filter_object_reference_{$this->getId()} ON (filter_object_reference_{$this->getId()}.ref_id = filter_stloc_{$this->getId()}.value ) ";
        $join_str .= "INNER JOIN object_data AS filter_object_data_{$this->getId()} ON (filter_object_data_{$this->getId()}.obj_id = filter_object_reference_{$this->getId()}.obj_id AND filter_object_data_{$this->getId()}.title LIKE "
            . $ilDB->quote("%$filter_value%", 'text') . ") ";

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);

        return $sql_obj;
    }


    /**
     * @inheritDoc
     */
    public function getValidFieldProperties()
    {
        return array(ilDclBaseFieldModel::PROP_LEARNING_PROGRESS, ilDclBaseFieldModel::PROP_ILIAS_REFERENCE_LINK, ilDclBaseFieldModel::PROP_DISPLAY_COPY_LINK_ACTION_MENU);
    }
}
