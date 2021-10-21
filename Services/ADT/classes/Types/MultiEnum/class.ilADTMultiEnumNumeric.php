<?php

class ilADTMultiEnumNumeric extends ilADTMultiEnum
{
    protected function handleSelectionValue(mixed $a_value) : mixed
    {
        return (int) $a_value;
    }
}
