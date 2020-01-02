<?php

class ilADTIntegerDefinition extends ilADTDefinition
{
    protected $min_value; // [int]
    protected $max_value; // [int]
    protected $suffix; // [string]
    
    
    // properties
    
    public function handleNumber($a_value)
    {
        if (!is_numeric($a_value)) {
            $a_value = null;
        }
        if ($a_value !== null) {
            // round?
            $a_value = (int) $a_value;
        }
        return $a_value;
    }
    
    public function getMin()
    {
        return $this->min;
    }
    
    public function setMin($a_value)
    {
        $this->min = $this->handleNumber($a_value);
    }
    
    public function getMax()
    {
        return $this->max;
    }
    
    public function setMax($a_value)
    {
        $this->max = $this->handleNumber($a_value);
    }
    
    public function getSuffix()
    {
        return $this->suffix;
    }
    
    public function setSuffix($a_value)
    {
        $this->suffix = trim($a_value);
    }
    
    
    // comparison
    
    public function isComparableTo(ilADT $a_adt)
    {
        // has to be number-based
        return ($a_adt instanceof ilADTInteger);
    }
}
