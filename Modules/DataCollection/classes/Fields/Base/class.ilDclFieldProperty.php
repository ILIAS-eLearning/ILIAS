<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclFieldProperty
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclFieldProperty extends ActiveRecord
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
    protected int $field_id;
    /**
     * @var string
     * @db_has_field        true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $name;
    /**
     * @var string
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected string $value;

    /**
     * ilDclFieldProperty constructor.
     */
    public function __construct(int $primary_key = 0)
    {
        parent::__construct($primary_key);
    }

    /**
     * @return string
     * @description Return the Name of your Database Table
     */
    public static function returnDbTableName() : string
    {
        return "il_dcl_field_prop";
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getFieldId() : int
    {
        return $this->field_id;
    }

    public function setFieldId(int $field_id)
    {
        $this->field_id = $field_id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string|array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|array|int $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function create() : void
    {
        $this->value = $this->serializeData($this->value);
        parent::create();
    }

    public function update()
    {
        $this->value = $this->serializeData($this->value);
        parent::update();
    }

    public function afterObjectLoad() : void
    {
        $this->value = $this->deserializeData($this->value);
    }

    /**
     * Serialize data before storing to db
     * @param int|string|array $value
     */
    public function serializeData($value) : string
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        return $value;
    }

    /**
     * Deserialize data before applying to field
     * @param $value mixed
     * @return string|array
     */
    public function deserializeData(string $value)
    {
        $deserialize = json_decode($value, true);
        if (is_array($deserialize)) {
            return $deserialize;
        }

        return $value;
    }
}
