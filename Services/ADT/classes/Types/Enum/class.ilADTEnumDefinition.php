<?php

class ilADTEnumDefinition extends ilADTDefinition
{
    protected $options = []; // [array]
    protected $numeric; // [bool]
    
    public function getType()
    {
        return "Enum";
    }
    
    
    // default
    
    public function reset()
    {
        parent::reset();
        
        $this->options = array();
        $this->setNumeric(true);
    }
    
    
    // properties
        
    public function isNumeric()
    {
        return $this->numeric;
    }
    
    public function setNumeric($a_value)
    {
        $this->numeric = $a_value;
    }
    
    public function getOptions()
    {
        return $this->options;
    }
    
    public function setOptions(array $a_values)
    {
        if ($this->isNumeric()) {
            foreach (array_keys($a_values) as $key) {
                if (!is_numeric($key)) {
                    throw new Exception("ilADTEnum was expecting numeric option keys");
                }
            }
        }
        
        $this->options = $a_values;
    }

    
    // comparison
        
    public function isComparableTo(ilADT $a_adt)
    {
        // has to be enum-based
        return ($a_adt instanceof ilADTEnum);
    }
    
    
    // ADT instance
    
    public function getADTInstance()
    {
        if ($this->isNumeric()) {
            $class = "ilADTEnumNumeric";
        } else {
            $class = "ilADTEnumText";
        }
        include_once "Services/ADT/classes/Types/Enum/class.ilADTEnum.php";
        include_once "Services/ADT/classes/Types/Enum/class." . $class . ".php";
        return new $class($this);
    }
}
