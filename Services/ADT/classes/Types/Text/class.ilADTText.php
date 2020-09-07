<?php

class ilADTText extends ilADT
{
    protected $value; // [string]

    
    // definition
    
    protected function isValidDefinition(ilADTDefinition $a_def)
    {
        return ($a_def instanceof ilADTTextDefinition);
    }
    
    public function reset()
    {
        parent::reset();
        
        $this->value = null;
    }
    
    
    // properties
    
    public function setText($a_value = null)
    {
        if ($a_value !== null) {
            $a_value = trim($a_value);
        }
        $this->value = $a_value;
    }
    
    public function getText()
    {
        return $this->value;
    }
    
    public function getLength()
    {
        // see ilStr::strLen();
        // not using ilStr to reduce dependencies in this low-level code
        
        if (function_exists("mb_strlen")) {
            return mb_strlen($this->getText(), "UTF-8");
        } else {
            return strlen($this->getText());
        }
    }
    
    
    // comparison
    
    public function equals(ilADT $a_adt)
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return !strcmp($this->getText(), $a_adt->getText());
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
        return !(bool) $this->getLength();
    }
    
    
    // validation
    
    public function isValid()
    {
        $valid = parent::isValid();
        
        if (!$this->isNull()) {
            $max = $this->getDefinition()->getMaxLength();
            if ($max && $max < $this->getLength()) {
                $valid = false;
                $this->addValidationError(self::ADT_VALIDATION_ERROR_MAX_LENGTH);
            }
        }
            
        return $valid;
    }
    
    
    // check
    
    public function getCheckSum()
    {
        if (!$this->isNull()) {
            return md5($this->getText());
        }
    }
}
