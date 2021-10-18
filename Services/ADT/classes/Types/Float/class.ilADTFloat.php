<?php

require_once "Services/ADT/classes/Types/Integer/class.ilADTInteger.php";

class ilADTFloat extends ilADTInteger
{
    // definition
    
    protected function isValidDefinition(ilADTDefinition $a_def) : bool
    {
        return $a_def instanceof ilADTFloatDefinition;
    }
}
