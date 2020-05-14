<?php

/**
 * Class ilDclReferenceFieldModel
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclReferenceFieldModel extends ilDclBaseFieldModel
{
    const PROP_REFERENCE = 'table_id';
    const PROP_N_REFERENCE = 'multiple_selection';


    /**
     * Returns a query-object for building the record-loader-sql-query
     *
     * @param string  $direction
     * @param boolean $sort_by_status The specific sort object is a status field
     *
     * @return null|ilDclRecordQueryObject
     */
    public function getRecordQuerySortObject($direction = "asc", $sort_by_status = false)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        if ($this->hasProperty(self::PROP_N_REFERENCE)) {
            return null;
        }

        $ref_field = ilDclCache::getFieldCache($this->getProperty(self::PROP_REFERENCE));

        $select_str = "stloc_{$this->getId()}_joined.value AS field_{$this->getId()},";
        $join_str = "LEFT JOIN il_dcl_record_field AS record_field_{$this->getId()} ON (record_field_{$this->getId()}.record_id = record.id AND record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";
        $join_str .= "LEFT JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS stloc_{$this->getId()} ON (stloc_{$this->getId()}.record_field_id = record_field_{$this->getId()}.id) ";
        $join_str .= "LEFT JOIN il_dcl_record_field AS record_field_{$this->getId()}_joined ON (record_field_{$this->getId()}_joined.record_id = stloc_{$this->getId()}.value AND record_field_{$this->getId()}_joined.field_id = "
            . $ilDB->quote($ref_field->getId(), 'integer') . ") ";
        $join_str .= "LEFT JOIN il_dcl_stloc{$ref_field->getStorageLocation()}_value AS stloc_{$this->getId()}_joined ON (stloc_{$this->getId()}_joined.record_field_id = record_field_{$this->getId()}_joined.id) ";

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

        $n_ref = $this->getProperty(ilDclBaseFieldModel::PROP_N_REFERENCE);

        $join_str
            = " INNER JOIN il_dcl_record_field AS filter_record_field_{$this->getId()} ON (filter_record_field_{$this->getId()}.record_id = record.id AND filter_record_field_{$this->getId()}.field_id = "
            . $ilDB->quote($this->getId(), 'integer') . ") ";

        if ($n_ref) {
            $join_str
                .= " INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id AND filter_stloc_{$this->getId()}.value LIKE "
                . $ilDB->quote("%$filter_value%", 'text') . ") ";
        } else {
            $join_str
                .= " INNER JOIN il_dcl_stloc{$this->getStorageLocation()}_value AS filter_stloc_{$this->getId()} ON (filter_stloc_{$this->getId()}.record_field_id = filter_record_field_{$this->getId()}.id AND filter_stloc_{$this->getId()}.value = "
                . $ilDB->quote($filter_value, 'integer') . ") ";
        }

        $sql_obj = new ilDclRecordQueryObject();
        $sql_obj->setJoinStatement($join_str);

        return $sql_obj;
    }


    /**
     * @inheritDoc
     */
    public function getValidFieldProperties()
    {
        return array(ilDclBaseFieldModel::PROP_REFERENCE, ilDclBaseFieldModel::PROP_REFERENCE_LINK, ilDclBaseFieldModel::PROP_N_REFERENCE);
    }


    /**
     * @return bool
     */
    public function allowFilterInListView()
    {
        //A reference-field is not filterable if the referenced field is of datatype MOB or File
        $ref_field = $this->getFieldRef();

        return !($ref_field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_MOB
            || $ref_field->getDatatypeId() == ilDclDatatype::INPUTFORMAT_FILE);
    }


    public function getFieldRef()
    {
        return ilDclCache::getFieldCache((int) $this->getProperty(ilDclBaseFieldModel::PROP_REFERENCE));
    }


    public function afterClone($records)
    {
        /** @var ilDclReferenceFieldModel $clone */
        $clone = ilDclCache::getCloneOf($this->getId(), ilDclCache::TYPE_FIELD);
        $reference_clone = ilDclCache::getCloneOf((int) $clone->getProperty(ilDclBaseFieldModel::PROP_REFERENCE), ilDclCache::TYPE_FIELD);
        if ($reference_clone) {
            $this->setProperty(ilDclBaseFieldModel::PROP_REFERENCE, $reference_clone->getId());
            $this->updateProperties();
        }
        parent::afterClone($records);
    }
}
