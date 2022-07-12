<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

/**
 * Class ilDclFieldProperty
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @ingroup ModulesDataCollection
 */
class ilDclFieldProperty extends ActiveRecord
{

    /**
     * @var ?int
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        integer
     * @db_length           8
     * @db_sequence         true
     */
    protected ?int $id;
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
    protected string $name;
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
    public function __construct(?int $primary_key = 0)
    {
        parent::__construct($primary_key);
    }

    /**
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

    public function setId(int $id) : void
    {
        $this->id = $id;
    }

    public function getFieldId() : int
    {
        return $this->field_id;
    }

    public function setFieldId(int $field_id) : void
    {
        $this->field_id = $field_id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : void
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
    public function setValue($value) : void
    {
        $this->value = $value;
    }

    public function create() : void
    {
        $this->value = $this->serializeData($this->value);
        parent::create();
    }

    public function update() : void
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
