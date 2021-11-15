<?php declare(strict_types=1);

class ilADTLocationDefinition extends ilADTDefinition
{
    public function isComparableTo(ilADT $a_adt) : bool
    {
        // has to be location-based
        return ($a_adt instanceof ilADTLocation);
    }
}
