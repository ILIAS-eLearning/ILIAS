<?php declare(strict_types=1);

class ilADTEnumText extends ilADTEnum
{
    protected function handleSelectionValue($a_value) : mixed
    {
        return (string) $a_value;
    }
}
