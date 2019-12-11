<?php

require_once "Services/ADT/classes/Types/Integer/class.ilADTIntegerDefinition.php";

class ilADTFloatDefinition extends ilADTIntegerDefinition
{
    protected $decimals; // [float]
    
    
    // default
    
    public function reset()
    {
        parent::reset();
        
        $this->setDecimals(1);
    }
    
    
    // properties
    
    public function handleNumber($a_value)
    {
        if (!is_numeric($a_value)) {
            $a_value = null;
        }
        if ($a_value !== null) {
            $a_value = round((float) $a_value, $this->getDecimals());
        }
        return $a_value;
    }
    
    public function getDecimals()
    {
        return $this->decimals;
    }
    
    public function setDecimals($a_value)
    {
        // max precision ?!
        $this->decimals = max(1, abs((int) $a_value));
    }
}
