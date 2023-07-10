<?php

declare(strict_types=1);

/**
 * Class ilADTBooleanDefinition
 */
class ilADTBooleanDefinition extends ilADTDefinition
{
    // comparison

    public function isComparableTo(ilADT $a_adt): bool
    {
        // has to be boolean-based
        return ($a_adt instanceof ilADTBoolean);
    }
}
