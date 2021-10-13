<?php

class ilADTBoolean extends ilADT
{
    protected $value; // [bool]
    
    
    // definition
    
    protected function isValidDefinition(ilADTDefinition $a_def) : bool
    {
        return ($a_def instanceof ilADTBooleanDefinition);
    }
    
    public function reset() : void
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
    
    public function equals(ilADT $a_adt) : ?bool
    {
        if ($this->isComparableTo($a_adt)) {
            return ($this->getStatus() === $a_adt->getStatus());
        }
        return null;
    }
                
    public function isLarger(ilADT $a_adt) : ?bool
    {
        return null;
    }

    public function isSmaller(ilADT $a_adt) : ?bool
    {
        return null;
    }
    
    
    // null
    
    public function isNull() : bool
    {
        return $this->getStatus() === null;
    }
    
    
    public function isValid() : bool
    {
        return true;
    }
    
    
    // check
    
    public function getCheckSum() : ?string
    {
        if (!$this->isNull()) {
            return (string) $this->getStatus();
        }
        return null;
    }
    
    
    // stdClass
    
    public function exportStdClass() : ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->value = $this->getStatus();
            return $obj;
        }
        return null;
    }
    
    public function importStdClass(?stdClass $a_std) : void
    {
        if (is_object($a_std)) {
            $this->setStatus($a_std->value);
        }
    }
}
