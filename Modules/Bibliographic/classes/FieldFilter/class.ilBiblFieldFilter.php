<?php
/**
 * Class ilBiblFieldFilter
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblFieldFilter extends ActiveRecord implements ilBiblFieldFilterInterface
{
    const TABLE_NAME = 'il_bibl_filter';


    /**
     * @return string
     */
    public static function returnDbTableName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @return string
     */
    public function getConnectorContainerName()
    {
        return self::TABLE_NAME;
    }


    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_primary true
     * @con_is_unique  true
     * @con_sequence   true
     */
    protected $id;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_unique  true
     */
    protected $field_id;
    /**
     * @var
     *
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     4
     * @con_is_notnull true
     * @con_is_unique  true
     */
    protected $object_id;
    /**
     * @var
     *
     * @con_has_field true
     * @con_fieldtype integer
     * @con_length    1
     */
    protected $filter_type;


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */
    public function getFieldId()
    {
        return $this->field_id;
    }


    /**
     * @param mixed $field_id
     */
    public function setFieldId($field_id)
    {
        $this->field_id = $field_id;
    }


    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->object_id;
    }


    /**
     * @param mixed $object_id
     */
    public function setObjectId($object_id)
    {
        $this->object_id = $object_id;
    }


    /**
     * @return mixed
     */
    public function getFilterType()
    {
        return $this->filter_type;
    }


    /**
     * @param mixed $filter_type
     */
    public function setFilterType($filter_type)
    {
        $this->filter_type = $filter_type;
    }
}
