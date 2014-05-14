<?php

class ilADTMultiEnumText extends ilADTMultiEnum
{
	protected function handleSelectionValue($a_value)
	{
		return (string)$a_value;
	}
}

?>