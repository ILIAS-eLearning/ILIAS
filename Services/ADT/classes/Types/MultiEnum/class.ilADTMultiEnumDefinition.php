<?php

class ilADTMultiEnumDefinition extends ilADTDefinition
{
    protected $options = []; // [array]
    protected $numeric; // [bool]
    
    
    // default
    
    public function reset()
    {
        parent::reset();
        
        $this->options = array();
        $this->setNumeric(true);
    }
    
    
    // properties
    
    public function getOptions()
    {
        return $this->options;
    }
    
    public function setOptions(array $a_values)
    {
        if ($this->isNumeric()) {
            foreach (array_keys($a_values) as $key) {
                if (!is_numeric($key)) {
                    throw new Exception("ilADTMultiEnum was expecting numeric option keys");
                }
            }
        }
        
        $this->options = $a_values;
    }
    
    public function isNumeric()
    {
        return $this->numeric;
    }
    
    public function setNumeric($a_value)
    {
        $this->numeric = $a_value;
    }
    
    
    // comparison
        
    public function isComparableTo(ilADT $a_adt)
    {
        // has to be text-based
        return ($a_adt instanceof ilADTMultiEnum);
    }
    
    
    // ADT instance
    
    public function getADTInstance()
    {
        if ($this->isNumeric()) {
            $class = "ilADTMultiEnumNumeric";
        } else {
            $class = "ilADTMultiEnumText";
        }
        include_once "Services/ADT/classes/Types/MultiEnum/class.ilADTMultiEnum.php";
        include_once "Services/ADT/classes/Types/MultiEnum/class." . $class . ".php";
        return new $class($this);
    }
}
