<?php declare(strict_types=1);

class ilADTMultiEnumText extends ilADTMultiEnum
{
    protected function handleSelectionValue(mixed $a_value) : mixed
    {
        return (string) $a_value;
    }
}
