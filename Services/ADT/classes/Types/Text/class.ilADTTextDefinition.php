<?php

class ilADTTextDefinition extends ilADTDefinition
{
    protected $max_length; // [int]
    
    
    // properties
    
    public function getMaxLength()
    {
        return $this->max_length;
    }
    
    public function setMaxLength($a_value)
    {
        $a_value = (int) $a_value;
        if ($a_value < 1) {
            $a_value = null;
        }
        $this->max_length = $a_value;
    }
    
    
    // comparison
        
    public function isComparableTo(ilADT $a_adt)
    {
        // has to be text-based
        return ($a_adt instanceof ilADTText);
    }
}
