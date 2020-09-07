<?php

class ilADTBoolean extends ilADT
{
    protected $value; // [bool]
    
    
    // definition
    
    protected function isValidDefinition(ilADTDefinition $a_def)
    {
        return ($a_def instanceof ilADTBooleanDefinition);
    }
    
    public function reset()
    {
        parent::reset();
        
        $this->value = null;
    }
    
    
    // properties
    
    public function setStatus($a_value = null)
    {
        if ($a_value !== null) {
            $a_value = (bool) $a_value;
        }
        $this->value = $a_value;
    }
    
    public function getStatus()
    {
        return $this->value;
    }
    
    
    // comparison
    
    public function equals(ilADT $a_adt)
    {
        if ($this->isComparableTo($a_adt)) {
            return ($this->getStatus() === $a_adt->getStatus());
        }
    }
                
    public function isLarger(ilADT $a_adt)
    {
        // return null?
    }

    public function isSmaller(ilADT $a_adt)
    {
        // return null?
    }
    
    
    // null
    
    public function isNull()
    {
        return ($this->getStatus() === null);
    }
    
    
    // validation
    
    public function isValid()
    {
        return true;
    }
    
    
    // check
    
    public function getCheckSum()
    {
        if (!$this->isNull()) {
            return (string) $this->getStatus();
        }
    }
}
