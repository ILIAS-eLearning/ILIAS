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
 * Class arFieldList
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arFieldList
{
    public const HAS_FIELD = 'has_field';
    public const IS_PRIMARY = 'is_primary';
    public const IS_NOTNULL = 'is_notnull';
    public const FIELDTYPE = 'fieldtype';
    public const LENGTH = 'length';
    public const SEQUENCE = 'sequence';
    public const INDEX = 'index';
    protected static array $prefixes = array('db', 'con');
    protected static array $protected_names = array('arConnector', 'arFieldList');
    protected static array $allowed_description_fields = array(
        'is_unique', // There are many classes which already use this (without any function)
        self::IS_PRIMARY,
        self::IS_NOTNULL,
        self::FIELDTYPE,
        self::LENGTH,
        self::SEQUENCE,
        self::INDEX
    );
    protected static array $allowed_connector_fields = array(
        self::IS_NOTNULL,
        self::FIELDTYPE,
        self::LENGTH,
    );
    /**
     * @var arField|array
     */
    protected $primary_field;
    protected array $primary_fields = array();
    protected array $raw_fields = array();
    protected array $fields = array();
    protected \ActiveRecord $ar;
    protected static array $key_maps = array(
        self::FIELDTYPE => 'type',
        self::IS_NOTNULL => 'notnull',
    );

    /**
     * arFieldList constructor.
     */
    public function __construct(ActiveRecord $ar)
    {
        $this->ar = $ar;
    }

    public static function mapKey(string $key): string
    {
        if (isset(self::$key_maps[$key])) {
            return self::$key_maps[$key];
        }

        return $key;
    }


    public static function getAllowedConnectorFields(): array
    {
        return self::$allowed_connector_fields;
    }

    public static function getAllowedDescriptionFields(): array
    {
        return self::$allowed_description_fields;
    }

    public static function getInstance(ActiveRecord $ar): \arFieldList
    {
        $arFieldList = new self($ar);
        $arFieldList->initRawFields($ar);
        $arFieldList->initFields();

        return $arFieldList;
    }

    /**
     * @deprecated
     */
    public static function getInstanceFromStorage(\ActiveRecord $ar): \arFieldList
    {
        $arFieldList = new self($ar);
        $arFieldList->initRawFields($ar);
        $arFieldList->initFields();

        return $arFieldList;
    }


    public function getArrayForConnector(): array
    {
        $return = array();
        foreach ($this->getFields() as $field) {
            $return[$field->getName()] = $field->getAttributesForConnector();
        }

        return $return;
    }

    protected function initFields(): void
    {
        foreach ($this->getRawFields() as $fieldname => $attributes) {
            if (self::checkAttributes($attributes)) {
                $arField = new arField();
                $arField->loadFromArray($fieldname, $attributes);
                $this->fields[] = $arField;
                if ($arField->getPrimary()) {
                    $this->setPrimaryField($arField);
                }
            }
        }
    }

    public function getFieldByName(string $field_name): ?arField
    {
        $field = null;
        static $field_map;
        $field_key = $this->ar->getConnectorContainerName() . '.' . $field_name;
        if (is_array($field_map) && array_key_exists($field_key, $field_map)) {
            return $field_map[$field_key];
        }
        foreach ($this->getFields() as $field) {
            if ($field->getName() === $field_name) {
                $field_map[$field_key] = $field;

                return $field;
            }
        }
        return null;
    }


    public function isField(string $field_name): bool
    {
        $is_field = false;
        foreach ($this->getFields() as $field) {
            if ($field->getName() === $field_name) {
                $is_field = true;
            }
        }

        return $is_field;
    }

    public function getPrimaryFieldName(): string
    {
        return $this->getPrimaryField()->getName();
    }

    public function getPrimaryFieldType(): string
    {
        return $this->getPrimaryField()->getFieldType();
    }

    protected function initRawFields(ActiveRecord $ar): void
    {
        $regex = "/[ ]*\\* @(" . implode('|', self::$prefixes) . ")_([a-zA-Z0-9_]*)[ ]*([a-zA-Z0-9_]*)/u";
        $reflectionClass = new ReflectionClass($ar);
        $raw_fields = array();
        foreach ($reflectionClass->getProperties() as $property) {
            if (in_array($property->getName(), self::$protected_names)) {
                continue;
            }
            $properties_array = array();
            $has_property = false;
            foreach (explode("\n", $property->getDocComment()) as $line) {
                if (preg_match($regex, $line, $matches)) {
                    $has_property = true;
                    $properties_array[(string) $matches[2]] = $matches[3];
                }
            }
            if ($has_property) {
                $raw_fields[$property->getName()] = $properties_array;
            }
        }

        $this->setRawFields($raw_fields);
    }

    protected static function isAllowedAttribute(string $attribute_name): bool
    {
        return in_array($attribute_name, array_merge(self::$allowed_description_fields, array(self::HAS_FIELD)), true);
    }

    protected static function checkAttributes(array $attributes): bool
    {
        if (isset($attributes[self::HAS_FIELD]) && $attributes[self::HAS_FIELD] === 'true') {
            foreach (array_keys($attributes) as $atr) {
                if (!self::isAllowedAttribute($atr)) {
                    return false;
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param \arField[] $fields
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @return arField[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function setPrimaryField(\arField $primary_field): void
    {
        $this->primary_field = $primary_field;
    }

    /**
     * @return \arField|mixed[]
     */
    public function getPrimaryField()
    {
        return $this->primary_field;
    }

    /**
     * @param mixed[] $raw_fields
     */
    public function setRawFields(array $raw_fields): void
    {
        $this->raw_fields = $raw_fields;
    }

    public function getRawFields(): array
    {
        return $this->raw_fields;
    }

    public function setPrimaryFields(array $primary_fields): void
    {
        $this->primary_fields = $primary_fields;
    }

    public function getPrimaryFields(): array
    {
        return $this->primary_fields;
    }
}
