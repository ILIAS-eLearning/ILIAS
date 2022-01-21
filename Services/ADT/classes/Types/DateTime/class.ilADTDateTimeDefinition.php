<?php declare(strict_types=1);

class ilADTDateTimeDefinition extends ilADTDefinition
{
    // comparison

    public function isComparableTo(ilADT $a_adt) : bool
    {
        // has to be date-based
        return ($a_adt instanceof ilADTDateTime || $a_adt instanceof ilADTDate);
    }
}
