<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclFieldProperty
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 *
 * @ingroup ModulesDataCollection
 */
class ilDclFieldProperty extends ActiveRecord
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
     *
     */
    protected $field_id;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $name;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $value;


    /**
     * ilDclFieldProperty constructor.
     *
     * @param int  $primary_key
     * @param null $connector
     */
    public function __construct($primary_key = 0, $connector = null)
    {
        parent::__construct($primary_key, $connector);
    }


    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName()
    {
        return "il_dcl_field_prop";
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @inheritdoc
     */
    public function create()
    {
        $this->value = $this->serializeData($this->value);
        parent::create();
    }


    /**
     * @inheritdoc
     */
    public function update()
    {
        $this->value = $this->serializeData($this->value);
        parent::update();
    }


    /**
     * @inheritdoc
     */
    public function afterObjectLoad()
    {
        $this->value = $this->deserializeData($this->value);
    }


    /**
     * Serialize data before storing to db
     *
     * @param $value mixed
     *
     * @return mixed
     */
    public function serializeData($value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }


    /**
     * Deserialize data before applying to field
     *
     * @param $value mixed
     *
     * @return mixed
     */
    public function deserializeData($value)
    {
        $deserialize = json_decode($value, true);
        if (is_array($deserialize)) {
            return $deserialize;
        }

        return $value;
    }
}
