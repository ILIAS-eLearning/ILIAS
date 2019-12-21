<?php

/**
 * Class ilDclTableFieldSetting
 *
 * defines table/field specific settings: field_order, editable, exportable
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableFieldSetting extends ActiveRecord
{

    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_sequence         true
     */
    protected $id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $table_id;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     *
     */
    protected $field;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected $field_order;
    /**
     * @var bool
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected $exportable;


    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName()
    {
        return "il_dcl_tfield_set";
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return int
     */
    public function getTableId()
    {
        return $this->table_id;
    }


    /**
     * @param int $table_id
     */
    public function setTableId($table_id)
    {
        $this->table_id = $table_id;
    }


    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }


    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }


    /**
     * @return int
     */
    public function getFieldOrder()
    {
        return $this->field_order;
    }


    /**
     * @param int $field_order
     */
    public function setFieldOrder($field_order)
    {
        $this->field_order = $field_order;
    }


    /**
     * @return boolean
     */
    public function isExportable()
    {
        return $this->exportable;
    }


    /**
     * @param boolean $exportable
     */
    public function setExportable($exportable)
    {
        $this->exportable = $exportable;
    }


    /**
     * @param $table_id
     * @param $field
     *
     * @return \ActiveRecord|\ilDclTableFieldSetting
     */
    public static function getInstance($table_id, $field)
    {
        $setting = self::where(array('table_id' => $table_id, 'field' => $field))->first();
        if (!$setting) {
            $setting = new self();
            $setting->setField($field);
            $setting->setTableId($table_id);
        }

        return $setting;
    }
}
