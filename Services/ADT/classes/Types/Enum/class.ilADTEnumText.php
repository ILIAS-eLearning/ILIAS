<?php

class ilADTEnumText extends ilADTEnum
{
	protected function handleSelectionValue($a_value)
	{
		return (string)$a_value;
	}
}

?>