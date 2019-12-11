<?php

class ilADTMultiEnumNumeric extends ilADTMultiEnum
{
    protected function handleSelectionValue($a_value)
    {
        return (int) $a_value;
    }
}
