<?php

require_once "Services/ADT/classes/Types/Integer/class.ilADTInteger.php";

class ilADTFloat extends ilADTInteger
{
    // definition
    
    protected function isValidDefinition(ilADTDefinition $a_def)
    {
        return ($a_def instanceof ilADTFloatDefinition);
    }
}
