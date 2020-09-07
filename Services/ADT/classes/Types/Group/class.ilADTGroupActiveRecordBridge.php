<?php

require_once "Services/ADT/classes/Bridges/class.ilADTActiveRecordBridge.php";

class ilADTGroupActiveRecordBridge extends ilADTActiveRecordBridge
{
    protected $elements = []; // [array]
    
    protected function isValidADT(ilADT $a_adt)
    {
        return ($a_adt instanceof ilADTGroup);
    }
    
    
    // elements
    
    protected function prepareElements()
    {
        if (sizeof($this->elements)) {
            return;
        }
        
        $this->elements = array();
        $factory = ilADTFactory::getInstance();
        
        // convert ADTs to ActiveRecord bridges
        
        foreach ($this->getADT()->getElements() as $name => $element) {
            $this->elements[$name] = $factory->getActiveRecordBridgeForInstance($element);
            $this->elements[$name]->setElementId($name);
        }
    }
    
    public function getElements()
    {
        $this->prepareElements();
        return $this->elements;
    }

    public function getElement($a_element_id)
    {
        if (array_key_exists($a_element_id, $this->getElements())) {
            return $this->elements[$a_element_id];
        }
    }
    
    //
    // active record
    //
    
    public function getActiveRecordFields()
    {
        $fields = array();
        foreach ($this->getElements() as $element_id => $element) {
            $element_fields = $element->getActiveRecordFields();
            if ($element_fields) {
                $fields[$element_id] = $element_fields;
            }
        }
        return $fields;
    }
}
