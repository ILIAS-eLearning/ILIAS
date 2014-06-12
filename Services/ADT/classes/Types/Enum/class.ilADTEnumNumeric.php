<?php

class ilADTEnumNumeric extends ilADTEnum
{
	protected function handleSelectionValue($a_value)
	{
		return (int)$a_value;
	}
}

?>