<?php

/**
 * Class ilDclTableFieldSetting
 * defines table/field specific settings: field_order, editable, exportable
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclTableFieldSetting extends ActiveRecord
{

    /**
     * @var int
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_sequence         true
     */
    protected int $id;
    /**
     * @var int
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $table_id;
    /**
     * @var string
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected string $field;
    /**
     * @var int
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           8
     */
    protected int $field_order;
    /**
     * @var bool
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           1
     */
    protected bool $exportable;

    /**
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName() : string
    {
        return "il_dcl_tfield_set";
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function getTableId() : int
    {
        return $this->table_id;
    }

    public function setTableId(int $table_id)
    {
        $this->table_id = $table_id;
    }

    public function getField() : string
    {
        return $this->field;
    }

    public function setField(string $field)
    {
        $this->field = $field;
    }

    public function getFieldOrder() : int
    {
        return $this->field_order;
    }

    public function setFieldOrder(int $field_order)
    {
        $this->field_order = $field_order;
    }

    public function isExportable() : bool
    {
        return $this->exportable;
    }

    public function setExportable(bool $exportable)
    {
        $this->exportable = $exportable;
    }

    /**
     * @return ActiveRecord|ilDclTableFieldSetting
     */
    public static function getInstance(int $table_id, string $field) : ActiveRecord
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
