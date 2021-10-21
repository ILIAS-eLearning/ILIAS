<?php

class ilADTEnumNumeric extends ilADTEnum
{
    protected function handleSelectionValue($a_value) : mixed
    {
        return (int) $a_value;
    }
}
