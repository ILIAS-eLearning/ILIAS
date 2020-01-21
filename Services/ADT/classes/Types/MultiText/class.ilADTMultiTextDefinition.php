<?php

class ilADTMultiTextDefinition extends ilADTDefinition
{
    protected $max_length; // [int]
    protected $max_size; // [int]
    
    
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
    
    public function getMaxSize()
    {
        return $this->max_size;
    }
    
    public function setMaxSize($a_value)
    {
        $a_value = (int) $a_value;
        if ($a_value < 1) {
            $a_value = null;
        }
        $this->max_size = $a_value;
    }
    
    
    
    // comparison
        
    public function isComparableTo(ilADT $a_adt)
    {
        // has to be text-based
        return ($a_adt instanceof ilADTMultiText);
    }
}
