<?php

class ilADTDateDefinition extends ilADTDefinition
{	
	// comparison
			
	public function isComparableTo(ilADT $a_adt)
	{
		// has to be date-based
		return ($a_adt instanceof ilADTDate || $a_adt instanceof ilADTDateTime);
	}		
}

?>