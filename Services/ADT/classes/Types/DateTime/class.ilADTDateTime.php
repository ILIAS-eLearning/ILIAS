<?php

class ilADTDateTime extends ilADT
{
    protected $value; // [ilDateTime]

    
    // definition
    
    protected function isValidDefinition(ilADTDefinition $a_def)
    {
        return ($a_def instanceof ilADTDateTimeDefinition);
    }
    
    public function reset()
    {
        parent::reset();
        
        $this->value = null;
    }
    
    
    // properties
    
    public function setDate(ilDateTime $a_value = null)
    {
        if ($a_value && $a_value->isNull()) {
            $a_value = null;
        }
        $this->value = $a_value;
    }
    
    public function getDate()
    {
        return $this->value;
    }
    
    
    // comparison
    
    public function equals(ilADT $a_adt)
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            if (!$this->isNull() && !$a_adt->isNull()) {
                // could use checksum...
                $value = $this->getDate()->get(IL_CAL_UNIX);
                $other = $a_adt->getDate()->get(IL_CAL_UNIX);
                return ($value == $other);
            }
        }
        // null?
    }
                
    public function isLarger(ilADT $a_adt)
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            if (!$this->isNull() && !$a_adt->isNull()) {
                $value = $this->getDate()->get(IL_CAL_UNIX);
                $other = $a_adt->getDate()->get(IL_CAL_UNIX);
                return ($value > $other);
            }
        }
    }

    public function isSmaller(ilADT $a_adt)
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            if (!$this->isNull() && !$a_adt->isNull()) {
                $value = $this->getDate()->get(IL_CAL_UNIX);
                $other = $a_adt->getDate()->get(IL_CAL_UNIX);
                return ($value < $other);
            }
        }
    }
    
    
    // null
    
    public function isNull()
    {
        return (!($this->value instanceof ilDateTime) || $this->value->isNull());
    }
    
    
    // validation
    
    public function isValid()
    {
        $valid = parent::isValid();
        
        /* timestamp is "always" valid
        if(!$this->isNull())
        {
            $value = getdate($this->getDate()->get(IL_CAL_UNIX));
            if(!checkdate($value["mon"], $value["mday"], $value["year"]))
            {
                $valid = false;
                $this->addValidationError(self::ADT_VALIDATION_DATE);
            }
        }
        */
            
        return $valid;
    }
    
    
    // check
    
    public function getCheckSum()
    {
        if (!$this->isNull()) {
            return (string) $this->getDate()->get(IL_CAL_UNIX);
        }
    }
}
