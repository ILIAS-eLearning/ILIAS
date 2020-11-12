<?php

/**
 * Class ilDclBaseTableViewDefaultValue
 *
 * @author  Jannik Dolf <jd@studer-raimann.ch>
 */
class ilDclTableViewNumberDefaultValue extends ilDclTableViewBaseDefaultValue
{
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           4
     * @db_sequence         true
     */
    protected $id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           4
     */
    protected $tview_set_id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_fieldtype        integer
     * @db_length           4
     */
    protected $value;


    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName()
    {
        return "il_dcl_stloc2_default";
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
    public function getTviewSetId()
    {
        return $this->tview_set_id;
    }


    /**
     * @param int $tview_set_id
     */
    public function setTviewSetId($tview_set_id)
    {
        $this->tview_set_id = $tview_set_id;
    }


    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param int $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}