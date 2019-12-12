<?php

require_once "Services/ADT/classes/Bridges/class.ilADTActiveRecordBridge.php";

class ilADTTextActiveRecordBridge extends ilADTActiveRecordBridge
{
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTText);
    }
    
    //
    // active record
    //
    
    public function getActiveRecordFields()
    {
        $def = $this->getADT()->getCopyOfDefinition();
        
        $field = new arField();
        $field->setHasField(true);
        $field->setNotNull(!$def->isNullAllowed());
        $field->setFieldType(arField::FIELD_TYPE_TEXT);
        $field->setName($this->getElementId());
        
        $max = $def->getMaxLength();
        if ($max !== null) {
            $field->setLength($max);
        }
        
        return array($field);
    }
        
    public function getFieldValue($a_field_name)
    {
        return $this->getADT()->getText();
    }
    
    public function setFieldValue($a_field_name, $a_field_value)
    {
        return $this->getADT()->setText($a_field_value);
    }
}
