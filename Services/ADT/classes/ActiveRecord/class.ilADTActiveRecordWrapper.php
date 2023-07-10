<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT Active Record service wrapper class
 * This class expects a valid primary for all actions!
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTActiveRecordWrapper extends ActiveRecord
{
    protected ilADTGroupActiveRecordBridge $properties;
    protected arFieldList $fields;
    protected arConnectorDB $arConnector;
    protected array $field_element_map = [];

    /**
     * Constructor
     * @param ilADTGroupActiveRecordBridge $a_properties
     */
    public function __construct(ilADTGroupActiveRecordBridge $a_properties)
    {
        $this->properties = $a_properties;
        $this->initFieldList();
        $this->arConnector = new arConnectorDB();
    }

    protected function getActiveRecordFieldTypeFromMDB2(string $a_mdb2_type): ?string
    {
        // currently they are all the same
        switch ($a_mdb2_type) {
            case "integer":
                return arField::FIELD_TYPE_INTEGER;

            case "float":
                return arField::FIELD_TYPE_FLOAT;

            case "text":
                return arField::FIELD_TYPE_TEXT;

            case "date":
                return arField::FIELD_TYPE_DATE;

            case "timestamp":
                return arField::FIELD_TYPE_TIMESTAMP;
        }
        return null;
    }

    protected function initFieldList(): void
    {
        $this->fields = new arFieldList($this);

        $fields = [];
        foreach ($this->properties->getActiveRecordFields() as $element_id => $element_fields) {
            foreach ($element_fields as $field) {
                $this->field_element_map[$field->getName()] = $element_id;
            }
            $fields = array_merge($fields, $element_fields);
        }
        $this->fields->setFields($fields);

        // primary

        if (count($this->properties->getPrimary()) > 1) {
            throw new ilException("ilADTActiveRecordWrapper - no complex primary keys supported yet");
        }

        foreach ($this->properties->getPrimary() as $primary_id => $primary_element) {
            $field = new arField();
            $field->setHasField(true);
            $field->setNotNull(true);
            $field->setFieldType($this->getActiveRecordFieldTypeFromMDB2($primary_element[0] ?? ''));
            $field->setName($primary_id);
            $this->fields->setPrimaryField($field);
        }
    }

    public function getConnectorContainerName(): string
    {
        return $this->properties->getTable();
    }

    public static function returnDbTableName(): string
    {
        throw new \RuntimeException('Not implemented yet');
    }

    public function getPrimaryFieldValue(): string
    {
        $primaries = $this->properties->getPrimary();
        $primary = array_shift($primaries);
        return $primary[1];
    }

    /**
     * @todo types extended from ActiveRecord
     */
    public function sleep($field_name)
    {
        if (array_key_exists($field_name, $this->field_element_map)) {
            $element = $this->properties->getElement($this->field_element_map[$field_name]);
            return $element->getFieldValue($field_name);
        }
        return false;
    }


    /**
     * @todo types extended from ActiveRecord
     */
    public function wakeUp($field_name, $field_value)
    {
        if (array_key_exists($field_name, $this->field_element_map)) {
            $element = $this->properties->getElement($this->field_element_map[$field_name]);
            $element->setFieldValue($field_name, $field_value);
            return true;
        }
        return false;
    }
}
