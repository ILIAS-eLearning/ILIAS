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
 *********************************************************************/

/**
 * Class arField
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arField
{
    protected bool $is_unique = false;
    public const FIELD_TYPE_TEXT = 'text'; // MySQL varchar, char
    public const FIELD_TYPE_INTEGER = 'integer'; // MySQL tinyint, smallint, mediumint, int, bigint
    public const FIELD_TYPE_FLOAT = 'float'; // MySQL double
    public const FIELD_TYPE_DATE = 'date'; // MySQL date
    public const FIELD_TYPE_TIME = 'time'; // MySQL time
    public const FIELD_TYPE_TIMESTAMP = 'timestamp'; // MySQL datetime
    public const FIELD_TYPE_CLOB = 'clob';
    protected static array $allowed_attributes = [
        self::FIELD_TYPE_TEXT => [
            arFieldList::LENGTH,
            arFieldList::IS_NOTNULL,
            arFieldList::IS_PRIMARY
        ],
        self::FIELD_TYPE_INTEGER => [
            arFieldList::LENGTH,
            arFieldList::IS_NOTNULL,
            arFieldList::IS_PRIMARY,
            arFieldList::SEQUENCE
        ],
        self::FIELD_TYPE_FLOAT => [arFieldList::IS_NOTNULL],
        self::FIELD_TYPE_DATE => [arFieldList::IS_NOTNULL],
        self::FIELD_TYPE_TIME => [arFieldList::IS_NOTNULL],
        self::FIELD_TYPE_TIMESTAMP => [arFieldList::IS_NOTNULL],
        self::FIELD_TYPE_CLOB => [arFieldList::IS_NOTNULL]
    ];
    protected static array $date_fields = [self::FIELD_TYPE_DATE, self::FIELD_TYPE_TIME, self::FIELD_TYPE_TIMESTAMP];

    public function loadFromArray(string $name, array $array): void
    {
        $this->setName($name);
        foreach ($array as $key => $value) {
            $this->{$key} = match ($value) {
                'true' => true,
                'false' => false,
                default => $value,
            };
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
        $return = [];
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
        $return = [];
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

    protected string $fieldtype;
    protected ?int $length = null;
    protected bool $is_primary = false;
    protected string $name = '';
    protected bool $is_notnull = false;
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
        if ($field_name === arFieldList::FIELDTYPE) {
            return true;
        }
        if ($field_name === arFieldList::HAS_FIELD) {
            return true;
        }
        return in_array($field_name, self::$allowed_attributes[$type], true);
    }

    public static function isDateFieldType($field_type): bool
    {
        return in_array($field_type, self::$date_fields, true);
    }
}
