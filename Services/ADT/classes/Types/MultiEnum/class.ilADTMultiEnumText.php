<?php

class ilADTMultiEnumText extends ilADTMultiEnum
{
    protected function handleSelectionValue(mixed $a_value) : mixed
    {
        return (string) $a_value;
    }
}
