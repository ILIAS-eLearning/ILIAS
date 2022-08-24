<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class arField
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arField
{
    public const FIELD_TYPE_TEXT = 'text'; // MySQL varchar, char
    public const FIELD_TYPE_INTEGER = 'integer'; // MySQL tinyint, smallint, mediumint, int, bigint
    public const FIELD_TYPE_FLOAT = 'float'; // MySQL double
    public const FIELD_TYPE_DATE = 'date'; // MySQL date
    public const FIELD_TYPE_TIME = 'time'; // MySQL time
    public const FIELD_TYPE_TIMESTAMP = 'timestamp'; // MySQL datetime
    public const FIELD_TYPE_CLOB = 'clob';
    protected static array $allowed_attributes = array(
        self::FIELD_TYPE_TEXT => array(
            arFieldList::LENGTH,
            arFieldList::IS_NOTNULL,
            arFieldList::IS_PRIMARY,
        ),
        self::FIELD_TYPE_INTEGER => array(
            arFieldList::LENGTH,
            arFieldList::IS_NOTNULL,
            arFieldList::IS_PRIMARY,
            arFieldList::SEQUENCE,
        ),
        self::FIELD_TYPE_FLOAT => array(
            arFieldList::IS_NOTNULL,
        ),
        self::FIELD_TYPE_DATE => array(
            arFieldList::IS_NOTNULL,
        ),
        self::FIELD_TYPE_TIME => array(
            arFieldList::IS_NOTNULL,
        ),
        self::FIELD_TYPE_TIMESTAMP => array(
            arFieldList::IS_NOTNULL,
        ),
        self::FIELD_TYPE_CLOB => array(
            arFieldList::IS_NOTNULL,
        ),
    );
    protected static array $date_fields = array(
        self::FIELD_TYPE_DATE,
        self::FIELD_TYPE_TIME,
        self::FIELD_TYPE_TIMESTAMP
    );

    public function loadFromArray(string $name, array $array): void
    {
        $this->setName($name);
        foreach ($array as $key => $value) {
            switch ($value) {
                case 'true':
                    $this->{$key} = true;
                    break;
                case 'false':
                    $this->{$key} = false;
                    break;
                default:
                    $this->{$key} = $value;
                    break;
            }
        }
    }

    public function loadFromStdClass(string $name, stdClass $stdClass): void
    {
        $array = (array) $stdClass;
        $this->loadFromArray($name, $array);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getAttributesForConnector(): array
    {
        $return = array();
        foreach (arFieldList::getAllowedConnectorFields() as $field_name) {
            if (isset($this->{$field_name}) && $this->{$field_name} && self::isAllowedAttribute(
                $this->getFieldType(),
                $field_name
            )) {
                $return[arFieldList::mapKey($field_name)] = $this->{$field_name};
            }
        }

        return $return;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getAttributesForDescription(): array
    {
        $return = array();
        foreach (arFieldList::getAllowedDescriptionFields() as $field_name) {
            if ($this->{$field_name} && self::isAllowedAttribute($this->getFieldType(), $field_name)) {
                $return[arFieldList::mapKey($field_name)] = $this->{$field_name};
            }
        }

        return $return;
    }

    public function isDateField(): bool
    {
        return self::isDateFieldType($this->getFieldType());
    }

    /**
     * @var
     */
    protected string $fieldtype;
    protected ?int $length = null;
    protected bool $is_primary = false;
    protected string $name = '';
    protected bool $not_null = false;
    protected bool $has_field = false;
    protected bool $sequence = false;
    protected bool $index = false;

    public function setFieldType(string $field_type): void
    {
        $this->fieldtype = $field_type;
    }

    public function getFieldType(): string
    {
        return $this->fieldtype;
    }

    public function setHasField(bool $has_field): void
    {
        $this->has_field = $has_field;
    }

    public function getHasField(): bool
    {
        return $this->has_field;
    }

    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    public function getLength(): ?int
    {
        return $this->length;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setNotNull(bool $not_null): void
    {
        $this->not_null = $not_null;
    }

    public function getNotNull(): bool
    {
        return $this->not_null;
    }

    public function setPrimary(bool $primary): void
    {
        $this->is_primary = $primary;
    }

    public function getPrimary(): bool
    {
        return $this->is_primary;
    }

    public function setSequence(bool $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getSequence(): bool
    {
        return $this->sequence;
    }

    public function setIndex(bool $index): void
    {
        $this->index = $index;
    }

    public function getIndex(): bool
    {
        return $this->index;
    }

    public static function isAllowedAttribute(string $type, string $field_name): bool
    {
        if ($field_name === arFieldList::FIELDTYPE || $field_name === arFieldList::HAS_FIELD) {
            return true;
        }

        return in_array($field_name, self::$allowed_attributes[$type], true);
    }

    public static function isDateFieldType($field_type): bool
    {
        return in_array($field_type, self::$date_fields, true);
    }
}
