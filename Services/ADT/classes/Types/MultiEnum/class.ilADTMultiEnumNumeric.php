<?php declare(strict_types=1);

class ilADTMultiEnumNumeric extends ilADTMultiEnum
{
    protected function handleSelectionValue(mixed $a_value) : mixed
    {
        return (int) $a_value;
    }
}
