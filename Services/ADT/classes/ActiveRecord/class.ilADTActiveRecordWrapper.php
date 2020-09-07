<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/ActiveRecord/classes/class.ActiveRecord.php";

/**
 * ADT Active Record service wrapper class
 *
 * :TODO: EXPERIMENTAL!
 *
 * This class expects a valid primary for all actions!
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
class ilADTActiveRecordWrapper extends ActiveRecord
{
    protected $properties; // [ilADTGroupActiveRecordBridge]
    protected $fields; // [arFieldList]
    protected $field_element_map = []; // [array]
    
    /**
     * Constructor
     *
     * @param ilADTGroupActiveRecordBridge $a_properties
     * @return self
     */
    public function __construct(ilADTGroupActiveRecordBridge $a_properties)
    {
        $this->properties = $a_properties;
        
        // see ActiveRecord::__construct();
        $this->initFieldList();
        $this->arConnector = new arConnectorDB();
    }
    
    
    //
    // active record field(s)
    //
    
    protected function getActiveRecordFieldTypeFromMDB2($a_mdb2_type)
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
                
            case "integer":
                return arField::FIELD_TYPE_INTEGER;
                
            /*
            case "clob":
                return arField::FIELD_TYPE_CLOB;

            case "time":
                return arField::FIELD_TYPE_TIME;
            */
        }
    }
    
    /**
     * Get field list instance for ADT group
     */
    protected function initFieldList()
    {
        require_once "Services/ActiveRecord/classes/Fields/class.arFieldList.php";
        $this->fields = new arFieldList();

        // element => fields

        $fields = array();
        foreach ($this->properties->getActiveRecordFields() as $element_id => $element_fields) {
            foreach ($element_fields as $field) {
                $this->field_element_map[$field->getName()] = $element_id;
            }

            $fields = array_merge($fields, $element_fields);
        }

        $this->fields->setFields($fields);

        // primary

        if (sizeof($this->properties->getPrimary()) > 1) {
            throw new ilException("ilADTActiveRecordWrapper - no complex primary keys supported yet");
        }

        foreach ($this->properties->getPrimary() as $primary_id => $primary_element) {
            $field = new arField();
            $field->setHasField(true);
            $field->setNotNull(true);
            $field->setFieldType($this->getActiveRecordFieldTypeFromMDB2($primary_element[0]));
            $field->setName($primary_id);
            $this->fields->setPrimaryField($field);
        }
    }
    
    
    //
    // active record meta (table/primary key)
    //
    
    public function getConnectorContainerName()
    {
        return $this->properties->getTableName();
    }
        
    public static function returnDbTableName()
    {
        // :TODO: cannot be static
    }
    
    public function getPrimaryFieldValue()
    {
        $primary = array_shift($this->properties->getPrimary());
        return $primary[1];
    }
    
    
    //
    // active record CRUD
    //
    
    public function sleep($field_name)
    {
        if (array_key_exists($field_name, $this->field_element_map)) {
            $element = $this->properties->getElement($this->field_element_map[$field_name]);
            return $element->getFieldValue($field_name);
        }
        
        // returning NULL would result in direct property access!
        return false;
    }

    public function wakeUp($field_name, $field_value)
    {
        if (array_key_exists($field_name, $this->field_element_map)) {
            $element = $this->properties->getElement($this->field_element_map[$field_name]);
            return $element->setFieldValue($field_name, $field_value);
        }
        
        // returning NULL would result in direct property access!
        return false;
    }
}
