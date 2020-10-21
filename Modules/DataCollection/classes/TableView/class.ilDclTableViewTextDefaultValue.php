<?php

/**
 * Class ilDclTableViewTextDefaultValue
 *
 * @author  Jannik Dolf <jd@studer-raimann.ch>
 */
class ilDclTableViewTextDefaultValue extends ilDclTableViewBaseDefaultValue
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
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           4000
     */
    protected $value;


    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName()
    {
        return "il_dcl_stloc1_default";
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
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}