<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclSelectionOption
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclSelectionOption extends ActiveRecord
{
    const DB_TABLE_NAME = "il_dcl_sel_opts";


    public static function returnDbTableName()
    {
        return self::DB_TABLE_NAME;
    }


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
     *
     */
    protected $field_id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     *
     */
    protected $opt_id;
    /**
     * @var int
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     *
     */
    protected $sorting;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $value;


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
    public function getFieldId()
    {
        return $this->field_id;
    }


    /**
     * @param int $field_id
     */
    public function setFieldId($field_id)
    {
        $this->field_id = $field_id;
    }


    /**
     * @return int
     */
    public function getOptId()
    {
        return (int) $this->opt_id;
    }


    /**
     * @param int $opt_id
     */
    public function setOptId($opt_id)
    {
        $this->opt_id = $opt_id;
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


    /**
     * @return int
     */
    public function getSorting()
    {
        return $this->sorting;
    }


    /**
     * @param int $sorting
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;
    }


    /**
     * @param $field_id
     * @param $opt_id
     * @param $sorting
     * @param $value
     */
    public static function storeOption($field_id, $opt_id, $sorting, $value)
    {
        /** @var ilDclSelectionOption $option */
        $option = self::where(array("field_id" => $field_id, "opt_id" => $opt_id))->first();
        if (!$option) {
            $option = new self();
        }
        $option->setFieldId($field_id);
        $option->setOptId($opt_id);
        $option->setSorting($sorting);
        $option->setValue($value);
        $option->store();
    }


    /**
     * @param $field_id
     */
    public static function flushOptions($field_id)
    {
        foreach (self::getAllForField($field_id) as $option) {
            $option->delete();
        }
    }


    /**
     * @param $field_id
     *
     * @return self[]
     */
    public static function getAllForField($field_id)
    {
        return self::where(array("field_id" => $field_id))->orderBy('sorting')->get();
    }


    public static function getValues($field_id, $opt_ids)
    {
        $operators = array('field_id' => '=');
        if (is_array($opt_ids)) {
            if (empty($opt_ids)) {
                return array();
            }
            $operators['opt_id'] = 'IN';
        } else {
            $operators['opt_id'] = '=';
        }
        $return = array();
        foreach (self::where(array("field_id" => $field_id, "opt_id" => $opt_ids), $operators)->orderBy('sorting')->get() as $opt) {
            $return[] = $opt->getValue();
        }

        return $return;
    }


    /**
     * @param ilDclSelectionOption $original_option
     */
    public function cloneOption(ilDclSelectionOption $original_option)
    {
        $this->setValue($original_option->getValue());
        $this->setSorting($original_option->getSorting());
        $this->setOptId($original_option->getOptId());
    }
}
